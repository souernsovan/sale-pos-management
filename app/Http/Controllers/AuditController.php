<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $activities = Activity::query()
            ->with(['causer', 'subject'])
            ->when($request->filled('log_name'), fn ($q) => $q->where('log_name', $request->log_name))
            ->when($request->filled('causer_id'), fn ($q) => $q->where('causer_type', User::class)->where('causer_id', $request->causer_id))
            ->when($request->filled('event'), fn ($q) => $q->where('event', $request->event))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $logNames = Activity::query()->whereNotNull('log_name')->distinct()->orderBy('log_name')->pluck('log_name');
        $causers = User::orderBy('name')->get(['id', 'name']);

        return view('audit.index', compact('activities', 'logNames', 'causers'));
    }
}
