<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Inventory Item Details') }}
            </h2>
            <a href="{{ route('inventory-items.edit', $inventoryItem) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                {{ __('Edit Item') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Basic Information -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Basic Information') }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div class="text-sm font-medium text-gray-500">{{ __('Name') }}</div>
                            <div class="mt-1">{{ $inventoryItem->name }}</div>
                        </div>

                        <div>
                            <div class="text-sm font-medium text-gray-500">{{ __('Brand') }}</div>
                            <div class="mt-1">{{ $inventoryItem->brand ?? '-' }}</div>
                        </div>

                        <div>
                            <div class="text-sm font-medium text-gray-500">{{ __('Model') }}</div>
                            <div class="mt-1">{{ $inventoryItem->model ?? '-' }}</div>
                        </div>

                        <div>
                            <div class="text-sm font-medium text-gray-500">{{ __('Unit') }}</div>
                            <div class="mt-1">{{ $inventoryItem->unit_display }}</div>
                        </div>

                        @if($inventoryItem->description)
                            <div class="col-span-2">
                                <div class="text-sm font-medium text-gray-500">{{ __('Description') }}</div>
                                <div class="mt-1">{{ $inventoryItem->description }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Identifiers -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Identifiers') }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @if($inventoryItem->sku)
                            <div>
                                <div class="text-sm font-medium text-gray-500">{{ __('SKU') }}</div>
                                <div class="mt-1">{{ $inventoryItem->sku }}</div>
                            </div>
                        @endif

                        @if($inventoryItem->barcode)
                            <div>
                                <div class="text-sm font-medium text-gray-500">{{ __('Barcode') }}</div>
                                <div class="mt-1">{{ $inventoryItem->barcode }}</div>
                            </div>
                        @endif

                        @if($inventoryItem->upc)
                            <div>
                                <div class="text-sm font-medium text-gray-500">{{ __('UPC') }}</div>
                                <div class="mt-1">{{ $inventoryItem->upc }}</div>
                            </div>
                        @endif

                        @if($inventoryItem->ean)
                            <div>
                                <div class="text-sm font-medium text-gray-500">{{ __('EAN') }}</div>
                                <div class="mt-1">{{ $inventoryItem->ean }}</div>
                            </div>
                        @endif

                        @if($inventoryItem->isbn)
                            <div>
                                <div class="text-sm font-medium text-gray-500">{{ __('ISBN') }}</div>
                                <div class="mt-1">{{ $inventoryItem->isbn }}</div>
                            </div>
                        @endif

                        @if($inventoryItem->mpn)
                            <div>
                                <div class="text-sm font-medium text-gray-500">{{ __('MPN') }}</div>
                                <div class="mt-1">{{ $inventoryItem->mpn }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Associated Products -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Associated Products') }}</h3>
                    
                    @if($inventoryItem->products->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Business') }}
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Price') }}
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Stock') }}
                                        </th>
                                        <th scope="col" class="relative px-6 py-3">
                                            <span class="sr-only">{{ __('Actions') }}</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($inventoryItem->products as $product)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $product->business->name }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $product->formatted_price }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $product->stock }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="{{ route('products.show', $product) }}" class="text-indigo-600 hover:text-indigo-900">{{ __('View') }}</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-gray-500 text-sm">
                            {{ __('No products are currently using this inventory item.') }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Metadata -->
            <div class="mt-6 text-sm text-gray-500">
                <div>{{ __('Created by') }} {{ $inventoryItem->creator->name }} {{ __('on') }} {{ $inventoryItem->created_at->format('M j, Y') }}</div>
                <div>{{ __('Last updated by') }} {{ $inventoryItem->lastUpdatedBy->name }} {{ __('on') }} {{ $inventoryItem->updated_at->format('M j, Y') }}</div>
            </div>
        </div>
    </div>
</x-app-layout> 