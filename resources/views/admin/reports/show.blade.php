<x-admin-layout title="Report Detail">

    <div class="mb-6">
        <a href="{{ route('admin.reports.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Reports
        </a>
    </div>

    <div class="max-w-2xl space-y-6">
        {{-- Report Info --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-start justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Report #{{ $report->id }}</h3>
                <x-admin.badge :color="match($report->status) {
                    'pending' => 'amber', 'reviewed' => 'blue', 'resolved' => 'green', 'dismissed' => 'gray', default => 'gray',
                }">{{ ucfirst($report->status) }}</x-admin.badge>
            </div>

            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Reporter</dt>
                    <dd class="text-sm text-gray-900">{{ $report->reporter?->name ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Type</dt>
                    <dd class="text-sm text-gray-900">{{ ucfirst($report->reportable_type) }} #{{ $report->reportable_id }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Reason</dt>
                    <dd class="text-sm text-gray-900">{{ $report->reason }}</dd>
                </div>
                @if($report->description)
                    <div>
                        <dt class="text-sm text-gray-500 mb-1">Description</dt>
                        <dd class="text-sm text-gray-700 bg-gray-50 rounded-lg p-3">{{ $report->description }}</dd>
                    </div>
                @endif
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Reported at</dt>
                    <dd class="text-sm text-gray-900">{{ $report->created_at->format('M d, Y H:i') }}</dd>
                </div>
                @if($report->reviewer)
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Reviewed by</dt>
                        <dd class="text-sm text-gray-900">{{ $report->reviewer->name }}</dd>
                    </div>
                @endif
                @if($report->resolution_note)
                    <div>
                        <dt class="text-sm text-gray-500 mb-1">Resolution note</dt>
                        <dd class="text-sm text-gray-700 bg-gray-50 rounded-lg p-3">{{ $report->resolution_note }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        {{-- Actions --}}
        @if($report->status === 'pending')
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h4 class="font-semibold text-gray-900 mb-4">Take Action</h4>

                <div class="space-y-4">
                    {{-- Resolve --}}
                    <form method="POST" action="{{ route('admin.reports.resolve', $report) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Resolution note</label>
                            <textarea name="resolution_note" rows="2" required placeholder="Describe the action taken..."
                                      class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                        </div>
                        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">
                            Resolve
                        </button>
                    </form>

                    <div class="border-t border-gray-200 pt-4">
                        <form method="POST" action="{{ route('admin.reports.dismiss', $report) }}">
                            @csrf
                            <input type="hidden" name="resolution_note" value="">
                            <button type="submit" class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                                Dismiss Report
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>

</x-admin-layout>
