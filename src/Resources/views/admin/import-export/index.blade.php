<x-admin::layouts>
    <x-slot:title>
        @lang('inventory-plus::app.admin.import-export.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('inventory-plus::app.admin.import-export.title')
        </p>
    </div>

    <div class="mt-3.5 grid grid-cols-2 gap-4">
        {{-- Export Section --}}
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <h3 class="mb-3 text-lg font-semibold">📤 @lang('inventory-plus::app.admin.import-export.export')</h3>
            <p class="mb-3 text-sm text-gray-500">@lang('inventory-plus::app.admin.import-export.export-desc')</p>

            <form method="POST" action="{{ route('admin.inventory-plus.import-export.export') }}">
                @csrf

                <x-admin::form.control-group>
                    <x-admin::form.control-group.label>
                        @lang('inventory-plus::app.admin.import-export.source')
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="select"
                        name="inventory_source_id"
                    >
                        <option value="">@lang('inventory-plus::app.admin.import-export.all-sources')</option>
                        @foreach ($sources as $source)
                            <option value="{{ $source->id }}">{{ $source->name }}</option>
                        @endforeach
                    </x-admin::form.control-group.control>
                </x-admin::form.control-group>

                <button type="submit" class="primary-button mt-2">
                    @lang('inventory-plus::app.admin.import-export.download-csv')
                </button>
            </form>
        </div>

        {{-- Import Section --}}
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <h3 class="mb-3 text-lg font-semibold">📥 @lang('inventory-plus::app.admin.import-export.import')</h3>
            <p class="mb-3 text-sm text-gray-500">@lang('inventory-plus::app.admin.import-export.import-desc')</p>

            <form method="POST" action="{{ route('admin.inventory-plus.import-export.import') }}" enctype="multipart/form-data">
                @csrf

                <x-admin::form.control-group>
                    <x-admin::form.control-group.label class="required">
                        @lang('inventory-plus::app.admin.import-export.csv-file')
                    </x-admin::form.control-group.label>

                    <input type="file"
                           name="file"
                           accept=".csv"
                           required
                           class="w-full rounded border px-3 py-2 dark:border-gray-700 dark:bg-gray-800">

                    <x-admin::form.control-group.error control-name="file" />
                </x-admin::form.control-group>

                <x-admin::form.control-group>
                    <x-admin::form.control-group.label class="required">
                        @lang('inventory-plus::app.admin.import-export.action')
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="select"
                        name="action"
                        rules="required"
                    >
                        <option value="set">@lang('inventory-plus::app.admin.import-export.action-set')</option>
                        <option value="add">@lang('inventory-plus::app.admin.import-export.action-add')</option>
                    </x-admin::form.control-group.control>

                    <p class="mt-1 text-xs text-gray-400">
                        @lang('inventory-plus::app.admin.import-export.action-help')
                    </p>
                </x-admin::form.control-group>

                <button type="submit" class="primary-button mt-2">
                    @lang('inventory-plus::app.admin.import-export.upload-import')
                </button>
            </form>
        </div>
    </div>

    {{-- Template Download --}}
    <div class="mt-4 box-shadow rounded bg-white p-4 dark:bg-gray-900">
        <h3 class="mb-2 text-lg font-semibold">📋 @lang('inventory-plus::app.admin.import-export.template')</h3>
        <p class="mb-3 text-sm text-gray-500">@lang('inventory-plus::app.admin.import-export.template-desc')</p>

        <a href="{{ route('admin.inventory-plus.import-export.template') }}" class="secondary-button">
            @lang('inventory-plus::app.admin.import-export.download-template')
        </a>

        <div class="mt-3 rounded bg-gray-50 p-3 dark:bg-gray-800">
            <p class="text-sm font-medium">@lang('inventory-plus::app.admin.import-export.csv-format'):</p>
            <code class="mt-1 block text-xs text-gray-600 dark:text-gray-300">
                sku,source_code,qty,action<br>
                PROD-001,default,50,set<br>
                PROD-002,warehouse-1,+10,add
            </code>
        </div>
    </div>

    @if (session('import_result'))
        <div class="mt-4 box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <h3 class="mb-2 text-lg font-semibold">@lang('inventory-plus::app.admin.import-export.import-results')</h3>

            @php $result = session('import_result'); @endphp

            <div class="grid grid-cols-3 gap-4">
                <div class="rounded bg-green-50 p-3 text-center">
                    <p class="text-2xl font-bold text-green-600">{{ $result['success'] ?? 0 }}</p>
                    <p class="text-sm text-green-700">@lang('inventory-plus::app.admin.import-export.success')</p>
                </div>
                <div class="rounded bg-red-50 p-3 text-center">
                    <p class="text-2xl font-bold text-red-600">{{ $result['failed'] ?? 0 }}</p>
                    <p class="text-sm text-red-700">@lang('inventory-plus::app.admin.import-export.failed')</p>
                </div>
                <div class="rounded bg-blue-50 p-3 text-center">
                    <p class="text-2xl font-bold text-blue-600">{{ $result['total'] ?? 0 }}</p>
                    <p class="text-sm text-blue-700">@lang('inventory-plus::app.admin.import-export.total')</p>
                </div>
            </div>

            @if (! empty($result['errors']))
                <div class="mt-3">
                    <p class="text-sm font-medium text-red-600">@lang('inventory-plus::app.admin.import-export.errors'):</p>
                    <ul class="ml-4 mt-1 list-disc text-sm text-red-500">
                        @foreach ($result['errors'] as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif
</x-admin::layouts>
