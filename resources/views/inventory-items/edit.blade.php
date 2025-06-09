<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Inventory Item') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <form method="POST" action="{{ route('inventory-items.update', $inventoryItem) }}" class="p-6">
                    @csrf
                    @method('PUT')

                    <x-inventory-items.form :inventoryItem="$inventoryItem" />

                    <div class="mt-6 flex justify-end">
                        <x-button>
                            {{ __('Update Item') }}
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout> 