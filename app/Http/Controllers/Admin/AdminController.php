<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use App\Models\Views\User as ViewsUser;
use App\Models\Views\Visit;
use App\Models\Views\VisitYesterday;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use stdClass;
use Yajra\DataTables\Facades\DataTables;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $users = ViewsUser::count();

        /** Reservation statistics (visible to all authenticated users) */
        $totalRooms = Room::count();
        $activeReservations = Reservation::where('status', 'ativa')->count();
        $todayReservations = Reservation::where('status', 'ativa')
            ->whereDate('date', today())->count();
        $weekReservations = Reservation::where('status', 'ativa')
            ->whereBetween('date', [today(), today()->addDays(6)])->count();
        $cancelledReservations = Reservation::where('status', 'cancelada')->count();

        $visits = Visit::where('url', '!=', route('admin.home.chart'))
            ->where('url', 'NOT LIKE', '%columns%')
            ->where('url', 'NOT LIKE', '%storage%')
            ->where('url', 'NOT LIKE', '%admin%')
            ->where('url', 'NOT LIKE', '%offline%')
            ->where('url', 'NOT LIKE', '%manifest.json%')
            ->where('url', 'NOT LIKE', '%.png%')
            ->where('url', 'NOT LIKE', '%.js%')
            ->get();

        if ($request->ajax()) {
            return DataTables::of($visits)
                ->addColumn('time', function ($row) {
                    return date(('H:i:s'), strtotime($row->created_at));
                })
                ->addIndexColumn()
                ->rawColumns(['time'])
                ->make(true);
        }

        /** Statistics */
        $statistics = $this->accessStatistics();
        $onlineUsers = $statistics['onlineUsers'];
        $percent = $statistics['percent'];
        $access = $statistics['access'];
        $chart = $statistics['chart'];

        // Monthly reservations chart — last 12 months, visible to all users
        $ptMonths = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
        $chartMonths = collect(range(11, 0))->map(fn ($i) => now()->subMonths($i));

        $monthlyRaw = Reservation::selectRaw("DATE_FORMAT(date, '%Y-%m') as month, status, COUNT(*) as total")
            ->where('date', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('month', 'status')
            ->get()
            ->groupBy('month');

        $reservationChart = new stdClass;
        $reservationChart->labels = $chartMonths->map(fn ($d) => $ptMonths[$d->month - 1].'/'.$d->format('y'))->values()->toArray();
        $reservationChart->ativas = $chartMonths->map(fn ($d) => (int) ($monthlyRaw->get($d->format('Y-m'))?->firstWhere('status', 'ativa')?->total ?? 0))->values()->toArray();
        $reservationChart->canceladas = $chartMonths->map(fn ($d) => (int) ($monthlyRaw->get($d->format('Y-m'))?->firstWhere('status', 'cancelada')?->total ?? 0))->values()->toArray();

        return view('admin.home.index', compact(
            'users',
            'onlineUsers',
            'percent',
            'access',
            'chart',
            'totalRooms',
            'activeReservations',
            'todayReservations',
            'weekReservations',
            'cancelledReservations',
            'reservationChart',
        ));
    }

    public function chart(): JsonResponse
    {
        /** Statistics */
        $statistics = $this->accessStatistics();
        $onlineUsers = $statistics['onlineUsers'];
        $percent = $statistics['percent'];
        $access = $statistics['access'];
        $chart = $statistics['chart'];

        return response()->json([
            'onlineUsers' => $onlineUsers,
            'access' => $access,
            'percent' => $percent,
            'chart' => $chart,
        ]);
    }

    private function accessStatistics(): array
    {
        $onlineUsers = User::online()->count();

        $accessToday = Visit::where('url', '!=', route('admin.home.chart'))
            ->where('url', 'NOT LIKE', '%columns%')
            ->where('url', 'NOT LIKE', '%storage%')
            ->where('url', 'NOT LIKE', '%admin%')
            ->where('url', 'NOT LIKE', '%offline%')
            ->where('url', 'NOT LIKE', '%manifest.json%')
            ->where('url', 'NOT LIKE', '%.png%')
            ->where('url', 'NOT LIKE', '%.js%')
            ->where('method', 'GET')
            ->get();
        $accessYesterday = VisitYesterday::where('url', '!=', route('admin.home.chart'))
            ->where('url', 'NOT LIKE', '%columns%')
            ->where('url', 'NOT LIKE', '%storage%')
            ->where('url', 'NOT LIKE', '%admin%')
            ->where('url', 'NOT LIKE', '%offline%')
            ->where('url', 'NOT LIKE', '%manifest.json%')
            ->where('url', 'NOT LIKE', '%.png%')
            ->where('url', 'NOT LIKE', '%.js%')
            ->where('method', 'GET')
            ->count();

        $totalDaily = $accessToday->count();

        $percent = 0;
        if ($accessYesterday > 0 && $totalDaily > 0) {
            $percent = number_format((($totalDaily - $accessYesterday) / $totalDaily * 100), 2, ',', '.');
        }

        /** Visitor Chart */
        $data = $accessToday->groupBy(function ($reg) {
            return date('H', strtotime($reg->created_at));
        });

        $dataList = [];
        foreach ($data as $key => $value) {
            $dataList[$key.'H'] = count($value);
        }

        $chart = new stdClass;
        $chart->labels = (array_keys($dataList));
        $chart->dataset = (array_values($dataList));

        return [
            'onlineUsers' => $onlineUsers,
            'access' => $totalDaily,
            'percent' => $percent,
            'chart' => $chart,
        ];
    }
}
