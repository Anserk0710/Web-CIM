<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    public function index(
        Request $request,
    ): Response {
        Gate::authorize('audit.read');

        $action = trim(
            (string) $request->query(
                'action',
                '',
            ),
        );

        $entityType = trim(
            (string) $request->query(
                'entity_type',
                '',
            ),
        );

        $result = trim(
            (string) $request->query(
                'result',
                '',
            ),
        );

        $query = AuditLog::query()
            ->with(
                'actor:id,name,email',
            );

        if ($action !== '') {
            $query->where(
                'action',
                'like',
                '%'.$action.'%',
            );
        }

        if ($entityType !== '') {
            $query->where(
                'entity_type',
                $entityType,
            );
        }

        if ($result !== '') {
            $query->where(
                'result',
                $result,
            );
        }

        return Inertia::render(
            'Admin/Audit/Index',
            [
                'logs' => $query
                    ->latest('id')
                    ->paginate(30)
                    ->withQueryString(),

                'filters' => [
                    'action' => $action,
                    'entity_type' => $entityType,
                    'result' => $result,
                ],
            ],
        );
    }

    public function show(
        AuditLog $audit,
    ): Response {
        Gate::authorize('audit.read');

        $audit->load(
            'actor:id,name,email',
        );

        return Inertia::render(
            'Admin/Audit/Show',
            [
                'audit' => $audit,
            ],
        );
    }
}
