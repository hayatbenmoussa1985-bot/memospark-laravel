<x-admin-layout :title="'Edit Plan: ' . $plan->name">

    <div class="mb-6">
        <a href="{{ route('admin.subscriptions.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Subscriptions
        </a>
    </div>

    <div class="max-w-lg">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Edit Plan: {{ $plan->name }}</h3>

            <form method="POST" action="{{ route('admin.subscriptions.plans.update', $plan) }}">
                @csrf @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $plan->name) }}"
                               class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Price (USD)</label>
                        <input type="number" name="price" id="price" value="{{ old('price', $plan->price) }}" step="0.01" min="0"
                               class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label for="duration_days" class="block text-sm font-medium text-gray-700 mb-1">Duration (days)</label>
                        <input type="number" name="duration_days" id="duration_days" value="{{ old('duration_days', $plan->duration_days) }}" min="0"
                               class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        <p class="text-xs text-gray-500 mt-1">Use 0 for free plan (unlimited)</p>
                    </div>
                    <div>
                        <label for="apple_product_id" class="block text-sm font-medium text-gray-700 mb-1">Apple Product ID</label>
                        <input type="text" name="apple_product_id" id="apple_product_id" value="{{ old('apple_product_id', $plan->apple_product_id) }}"
                               class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                        <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $plan->sort_order) }}" min="0"
                               class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ $plan->is_active ? 'checked' : '' }}
                               class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        <label for="is_active" class="text-sm text-gray-700">Active</label>
                    </div>
                </div>
                <div class="mt-6 flex gap-3">
                    <button type="submit" class="px-6 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">Save</button>
                    <a href="{{ route('admin.subscriptions.index') }}" class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</a>
                </div>
            </form>
        </div>
    </div>

</x-admin-layout>
