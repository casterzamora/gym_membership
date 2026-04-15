<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    use ApiResponse;

    public function revenue(Request $request)
    {
        $validated = $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
            'group_by' => 'nullable|in:day,month',
        ]);

        $from = $validated['from'] ?? now()->subMonths(5)->startOfMonth()->toDateString();
        $to = $validated['to'] ?? now()->endOfMonth()->toDateString();
        $groupBy = $validated['group_by'] ?? 'month';

        $periodExpr = $groupBy === 'day'
            ? "DATE_FORMAT(payment_date, '%Y-%m-%d')"
            : "DATE_FORMAT(payment_date, '%Y-%m')";

        $rows = DB::table('payments')
            ->selectRaw("{$periodExpr} as period, SUM(amount_paid) as total_revenue, COUNT(*) as payment_count")
            ->whereDate('payment_date', '>=', $from)
            ->whereDate('payment_date', '<=', $to)
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        $totalRevenue = (float) $rows->sum('total_revenue');

        return $this->success([
            'from' => $from,
            'to' => $to,
            'group_by' => $groupBy,
            'total_revenue' => round($totalRevenue, 2),
            'series' => $rows,
        ], 'Revenue report retrieved successfully');
    }

    public function classPopularity(Request $request)
    {
        $validated = $request->validate([
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $limit = (int) ($validated['limit'] ?? 10);

        $rows = DB::table('fitness_classes as fc')
            ->leftJoin('class_schedules as cs', 'cs.class_id', '=', 'fc.id')
            ->leftJoin('attendance as a', 'a.schedule_id', '=', 'cs.id')
            ->selectRaw('fc.id as class_id, fc.class_name, fc.max_participants')
            ->selectRaw('COUNT(DISTINCT cs.id) as total_schedules')
            ->selectRaw('COUNT(a.schedule_id) as total_attendance_records')
            ->groupBy('fc.id', 'fc.class_name', 'fc.max_participants')
            ->orderByDesc('total_attendance_records')
            ->orderByDesc('total_schedules')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $capacity = (int) $row->max_participants;
                $schedules = (int) $row->total_schedules;
                $attendance = (int) $row->total_attendance_records;

                return [
                    'class_id' => (int) $row->class_id,
                    'class_name' => $row->class_name,
                    'max_participants' => $capacity,
                    'total_schedules' => $schedules,
                    'total_attendance_records' => $attendance,
                    'average_attendance_per_schedule' => $schedules > 0 ? round($attendance / $schedules, 2) : 0.0,
                    'capacity_utilization_percent' => ($schedules > 0 && $capacity > 0)
                        ? round(($attendance / ($schedules * $capacity)) * 100, 2)
                        : 0.0,
                ];
            })
            ->values();

        return $this->success($rows, 'Class popularity report retrieved successfully');
    }

    public function lowAttendanceMembers(Request $request)
    {
        $validated = $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $from = $validated['from'] ?? now()->subDays(60)->toDateString();
        $to = $validated['to'] ?? now()->toDateString();
        $limit = (int) ($validated['limit'] ?? 20);

        $rows = DB::table('members as m')
            ->leftJoin('attendance as a', function ($join) use ($from, $to) {
                $join->on('a.member_id', '=', 'm.id')
                    ->whereDate('a.recorded_at', '>=', $from)
                    ->whereDate('a.recorded_at', '<=', $to)
                    ->where('a.attendance_status', '=', 'Present');
            })
            ->selectRaw('m.id as member_id, m.first_name, m.last_name, m.email')
            ->selectRaw('COUNT(a.member_id) as present_count')
            ->groupBy('m.id', 'm.first_name', 'm.last_name', 'm.email')
            ->orderBy('present_count')
            ->orderBy('m.id')
            ->limit($limit)
            ->get();

        return $this->success([
            'from' => $from,
            'to' => $to,
            'members' => $rows,
        ], 'Low attendance members report retrieved successfully');
    }
}
