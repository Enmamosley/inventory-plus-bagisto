<x-admin::layouts>
    <x-slot:title>
        @lang('inventory-plus::app.admin.barcode.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('inventory-plus::app.admin.barcode.title')
        </p>
    </div>

    {{-- MAIN: Scan & Update Stock --}}
    <div class="mt-3.5 box-shadow rounded bg-white p-4 dark:bg-gray-900">
        <h3 class="mb-3 text-lg font-semibold">📦 @lang('inventory-plus::app.admin.barcode.scan-update')</h3>

        <div class="flex gap-2">
            <input type="text"
                   id="barcode-input"
                   class="flex-1 rounded border px-3 py-2 text-lg dark:border-gray-700 dark:bg-gray-800"
                   placeholder="@lang('inventory-plus::app.admin.barcode.scan-placeholder')"
                   autofocus
                   onkeydown="if(event.key==='Enter'){event.preventDefault();lookupBarcode();}">
            <button onclick="lookupBarcode()" class="primary-button">
                🔍 @lang('inventory-plus::app.admin.barcode.search')
            </button>
        </div>

        <div id="lookup-error" class="mt-4 hidden rounded bg-red-50 p-3 text-red-600 dark:bg-red-900/20 dark:text-red-400">
            @lang('inventory-plus::app.admin.barcode.not-found')
        </div>

        <div id="lookup-success" class="mt-4 hidden rounded bg-green-50 p-3 text-green-700 dark:bg-green-900/20 dark:text-green-400">
        </div>

        {{-- Product Info + Stock Editor (hidden until scan) --}}
        <div id="product-panel" class="mt-4 hidden">
            <div class="rounded border dark:border-gray-700">
                {{-- Product Header --}}
                <div class="flex items-center justify-between border-b bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                    <div>
                        <h4 class="text-lg font-bold" id="product-name"></h4>
                        <p class="text-sm text-gray-500" id="product-sku"></p>
                    </div>
                    <div id="product-barcode-badge" class="rounded bg-blue-100 px-3 py-1 font-mono text-sm text-blue-700 dark:bg-blue-900/30 dark:text-blue-300"></div>
                </div>

                {{-- Stock Per Source + Quick Edit --}}
                <div class="p-4">
                    <h5 class="mb-3 font-semibold">@lang('inventory-plus::app.admin.barcode.stock-by-source')</h5>
                    <div id="stock-sources"></div>

                    <input type="hidden" id="current-product-id" value="">

                    {{-- Reason / Notes --}}
                    <div class="mt-3 flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-600 dark:text-gray-400 whitespace-nowrap">
                            📝 @lang('inventory-plus::app.admin.barcode.reason'):
                        </label>
                        <select id="reason-preset" class="rounded border px-2 py-1.5 text-sm dark:border-gray-700 dark:bg-gray-800" onchange="applyReasonPreset()">
                            <option value="">@lang('inventory-plus::app.admin.barcode.reason-select')...</option>
                            <option value="physical_count">@lang('inventory-plus::app.admin.barcode.reason-count')</option>
                            <option value="damaged">@lang('inventory-plus::app.admin.barcode.reason-damaged')</option>
                            <option value="return">@lang('inventory-plus::app.admin.barcode.reason-return')</option>
                            <option value="receiving">@lang('inventory-plus::app.admin.barcode.reason-receiving')</option>
                            <option value="correction">@lang('inventory-plus::app.admin.barcode.reason-correction')</option>
                            <option value="custom">@lang('inventory-plus::app.admin.barcode.reason-custom')</option>
                        </select>
                        <input type="text"
                               id="reason-text"
                               class="flex-1 rounded border px-2 py-1.5 text-sm dark:border-gray-700 dark:bg-gray-800"
                               placeholder="@lang('inventory-plus::app.admin.barcode.reason-placeholder')"
                               maxlength="500">
                    </div>
                </div>

                {{-- Quick Actions --}}
                <div class="border-t bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-wrap gap-2">
                        <a id="link-product-edit" href="#" class="secondary-button text-sm" target="_blank">
                            ✏️ @lang('inventory-plus::app.admin.barcode.edit-product')
                        </a>
                        <a id="link-product-history" href="#" class="secondary-button text-sm">
                            📋 @lang('inventory-plus::app.admin.barcode.view-history')
                        </a>
                        <button onclick="document.getElementById('barcode-input').value='';document.getElementById('barcode-input').focus();" class="secondary-button text-sm">
                            🔄 @lang('inventory-plus::app.admin.barcode.scan-next')
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 grid grid-cols-2 gap-4">
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

        {{-- Print Labels Section --}}
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
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

                <x-admin::form.control-group>
                    <x-admin::form.control-group.label>
                        @lang('inventory-plus::app.admin.barcode.copies')
                    </x-admin::form.control-group.label>
                    <input type="number" name="copies" value="1" min="1" max="100"
                           class="w-full rounded border px-3 py-2 dark:border-gray-700 dark:bg-gray-800">
                </x-admin::form.control-group>

                <div id="hidden-product-ids"></div>

                <button type="submit" class="primary-button mt-3" onclick="prepareProductIds()">
                    @lang('inventory-plus::app.admin.barcode.print-labels')
                </button>
            </form>
        </div>
    </div>

    <script>
        let currentProduct = null;

        async function lookupBarcode() {
            const barcode = document.getElementById('barcode-input').value.trim();
            if (!barcode) return;

            document.getElementById('product-panel').classList.add('hidden');
            document.getElementById('lookup-error').classList.add('hidden');
            document.getElementById('lookup-success').classList.add('hidden');

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
                currentProduct = data.product;
                renderProductPanel(currentProduct);
            } catch (e) {
                document.getElementById('lookup-error').classList.remove('hidden');
            }
        }

        function renderProductPanel(p) {
            document.getElementById('product-name').textContent = p.name;
            document.getElementById('product-sku').textContent = 'SKU: ' + p.sku;
            document.getElementById('product-barcode-badge').textContent = p.barcode;
            document.getElementById('current-product-id').value = p.id;

            // Build stock editor per source
            let html = '';
            p.inventories.forEach((inv, idx) => {
                html += `
                <div class="mb-3 flex items-center gap-3 rounded border p-3 dark:border-gray-700" id="source-row-${inv.source_id}">
                    <div class="flex-1">
                        <span class="font-medium">${inv.source_name}</span>
                        <span class="ml-2 text-sm text-gray-400">@lang('inventory-plus::app.admin.barcode.current'):</span>
                        <span class="font-bold text-lg" id="current-qty-${inv.source_id}">${inv.qty}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <select id="action-${inv.source_id}" class="rounded border px-2 py-1.5 text-sm dark:border-gray-700 dark:bg-gray-800">
                            <option value="set">@lang('inventory-plus::app.admin.barcode.action-set')</option>
                            <option value="add">@lang('inventory-plus::app.admin.barcode.action-add')</option>
                            <option value="subtract">@lang('inventory-plus::app.admin.barcode.action-subtract')</option>
                        </select>
                        <input type="number"
                               id="qty-${inv.source_id}"
                               class="w-24 rounded border px-2 py-1.5 text-center text-sm dark:border-gray-700 dark:bg-gray-800"
                               placeholder="0"
                               min="0"
                               onkeydown="if(event.key==='Enter'){event.preventDefault();updateStock(${inv.source_id});}">
                        <button onclick="updateStock(${inv.source_id})" class="rounded bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-700">
                            ✓ @lang('inventory-plus::app.admin.barcode.update')
                        </button>
                    </div>
                </div>`;
            });

            if (p.inventories.length === 0) {
                html = '<p class="text-sm text-gray-400">@lang('inventory-plus::app.admin.barcode.no-inventory')</p>';
            }

            document.getElementById('stock-sources').innerHTML = html;

            // Links
            document.getElementById('link-product-edit').href = '{{ url(config("app.admin_url", "admin") . "/catalog/products/edit") }}/' + p.id;
            document.getElementById('link-product-history').href = '{{ route("admin.inventory-plus.movements.product-history", ":id") }}'.replace(':id', p.id);

            document.getElementById('product-panel').classList.remove('hidden');
        }

        async function updateStock(sourceId) {
            const productId = document.getElementById('current-product-id').value;
            const action = document.getElementById('action-' + sourceId).value;
            const qtyInput = document.getElementById('qty-' + sourceId);
            const qty = parseInt(qtyInput.value);
            const reason = getReasonText();

            if (isNaN(qty) || qty < 0) {
                qtyInput.classList.add('border-red-500');
                return;
            }
            qtyInput.classList.remove('border-red-500');

            const row = document.getElementById('source-row-' + sourceId);
            row.style.opacity = '0.5';

            try {
                const response = await fetch('{{ route("admin.inventory-plus.barcode.update-stock") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        inventory_source_id: sourceId,
                        action: action,
                        qty: qty,
                        reason: reason || null,
                    }),
                });

                const data = await response.json();
                row.style.opacity = '1';

                if (data.success) {
                    // Update displayed qty
                    document.getElementById('current-qty-' + sourceId).textContent = data.new_qty;
                    qtyInput.value = '';

                    // Flash green
                    row.classList.add('border-green-500', 'bg-green-50', 'dark:bg-green-900/20');
                    setTimeout(() => {
                        row.classList.remove('border-green-500', 'bg-green-50', 'dark:bg-green-900/20');
                    }, 1500);

                    // Show success message
                    const msg = document.getElementById('lookup-success');
                    msg.textContent = data.message;
                    msg.classList.remove('hidden');
                    setTimeout(() => msg.classList.add('hidden'), 3000);
                } else {
                    alert(data.message || 'Error updating stock');
                }
            } catch (e) {
                row.style.opacity = '1';
                alert('Error: ' + e.message);
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

        // Auto-focus input on page load
        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('barcode-input').focus();
        });

        const reasonLabels = {
            physical_count: '@lang('inventory-plus::app.admin.barcode.reason-count')',
            damaged: '@lang('inventory-plus::app.admin.barcode.reason-damaged')',
            'return': '@lang('inventory-plus::app.admin.barcode.reason-return')',
            receiving: '@lang('inventory-plus::app.admin.barcode.reason-receiving')',
            correction: '@lang('inventory-plus::app.admin.barcode.reason-correction')',
        };

        function applyReasonPreset() {
            const preset = document.getElementById('reason-preset').value;
            const textInput = document.getElementById('reason-text');

            if (preset && preset !== 'custom') {
                textInput.value = reasonLabels[preset] || '';
            } else if (preset === 'custom') {
                textInput.value = '';
                textInput.focus();
            }
        }

        function getReasonText() {
            const preset = document.getElementById('reason-preset').value;
            const text = document.getElementById('reason-text').value.trim();

            if (text) {
                return text;
            }

            if (preset && preset !== 'custom' && reasonLabels[preset]) {
                return reasonLabels[preset];
            }

            return '';
        }
    </script>
</x-admin::layouts>
