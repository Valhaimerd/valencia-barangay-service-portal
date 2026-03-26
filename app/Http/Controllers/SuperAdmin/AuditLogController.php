<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());
        $action = $request->string('action')->toString();

        $logs = AuditLog::query()
            ->with('user')
            ->when($action !== '', fn ($query) => $query->where('action', $action))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('action', 'ilike', "%{$search}%")
                        ->orWhere('description', 'ilike', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery
                                ->where('name', 'ilike', "%{$search}%")
                                ->orWhere('email', 'ilike', "%{$search}%");
                        });
                });
            })
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        $actionOptions = AuditLog::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view('super-admin.audit-logs.index', [
            'logs' => $logs,
            'search' => $search,
            'selectedAction' => $action,
            'actionOptions' => $actionOptions,
        ]);
    }
}
