<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Central Inventory Items') }}
            </h2>
            <a href="{{ route('inventory-items.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                {{ __('Add New Item') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <!-- Search and Filter Form -->
                    <form method="GET" action="{{ route('inventory-items.index') }}" class="mb-6">
                        <div class="flex flex-wrap gap-4">
                            <div class="flex-1">
                                <x-input type="text" name="search" value="{{ request('search') }}" placeholder="Search items..." class="w-full"/>
                            </div>
                            <div class="w-48">
                                <select name="brand" class="w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">
                                    <option value="">All Brands</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand }}" {{ request('brand') == $brand ? 'selected' : '' }}>
                                            {{ $brand }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-button type="submit">
                                    {{ __('Filter') }}
                                </x-button>
                            </div>
                        </div>
                    </form>

                    <!-- Items Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Item Details') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Identifiers') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Unit') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Last Updated') }}
                                    </th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">{{ __('Actions') }}</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($items as $item)
                                    <tr>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $item->name }}</div>
                                            @if($item->brand)
                                                <div class="text-sm text-gray-500">{{ $item->brand }}</div>
                                            @endif
                                            @if($item->model)
                                                <div class="text-sm text-gray-500">{{ $item->model }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($item->sku)
                                                <div class="text-sm text-gray-500">SKU: {{ $item->sku }}</div>
                                            @endif
                                            @if($item->barcode)
                                                <div class="text-sm text-gray-500">Barcode: {{ $item->barcode }}</div>
                                            @endif
                                            @if($item->upc)
                                                <div class="text-sm text-gray-500">UPC: {{ $item->upc }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            {{ $item->unit_display }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            <div>{{ $item->updated_at->format('M j, Y') }}</div>
                                            <div class="text-xs">by {{ $item->lastUpdatedBy->name }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm font-medium">
                                            <a href="{{ route('inventory-items.show', $item) }}" class="text-indigo-600 hover:text-indigo-900">{{ __('View') }}</a>
                                            <a href="{{ route('inventory-items.edit', $item) }}" class="ml-4 text-indigo-600 hover:text-indigo-900">{{ __('Edit') }}</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                            {{ __('No inventory items found.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $items->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 