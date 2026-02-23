<x-admin::layouts>
    <x-slot:title>
        @lang('inventory-plus::app.admin.stock-alerts.create-rule')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('inventory-plus::app.admin.stock-alerts.create-rule')
        </p>
    </div>

    <div class="mt-3.5 box-shadow rounded bg-white p-4 dark:bg-gray-900">
        <form method="POST" action="{{ route('admin.inventory-plus.alerts.store') }}">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                {{-- Rule Name --}}
                <x-admin::form.control-group>
                    <x-admin::form.control-group.label class="required">
                        @lang('inventory-plus::app.admin.stock-alerts.name')
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="text"
                        name="name"
                        :value="old('name')"
                        rules="required"
                        :placeholder="trans('inventory-plus::app.admin.stock-alerts.name')"
                    />

                    <x-admin::form.control-group.error control-name="name" />
                </x-admin::form.control-group>

                {{-- Product (optional) --}}
                <x-admin::form.control-group>
                    <x-admin::form.control-group.label>
                        @lang('inventory-plus::app.admin.stock-alerts.product')
                        <span class="text-xs text-gray-400">(@lang('inventory-plus::app.admin.stock-alerts.optional-all'))</span>
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="select"
                        name="product_id"
                        :value="old('product_id')"
                    >
                        <option value="">@lang('inventory-plus::app.admin.stock-alerts.all-products')</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                        @endforeach
                    </x-admin::form.control-group.control>

                    <x-admin::form.control-group.error control-name="product_id" />
                </x-admin::form.control-group>

                {{-- Inventory Source (optional) --}}
                <x-admin::form.control-group>
                    <x-admin::form.control-group.label>
                        @lang('inventory-plus::app.admin.stock-alerts.source')
                        <span class="text-xs text-gray-400">(@lang('inventory-plus::app.admin.stock-alerts.optional-all'))</span>
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="select"
                        name="inventory_source_id"
                        :value="old('inventory_source_id')"
                    >
                        <option value="">@lang('inventory-plus::app.admin.stock-alerts.all-sources')</option>
                        @foreach ($sources as $source)
                            <option value="{{ $source->id }}">{{ $source->name }}</option>
                        @endforeach
                    </x-admin::form.control-group.control>

                    <x-admin::form.control-group.error control-name="inventory_source_id" />
                </x-admin::form.control-group>

                {{-- Low Stock Threshold --}}
                <x-admin::form.control-group>
                    <x-admin::form.control-group.label class="required">
                        @lang('inventory-plus::app.admin.stock-alerts.low-threshold')
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="number"
                        name="low_stock_threshold"
                        :value="old('low_stock_threshold', 10)"
                        rules="required|numeric|min:1"
                    />

                    <x-admin::form.control-group.error control-name="low_stock_threshold" />
                </x-admin::form.control-group>

                {{-- Critical Stock Threshold --}}
                <x-admin::form.control-group>
                    <x-admin::form.control-group.label class="required">
                        @lang('inventory-plus::app.admin.stock-alerts.critical-threshold')
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="number"
                        name="critical_stock_threshold"
                        :value="old('critical_stock_threshold', 3)"
                        rules="required|numeric|min:0"
                    />

                    <x-admin::form.control-group.error control-name="critical_stock_threshold" />
                </x-admin::form.control-group>

                {{-- Notify by Email --}}
                <x-admin::form.control-group>
                    <x-admin::form.control-group.label>
                        @lang('inventory-plus::app.admin.stock-alerts.email-enabled')
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="select"
                        name="notify_email"
                        :value="old('notify_email', 1)"
                    >
                        <option value="1">@lang('inventory-plus::app.admin.stock-alerts.yes')</option>
                        <option value="0">@lang('inventory-plus::app.admin.stock-alerts.no')</option>
                    </x-admin::form.control-group.control>
                </x-admin::form.control-group>

                {{-- Email Recipients --}}
                <x-admin::form.control-group>
                    <x-admin::form.control-group.label>
                        @lang('inventory-plus::app.admin.stock-alerts.recipients')
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="text"
                        name="email_recipients"
                        :value="old('email_recipients')"
                        :placeholder="trans('inventory-plus::app.admin.stock-alerts.recipients-placeholder')"
                    />

                    <x-admin::form.control-group.error control-name="email_recipients" />
                    <p class="mt-1 text-xs text-gray-400">
                        @lang('inventory-plus::app.admin.stock-alerts.recipients-help')
                    </p>
                </x-admin::form.control-group>
            </div>

            <div class="mt-4 flex items-center gap-2">
                <button type="submit" class="primary-button">
                    @lang('inventory-plus::app.admin.stock-alerts.save')
                </button>

                <a href="{{ route('admin.inventory-plus.alerts.index') }}" class="secondary-button">
                    @lang('inventory-plus::app.admin.stock-alerts.cancel')
                </a>
            </div>
        </form>
    </div>
</x-admin::layouts>
