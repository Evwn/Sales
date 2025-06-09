<!-- Navigation Links -->
<div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
        {{ __('Dashboard') }}
    </x-nav-link>

    <x-nav-link :href="route('inventory-items.index')" :active="request()->routeIs('inventory-items.*')">
        {{ __('Central Inventory') }}
    </x-nav-link>

    <!-- ... existing links ... -->
</div> 