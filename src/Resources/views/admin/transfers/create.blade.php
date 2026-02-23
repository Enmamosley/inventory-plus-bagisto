<x-admin::layouts>
    <x-slot:title>
        @lang('inventory-plus::app.admin.transfers.create-title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('inventory-plus::app.admin.transfers.create-title')
        </p>
    </div>

    <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
        <div class="flex flex-1 flex-col gap-2 overflow-auto max-xl:flex-auto">
            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <form method="POST" action="{{ route('admin.inventory-plus.transfers.store') }}" id="transfer-form">
                    @csrf

                    <div class="grid grid-cols-2 gap-4">
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('inventory-plus::app.admin.transfers.source')
                            </x-admin::form.control-group.label>
                            <x-admin::form.control-group.control
                                type="select"
                                name="source_id"
                                rules="required"
                            >
                                <option value="">-- Select --</option>
                                @foreach($sources as $source)
                                    <option value="{{ $source->id }}">{{ $source->name }}</option>
                                @endforeach
                            </x-admin::form.control-group.control>
                            <x-admin::form.control-group.error control-name="source_id" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('inventory-plus::app.admin.transfers.destination')
                            </x-admin::form.control-group.label>
                            <x-admin::form.control-group.control
                                type="select"
                                name="destination_id"
                                rules="required"
                            >
                                <option value="">-- Select --</option>
                                @foreach($sources as $source)
                                    <option value="{{ $source->id }}">{{ $source->name }}</option>
                                @endforeach
                            </x-admin::form.control-group.control>
                            <x-admin::form.control-group.error control-name="destination_id" />
                        </x-admin::form.control-group>
                    </div>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('inventory-plus::app.admin.transfers.notes')
                        </x-admin::form.control-group.label>
                        <x-admin::form.control-group.control
                            type="textarea"
                            name="notes"
                        />
                    </x-admin::form.control-group>

                    <div class="mt-4">
                        <h3 class="mb-2 text-lg font-semibold">@lang('inventory-plus::app.admin.transfers.items')</h3>

                        <div id="transfer-items">
                            <div class="mb-2 grid grid-cols-3 gap-2">
                                <x-admin::form.control-group.control
                                    type="text"
                                    name="items[0][product_id]"
                                    placeholder="Product ID"
                                    rules="required"
                                />
                                <x-admin::form.control-group.control
                                    type="number"
                                    name="items[0][qty]"
                                    placeholder="Quantity"
                                    rules="required|min:1"
                                />
                            </div>
                        </div>

                        <button type="button" class="secondary-button mt-2" onclick="addTransferItem()">
                            + @lang('inventory-plus::app.admin.transfers.add-item')
                        </button>
                    </div>

                    <div class="mt-4 flex items-center gap-2">
                        <button type="submit" class="primary-button">
                            @lang('inventory-plus::app.admin.transfers.create')
                        </button>
                        <a href="{{ route('admin.inventory-plus.transfers.index') }}" class="transparent-button">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let itemIndex = 1;
        function addTransferItem() {
            const container = document.getElementById('transfer-items');
            const row = document.createElement('div');
            row.className = 'mb-2 grid grid-cols-3 gap-2';
            row.innerHTML = `
                <input type="text" name="items[${itemIndex}][product_id]" placeholder="Product ID" class="rounded border px-3 py-2" required>
                <input type="number" name="items[${itemIndex}][qty]" placeholder="Quantity" class="rounded border px-3 py-2" required min="1">
                <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700">✕ Remove</button>
            `;
            container.appendChild(row);
            itemIndex++;
        }
    </script>
</x-admin::layouts>
