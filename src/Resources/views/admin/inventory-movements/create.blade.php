<x-admin::layouts>
    <x-slot:title>
        @lang('inventory-plus::app.admin.movements.create-title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('inventory-plus::app.admin.movements.create-title')
        </p>
    </div>

    <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
        <div class="flex flex-1 flex-col gap-2 overflow-auto max-xl:flex-auto">
            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <form method="POST" action="{{ route('admin.inventory-plus.movements.store') }}">
                    @csrf

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('inventory-plus::app.admin.movements.product')
                        </x-admin::form.control-group.label>
                        <x-admin::form.control-group.control
                            type="text"
                            name="product_id"
                            :value="old('product_id')"
                            :placeholder="trans('inventory-plus::app.admin.movements.select-product')"
                            rules="required"
                        />
                        <x-admin::form.control-group.error control-name="product_id" />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('inventory-plus::app.admin.movements.source')
                        </x-admin::form.control-group.label>
                        <x-admin::form.control-group.control
                            type="select"
                            name="inventory_source_id"
                            rules="required"
                        >
                            <option value="">@lang('inventory-plus::app.admin.movements.select-source')</option>
                            @foreach($sources as $source)
                                <option value="{{ $source->id }}">{{ $source->name }}</option>
                            @endforeach
                        </x-admin::form.control-group.control>
                        <x-admin::form.control-group.error control-name="inventory_source_id" />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('inventory-plus::app.admin.movements.type')
                        </x-admin::form.control-group.label>
                        <x-admin::form.control-group.control
                            type="select"
                            name="type"
                            rules="required"
                        >
                            <option value="">@lang('inventory-plus::app.admin.movements.select-type')</option>
                            @foreach($types as $type)
                                <option value="{{ $type->value }}">{{ $type->label() }}</option>
                            @endforeach
                        </x-admin::form.control-group.control>
                        <x-admin::form.control-group.error control-name="type" />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('inventory-plus::app.admin.movements.qty')
                        </x-admin::form.control-group.label>
                        <x-admin::form.control-group.control
                            type="number"
                            name="qty_change"
                            :value="old('qty_change')"
                            rules="required"
                        />
                        <p class="mt-1 text-xs text-gray-500">Use positive (+) for stock in, negative (-) for stock out.</p>
                        <x-admin::form.control-group.error control-name="qty_change" />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('inventory-plus::app.admin.movements.reason')
                        </x-admin::form.control-group.label>
                        <x-admin::form.control-group.control
                            type="textarea"
                            name="reason"
                            :value="old('reason')"
                        />
                    </x-admin::form.control-group>

                    <div class="mt-4 flex items-center gap-2">
                        <button type="submit" class="primary-button">
                            @lang('inventory-plus::app.admin.movements.create')
                        </button>

                        <a href="{{ route('admin.inventory-plus.movements.index') }}" class="transparent-button">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin::layouts>
