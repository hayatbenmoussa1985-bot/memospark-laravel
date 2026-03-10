<x-admin-layout title="Audit Log Detail">

    <div class="mb-6">
        <a href="{{ route('admin.audit-log.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Audit Log
        </a>
    </div>

    <div class="max-w-2xl">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Audit Log #{{ $auditLog->id }}</h3>

            <dl class="space-y-4">
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Action</dt>
                    <dd><x-admin.badge color="blue">{{ str_replace('_', ' ', $auditLog->action) }}</x-admin.badge></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">User</dt>
                    <dd class="text-sm text-gray-900">{{ $auditLog->user?->name ?? '—' }} ({{ $auditLog->user?->email }})</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Target</dt>
                    <dd class="text-sm text-gray-900">{{ $auditLog->target_type ? ucfirst($auditLog->target_type) . ' #' . $auditLog->target_id : '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">IP Address</dt>
                    <dd class="text-sm text-gray-900 font-mono">{{ $auditLog->ip_address ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Timestamp</dt>
                    <dd class="text-sm text-gray-900">{{ $auditLog->created_at->format('M d, Y H:i:s') }}</dd>
                </div>

                @if($auditLog->old_values)
                    <div>
                        <dt class="text-sm text-gray-500 mb-2">Old Values</dt>
                        <dd class="bg-red-50 rounded-lg p-3">
                            <pre class="text-xs text-red-800 whitespace-pre-wrap">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT) }}</pre>
                        </dd>
                    </div>
                @endif

                @if($auditLog->new_values)
                    <div>
                        <dt class="text-sm text-gray-500 mb-2">New Values</dt>
                        <dd class="bg-emerald-50 rounded-lg p-3">
                            <pre class="text-xs text-emerald-800 whitespace-pre-wrap">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT) }}</pre>
                        </dd>
                    </div>
                @endif

                @if($auditLog->user_agent)
                    <div>
                        <dt class="text-sm text-gray-500 mb-1">User Agent</dt>
                        <dd class="text-xs text-gray-500 break-all">{{ $auditLog->user_agent }}</dd>
                    </div>
                @endif
            </dl>
        </div>
    </div>

</x-admin-layout>
