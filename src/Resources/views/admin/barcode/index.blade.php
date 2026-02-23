<x-admin::layouts>
    <x-slot:title>
        @lang('inventory-plus::app.admin.barcode.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('inventory-plus::app.admin.barcode.title')
        </p>
    </div>

    <div class="mt-3.5 grid grid-cols-2 gap-4">
        {{-- Barcode Lookup --}}
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <h3 class="mb-3 text-lg font-semibold">🔍 @lang('inventory-plus::app.admin.barcode.search')</h3>

            <div class="flex gap-2">
                <input type="text"
                       id="barcode-input"
                       class="flex-1 rounded border px-3 py-2 dark:border-gray-700 dark:bg-gray-800"
                       placeholder="@lang('inventory-plus::app.admin.barcode.scan-placeholder')"
                       autofocus
                       onkeydown="if(event.key==='Enter'){event.preventDefault();lookupBarcode();}">
                <button onclick="lookupBarcode()" class="primary-button">
                    @lang('inventory-plus::app.admin.barcode.search')
                </button>
            </div>

            <div id="lookup-result" class="mt-4 hidden">
                <div class="rounded border p-4">
                    <h4 class="font-semibold" id="product-name"></h4>
                    <p class="text-sm text-gray-500" id="product-sku"></p>
                    <div id="product-barcode-img" class="my-2"></div>
                    <div id="product-inventories" class="mt-2"></div>
                </div>
            </div>

            <div id="lookup-error" class="mt-4 hidden rounded bg-red-50 p-3 text-red-600">
                @lang('inventory-plus::app.admin.barcode.not-found')
            </div>
        </div>

        {{-- Barcode Generator --}}
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <h3 class="mb-3 text-lg font-semibold">🏷️ @lang('inventory-plus::app.admin.barcode.generate')</h3>

            <div class="space-y-3">
                <x-admin::form.control-group>
                    <x-admin::form.control-group.label>
                        @lang('inventory-plus::app.admin.barcode.barcode-value')
                    </x-admin::form.control-group.label>
                    <input type="text"
                           id="gen-value"
                           class="w-full rounded border px-3 py-2 dark:border-gray-700 dark:bg-gray-800"
                           placeholder="Enter barcode value">
                </x-admin::form.control-group>

                <x-admin::form.control-group>
                    <x-admin::form.control-group.label>
                        @lang('inventory-plus::app.admin.barcode.barcode-type')
                    </x-admin::form.control-group.label>
                    <select id="gen-type" class="w-full rounded border px-3 py-2 dark:border-gray-700 dark:bg-gray-800">
                        <option value="EAN-13">EAN-13</option>
                        <option value="EAN-8">EAN-8</option>
                        <option value="UPC-A">UPC-A</option>
                        <option value="CODE-128" selected>Code 128</option>
                        <option value="CODE-39">Code 39</option>
                    </select>
                </x-admin::form.control-group>

                <button onclick="generateBarcode()" class="primary-button">
                    @lang('inventory-plus::app.admin.barcode.preview')
                </button>

                <div id="gen-preview" class="mt-3 hidden text-center">
                    <div id="gen-barcode-img"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Print Labels Section --}}
    <div class="mt-4 box-shadow rounded bg-white p-4 dark:bg-gray-900">
        <h3 class="mb-3 text-lg font-semibold">🖨️ @lang('inventory-plus::app.admin.barcode.print-labels')</h3>

        <form method="POST" action="{{ route('admin.inventory-plus.barcode.print-labels') }}" target="_blank">
            @csrf

            <x-admin::form.control-group>
                <x-admin::form.control-group.label>
                    @lang('inventory-plus::app.admin.barcode.select-products')
                </x-admin::form.control-group.label>
                <input type="text"
                       name="product_ids_text"
                       class="w-full rounded border px-3 py-2 dark:border-gray-700 dark:bg-gray-800"
                       placeholder="Enter product IDs (comma-separated): 1, 2, 3"
                       id="label-product-ids">
            </x-admin::form.control-group>

            <div class="grid grid-cols-2 gap-4">
                <x-admin::form.control-group>
                    <x-admin::form.control-group.label>
                        @lang('inventory-plus::app.admin.barcode.copies')
                    </x-admin::form.control-group.label>
                    <input type="number" name="copies" value="1" min="1" max="100"
                           class="w-full rounded border px-3 py-2 dark:border-gray-700 dark:bg-gray-800">
                </x-admin::form.control-group>
            </div>

            <div id="hidden-product-ids"></div>

            <button type="submit" class="primary-button mt-3" onclick="prepareProductIds()">
                @lang('inventory-plus::app.admin.barcode.print-labels')
            </button>
        </form>
    </div>

    <script>
        async function lookupBarcode() {
            const barcode = document.getElementById('barcode-input').value.trim();
            if (!barcode) return;

            document.getElementById('lookup-result').classList.add('hidden');
            document.getElementById('lookup-error').classList.add('hidden');

            try {
                const response = await fetch('{{ route("admin.inventory-plus.barcode.lookup") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ barcode }),
                });

                if (!response.ok) {
                    document.getElementById('lookup-error').classList.remove('hidden');
                    return;
                }

                const data = await response.json();
                const p = data.product;

                document.getElementById('product-name').textContent = p.name;
                document.getElementById('product-sku').textContent = 'SKU: ' + p.sku + ' | Barcode: ' + p.barcode;

                // Show inventories
                let invHtml = '<div class="mt-2 text-sm"><strong>Stock:</strong><ul class="ml-4 list-disc">';
                p.inventories.forEach(inv => {
                    invHtml += `<li>${inv.source_name}: <strong>${inv.qty}</strong></li>`;
                });
                invHtml += '</ul></div>';
                document.getElementById('product-inventories').innerHTML = invHtml;

                document.getElementById('lookup-result').classList.remove('hidden');
            } catch (e) {
                document.getElementById('lookup-error').classList.remove('hidden');
            }
        }

        async function generateBarcode() {
            const value = document.getElementById('gen-value').value.trim();
            const type = document.getElementById('gen-type').value;
            if (!value) return;

            try {
                const response = await fetch('{{ route("admin.inventory-plus.barcode.generate") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ value, type, format: 'png' }),
                });

                const data = await response.json();
                document.getElementById('gen-barcode-img').innerHTML =
                    `<img src="${data.barcode}" alt="barcode" style="max-width:100%;"><p class="mt-1 font-mono text-sm">${value}</p>`;
                document.getElementById('gen-preview').classList.remove('hidden');
            } catch (e) {
                alert('Error generating barcode');
            }
        }

        function prepareProductIds() {
            const text = document.getElementById('label-product-ids').value;
            const ids = text.split(',').map(s => s.trim()).filter(s => s && !isNaN(s));
            const container = document.getElementById('hidden-product-ids');
            container.innerHTML = '';
            ids.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'product_ids[]';
                input.value = id;
                container.appendChild(input);
            });
        }
    </script>
</x-admin::layouts>
