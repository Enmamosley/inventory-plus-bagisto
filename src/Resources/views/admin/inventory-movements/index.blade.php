<x-admin::layouts>
    <x-slot:title>
        @lang('inventory-plus::app.admin.movements.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('inventory-plus::app.admin.movements.title')
        </p>

        <div class="flex items-center gap-x-2.5">
            <a href="{{ route('admin.inventory-plus.movements.create') }}"
               class="primary-button">
                @lang('inventory-plus::app.admin.movements.create')
            </a>
        </div>
    </div>

    <x-admin::datagrid :src="route('admin.inventory-plus.movements.index')" />
</x-admin::layouts>
