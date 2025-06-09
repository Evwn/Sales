@props(['inventoryItem' => null])

<div>
    <!-- Basic Information -->
    <div class="md:grid md:grid-cols-3 md:gap-6">
        <div class="md:col-span-1">
            <div class="px-4 sm:px-0">
                <h3 class="text-lg font-medium leading-6 text-gray-900">{{ __('Basic Information') }}</h3>
                <p class="mt-1 text-sm text-gray-600">
                    {{ __('Enter the basic details of the inventory item.') }}
                </p>
            </div>
        </div>
        <div class="mt-5 md:mt-0 md:col-span-2">
            <div class="shadow sm:rounded-md">
                <div class="px-4 py-5 bg-white sm:p-6">
                    <div class="grid grid-cols-6 gap-6">
                        <div class="col-span-6 sm:col-span-4">
                            <x-label for="name" value="{{ __('Name') }}" />
                            <x-input id="name" type="text" class="mt-1 block w-full" name="name" :value="old('name', $inventoryItem?->name)" required autofocus />
                            <x-input-error for="name" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-3">
                            <x-label for="brand" value="{{ __('Brand') }}" />
                            <x-input id="brand" type="text" class="mt-1 block w-full" name="brand" :value="old('brand', $inventoryItem?->brand)" />
                            <x-input-error for="brand" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-3">
                            <x-label for="model" value="{{ __('Model') }}" />
                            <x-input id="model" type="text" class="mt-1 block w-full" name="model" :value="old('model', $inventoryItem?->model)" />
                            <x-input-error for="model" class="mt-2" />
                        </div>

                        <div class="col-span-6">
                            <x-label for="description" value="{{ __('Description') }}" />
                            <textarea id="description" name="description" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">{{ old('description', $inventoryItem?->description) }}</textarea>
                            <x-input-error for="description" class="mt-2" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Identifiers -->
    <div class="hidden sm:block" aria-hidden="true">
        <div class="py-5">
            <div class="border-t border-gray-200"></div>
        </div>
    </div>

    <div class="md:grid md:grid-cols-3 md:gap-6">
        <div class="md:col-span-1">
            <div class="px-4 sm:px-0">
                <h3 class="text-lg font-medium leading-6 text-gray-900">{{ __('Identifiers') }}</h3>
                <p class="mt-1 text-sm text-gray-600">
                    {{ __('Add unique identifiers for the item.') }}
                </p>
            </div>
        </div>
        <div class="mt-5 md:mt-0 md:col-span-2">
            <div class="shadow sm:rounded-md">
                <div class="px-4 py-5 bg-white sm:p-6">
                    <div class="grid grid-cols-6 gap-6">
                        <div class="col-span-6 sm:col-span-3">
                            <x-label for="sku" value="{{ __('SKU') }}" />
                            <x-input id="sku" type="text" class="mt-1 block w-full" name="sku" :value="old('sku', $inventoryItem?->sku)" />
                            <x-input-error for="sku" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-3">
                            <x-label for="barcode" value="{{ __('Barcode') }}" />
                            <x-input id="barcode" type="text" class="mt-1 block w-full" name="barcode" :value="old('barcode', $inventoryItem?->barcode)" />
                            <x-input-error for="barcode" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-2">
                            <x-label for="upc" value="{{ __('UPC') }}" />
                            <x-input id="upc" type="text" class="mt-1 block w-full" name="upc" :value="old('upc', $inventoryItem?->upc)" />
                            <x-input-error for="upc" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-2">
                            <x-label for="ean" value="{{ __('EAN') }}" />
                            <x-input id="ean" type="text" class="mt-1 block w-full" name="ean" :value="old('ean', $inventoryItem?->ean)" />
                            <x-input-error for="ean" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-2">
                            <x-label for="isbn" value="{{ __('ISBN') }}" />
                            <x-input id="isbn" type="text" class="mt-1 block w-full" name="isbn" :value="old('isbn', $inventoryItem?->isbn)" />
                            <x-input-error for="isbn" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-3">
                            <x-label for="mpn" value="{{ __('Manufacturer Part Number (MPN)') }}" />
                            <x-input id="mpn" type="text" class="mt-1 block w-full" name="mpn" :value="old('mpn', $inventoryItem?->mpn)" />
                            <x-input-error for="mpn" class="mt-2" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Unit Information -->
    <div class="hidden sm:block" aria-hidden="true">
        <div class="py-5">
            <div class="border-t border-gray-200"></div>
        </div>
    </div>

    <div class="md:grid md:grid-cols-3 md:gap-6">
        <div class="md:col-span-1">
            <div class="px-4 sm:px-0">
                <h3 class="text-lg font-medium leading-6 text-gray-900">{{ __('Unit Information') }}</h3>
                <p class="mt-1 text-sm text-gray-600">
                    {{ __('Specify how this item is measured or sold.') }}
                </p>
            </div>
        </div>
        <div class="mt-5 md:mt-0 md:col-span-2">
            <div class="shadow sm:rounded-md">
                <div class="px-4 py-5 bg-white sm:p-6">
                    <div class="grid grid-cols-6 gap-6">
                        <div class="col-span-6 sm:col-span-3">
                            <x-label for="unit" value="{{ __('Unit Type') }}" />
                            <select id="unit" name="unit" class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">
                                <option value="">{{ __('Select a unit type') }}</option>
                                @foreach(['piece', 'kg', 'g', 'mg', 'l', 'ml', 'm', 'cm', 'mm'] as $unit)
                                    <option value="{{ $unit }}" {{ old('unit', $inventoryItem?->unit) == $unit ? 'selected' : '' }}>
                                        {{ __(ucfirst($unit)) }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error for="unit" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-3">
                            <x-label for="unit_value" value="{{ __('Unit Value') }}" />
                            <x-input id="unit_value" type="number" step="0.01" class="mt-1 block w-full" name="unit_value" :value="old('unit_value', $inventoryItem?->unit_value)" />
                            <x-input-error for="unit_value" class="mt-2" />
                            <p class="mt-2 text-sm text-gray-500">
                                {{ __('For example: 1 for single piece, 0.5 for half kg, etc.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 