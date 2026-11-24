<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Core\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizePermission('activity.view');

        $logs = ActivityLog::query()
            ->with('user')
            ->when($request->filled('user'), fn ($q) => $q->where('user_id', $request->query('user')))
            ->when($request->filled('module'), fn ($q) => $q->where('module', $request->query('module')))
            ->when($request->filled('action'), fn ($q) => $q->where('action', $request->query('action')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->query('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->query('to')))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        $users = User::orderBy('name')->get();
        $modules = ActivityLog::query()->distinct()->orderBy('module')->pluck('module');

        return view('activity.index', compact('logs', 'users', 'modules'));
    }
}
