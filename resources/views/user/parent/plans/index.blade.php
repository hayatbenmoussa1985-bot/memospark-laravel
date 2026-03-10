<x-user-layout title="Revision Plans">

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Revision Plans</h1>
            <p class="text-sm text-gray-500">Create study plans for your children</p>
        </div>
        <a href="{{ route('user.parent.plans.create') }}" class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">
            + New Plan
        </a>
    </div>

    <div class="space-y-4">
        @forelse($plans as $plan)
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">{{ $plan->title }}</h3>
                        <p class="text-sm text-gray-500">For {{ $plan->childUser?->name ?? 'Unknown' }}</p>
                    </div>
                    @if($plan->isCurrentlyActive())
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Active</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                            {{ $plan->end_date->isPast() ? 'Completed' : 'Upcoming' }}
                        </span>
                    @endif
                </div>

                @if($plan->description)
                    <p class="text-sm text-gray-600 mb-3">{{ $plan->description }}</p>
                @endif

                <div class="flex items-center gap-4 text-xs text-gray-500 mb-3">
                    <span>{{ $plan->start_date->format('M d') }} — {{ $plan->end_date->format('M d, Y') }}</span>
                    <span>{{ $plan->decks->count() }} decks</span>
                </div>

                <div class="flex flex-wrap gap-1.5">
                    @foreach($plan->decks as $deck)
                        <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded-md text-xs">{{ $deck->title }}</span>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
                <p class="text-gray-500">No revision plans yet.</p>
                <a href="{{ route('user.parent.plans.create') }}" class="mt-2 inline-block text-sm text-emerald-600 hover:underline">Create your first plan</a>
            </div>
        @endforelse
    </div>

</x-user-layout>
