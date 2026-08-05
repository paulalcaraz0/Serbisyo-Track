<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditEventType;
use App\Enums\ServiceRequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReportFilterRequest;
use App\Models\Service;
use App\Models\User;
use App\Services\AdminReportService;
use App\Services\AuditLogger;
use App\Support\CsvCellSanitizer;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(ReportFilterRequest $request, AdminReportService $reports): Response
    {
        Gate::authorize('viewReports');
        $filters = $request->filters();
        $records = $reports->records($filters);
        $statuses = [];

        foreach (ServiceRequestStatus::cases() as $status) {
            $statuses[] = [
                'value' => $status->value,
                'label' => $status->label(),
            ];
        }

        return Inertia::render('admin/reports/index', [
            'filters' => $filters,
            'analytics' => $reports->analytics($records, $filters),
            'services' => Service::query()->orderBy('name_en')->get(['slug', 'name_en']),
            'statuses' => $statuses,
        ]);
    }

    public function export(ReportFilterRequest $request, AdminReportService $reports, AuditLogger $auditLogger): StreamedResponse
    {
        Gate::authorize('viewReports');
        $filters = $request->filters();
        $records = $reports->records($filters);
        $actor = $request->user();
        abort_unless($actor instanceof User, 403);

        $auditLogger->record($actor, AuditEventType::ReportExported, 'report', 'requests_csv', [
            'date_from' => $filters['date_from'],
            'date_to' => $filters['date_to'],
            'service_slug' => $filters['service'],
            'status' => $filters['status'],
            'row_count' => $records->count(),
        ]);

        return response()->streamDownload(function () use ($records): void {
            $stream = fopen('php://output', 'wb');
            abort_unless(is_resource($stream), 500);
            fputcsv($stream, ['Reference', 'Service', 'Status', 'Submitted at', 'Due at', 'Closed at', 'Assignee']);

            foreach ($records as $serviceRequest) {
                fputcsv($stream, array_map(CsvCellSanitizer::sanitize(...), [
                    $serviceRequest->public_reference,
                    $serviceRequest->service->name_en,
                    $serviceRequest->status->label(),
                    $serviceRequest->submitted_at->toIso8601String(),
                    $serviceRequest->due_at?->toIso8601String(),
                    $serviceRequest->closed_at?->toIso8601String(),
                    $serviceRequest->assignee?->name,
                ]));

            }

            fclose($stream);
        }, 'serbisyo-track-requests-'.$filters['date_from'].'-'.$filters['date_to'].'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
