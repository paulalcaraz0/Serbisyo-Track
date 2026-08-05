<?php

namespace App\Services;

use App\Enums\AuditEventType;
use App\Models\OfficeSetting;
use App\Models\ServiceRequest;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class RequestRetentionService
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /** @return array{request_count: int, attachment_count: int, cutoff_date: string, dry_run: bool} */
    public function purge(bool $dryRun = false, ?User $actor = null): array
    {
        $cutoff = CarbonImmutable::now()->subDays(OfficeSetting::current()->retention_days)->endOfDay();
        $candidateIds = ServiceRequest::query()
            ->whereNotNull('closed_at')
            ->where('closed_at', '<=', $cutoff)
            ->orderBy('id')
            ->pluck('id');
        $attachmentCount = 0;

        if ($dryRun) {
            $attachmentCount = ServiceRequest::query()
                ->whereKey($candidateIds)
                ->withCount('attachments')
                ->get()
                ->sum('attachments_count');
        } else {
            foreach ($candidateIds as $requestId) {
                $attachmentCount += DB::transaction(function () use ($requestId, $cutoff): int {
                    $request = ServiceRequest::query()
                        ->whereKey($requestId)
                        ->whereNotNull('closed_at')
                        ->where('closed_at', '<=', $cutoff)
                        ->lockForUpdate()
                        ->first();

                    if ($request === null) {
                        return 0;
                    }

                    $attachments = $request->attachments()->get();

                    foreach ($attachments as $attachment) {
                        $disk = Storage::disk($attachment->disk);

                        if ($disk->exists($attachment->path) && ! $disk->delete($attachment->path)) {
                            throw new RuntimeException('An expired private attachment could not be deleted.');
                        }
                    }

                    $count = $attachments->count();
                    $request->delete();

                    return $count;
                });
            }
        }

        $result = [
            'request_count' => $candidateIds->count(),
            'attachment_count' => $attachmentCount,
            'cutoff_date' => $cutoff->toDateString(),
            'dry_run' => $dryRun,
        ];

        $this->auditLogger->record($actor, AuditEventType::RetentionPurged, 'retention', $cutoff->toDateString(), $result);

        return $result;
    }
}
