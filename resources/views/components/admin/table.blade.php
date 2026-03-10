@props(['headers' => []])

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    {{-- Optional header slot for title/filters --}}
    @if(isset($toolbar))
        <div class="px-6 py-4 border-b border-gray-200">
            {{ $toolbar }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50 border-b border-gray-200">
                <tr>
                    @foreach($headers as $header)
                        <th class="px-6 py-3 whitespace-nowrap">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                {{ $slot }}
            </tbody>
        </table>
    </div>

    {{-- Optional pagination slot --}}
    @if(isset($pagination))
        <div class="px-6 py-3 border-t border-gray-200 bg-gray-50">
            {{ $pagination }}
        </div>
    @endif
</div>
