<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = AuditLog::query()
            ->search($request->input('search'))
            ->forAction($request->input('action'))
            ->forSubjectType($request->input('model'))
            ->forUser($request->filled('user_id') ? (int) $request->input('user_id') : null)
            ->dateRange($request->input('from'), $request->input('to'))
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString()
            ->through(fn (AuditLog $log) => [
                'id' => $log->id,
                'subject_type' => $log->subject_type,
                'subject_id' => $log->subject_id,
                'action' => $log->action,
                'old_values' => $log->old_values,
                'new_values' => $log->new_values,
                'user_name' => $log->user_name,
                'user_email' => $log->user_email,
                'ip_address' => $log->ip_address,
                'summary' => $log->summary,
                'created_at' => $log->created_at?->format('d/m/Y H:i:s'),
                'model_label' => AuditLog::modelTypeLabel($log->subject_type),
                'action_label' => AuditLog::actionLabel($log->action),
                'short_model' => $log->shortModelName(),
            ]);

        $actions = collect(AuditLog::allActions())->map(fn (string $a) => [
            'value' => $a,
            'label' => AuditLog::actionLabel($a),
        ]);

        $models = collect(AuditLog::allSubjectTypes())->map(fn (string $m) => [
            'value' => $m,
            'label' => AuditLog::modelTypeLabel($m),
        ]);

        $users = User::orderBy('name')->get()->map(fn (User $u) => [
            'value' => $u->id,
            'label' => $u->name,
        ]);

        return Inertia::render('Settings/AuditLogs', [
            'logs' => $logs,
            'filters' => [
                'search' => $request->input('search', ''),
                'action' => $request->input('action', ''),
                'model' => $request->input('model', ''),
                'user_id' => $request->input('user_id', ''),
                'from' => $request->input('from', ''),
                'to' => $request->input('to', ''),
            ],
            'actionOptions' => $actions,
            'modelOptions' => $models,
            'userOptions' => $users,
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $logs = AuditLog::query()
            ->search($request->input('search'))
            ->forAction($request->input('action'))
            ->forSubjectType($request->input('model'))
            ->forUser($request->filled('user_id') ? (int) $request->input('user_id') : null)
            ->dateRange($request->input('from'), $request->input('to'))
            ->orderByDesc('created_at')
            ->get();

        return response()->streamDownload(function () use ($logs) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM
            fwrite($handle, "\xEF\xBB\xBF");

            // Header
            fputcsv($handle, [
                __('audit.col_date'),
                __('audit.col_user'),
                __('audit.col_action'),
                __('audit.col_model'),
                __('audit.col_entity_id'),
                __('audit.col_summary'),
                __('audit.col_ip'),
            ], ';');

            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->created_at?->format('d/m/Y H:i:s'),
                    $log->user_name ?? __('audit.system'),
                    __($log->action),
                    $log->shortModelName(),
                    $log->subject_id,
                    $log->summary,
                    $log->ip_address,
                ], ';');
            }

            fclose($handle);
        }, 'audit_logs_' . now()->format('Y-m-d_His') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
