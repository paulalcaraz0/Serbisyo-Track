<?php

namespace App\Services;

use App\Enums\ServiceRequestStatus;
use App\Models\ServiceRequest;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AdminReportService
{
    /**
     * @param  array{date_from: string, date_to: string, service: string, status: string}  $filters
     * @return Collection<int, ServiceRequest>
     */
    public function records(array $filters): Collection
    {
        $query = ServiceRequest::query()
            ->select([
                'id',
                'service_id',
                'public_reference',
                'status',
                'assigned_to',
                'submitted_at',
                'due_at',
                'closed_at',
            ])
            ->with([
                'service:id,slug,name_en',
                'assignee:id,name',
            ])
            ->whereBetween('submitted_at', [
                CarbonImmutable::parse($filters['date_from'])->startOfDay(),
                CarbonImmutable::parse($filters['date_to'])->endOfDay(),
            ]);

        if ($filters['service'] !== 'all') {
            $query->whereHas('service', fn (Builder $builder) => $builder->where('slug', $filters['service']));
        }

        if ($filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        return $query->orderByDesc('submitted_at')->orderByDesc('id')->get();
    }

    /**
     * @param  Collection<int, ServiceRequest>  $records
     * @param  array{date_from: string, date_to: string, service: string, status: string}  $filters
     * @return array<string, mixed>
     */
    public function analytics(Collection $records, array $filters): array
    {
        $open = $records->filter(fn (ServiceRequest $request) => ! $request->status->isTerminal());
        $completed = $records->filter(fn (ServiceRequest $request) => $request->status === ServiceRequestStatus::Completed);
        $overdue = $open->filter(fn (ServiceRequest $request) => $request->due_at !== null && $request->due_at->isPast());
        $resolutionHours = $completed
            ->filter(fn (ServiceRequest $request) => $request->closed_at !== null)
            ->map(fn (ServiceRequest $request) => round($request->submitted_at->diffInSeconds($request->closed_at) / 3600, 1));

        $statusCounts = $records->countBy(fn (ServiceRequest $request) => $request->status->value);
        $statusBreakdown = [];

        foreach (ServiceRequestStatus::cases() as $status) {
            $statusBreakdown[] = [
                'status' => $status->value,
                'label' => $status->label(),
                'count' => (int) ($statusCounts[$status->value] ?? 0),
            ];
        }

        $serviceBreakdown = $records
            ->groupBy(fn (ServiceRequest $request) => $request->service->slug)
            ->map(function (Collection $requests): array {
                /** @var ServiceRequest $first */
                $first = $requests->first();

                return [
                    'slug' => $first->service->slug,
                    'name' => $first->service->name_en,
                    'total' => $requests->count(),
                    'open' => $requests->filter(fn (ServiceRequest $request) => ! $request->status->isTerminal())->count(),
                    'completed' => $requests->where('status', ServiceRequestStatus::Completed)->count(),
                    'overdue' => $requests->filter(fn (ServiceRequest $request) => ! $request->status->isTerminal()
                        && $request->due_at !== null && $request->due_at->isPast())->count(),
                ];
            })
            ->sortByDesc('total')
            ->values()
            ->all();

        $dailyCounts = $records->countBy(fn (ServiceRequest $request) => $request->submitted_at->toDateString());
        $trend = [];
        $day = CarbonImmutable::parse($filters['date_from'])->startOfDay();
        $lastDay = CarbonImmutable::parse($filters['date_to'])->startOfDay();

        while ($day->lessThanOrEqualTo($lastDay)) {
            $date = $day->toDateString();
            $trend[] = ['date' => $date, 'count' => (int) ($dailyCounts[$date] ?? 0)];
            $day = $day->addDay();
        }

        return [
            'summary' => [
                'total' => $records->count(),
                'open' => $open->count(),
                'overdue' => $overdue->count(),
                'completed' => $completed->count(),
                'completion_rate' => $records->isEmpty() ? 0 : round(($completed->count() / $records->count()) * 100, 1),
                'average_resolution_hours' => $resolutionHours->isEmpty() ? null : round((float) $resolutionHours->average(), 1),
            ],
            'status_breakdown' => $statusBreakdown,
            'service_breakdown' => $serviceBreakdown,
            'trend' => $trend,
        ];
    }
}
