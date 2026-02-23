<x-admin::layouts>
    <x-slot:title>
        @lang('inventory-plus::app.admin.transfers.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('inventory-plus::app.admin.transfers.title')
        </p>

        <a href="{{ route('admin.inventory-plus.transfers.create') }}" class="primary-button">
            @lang('inventory-plus::app.admin.transfers.create')
        </a>
    </div>

    <x-admin::datagrid :src="route('admin.inventory-plus.transfers.index')" />
</x-admin::layouts>
