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

    {{-- Print Labels Section --}}
    <div class="mt-4 box-shadow rounded bg-white p-4 dark:bg-gray-900">
        <h3 class="mb-3 text-lg font-semibold">🖨️ @lang('inventory-plus::app.admin.barcode.print-labels')</h3>

        {{-- Search & Filter Row --}}
        <div class="flex flex-wrap gap-3 mb-4">
            {{-- Category Filter --}}
            <div class="w-64">
                <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">
                    @lang('inventory-plus::app.admin.barcode.filter-category')
                </label>
                <select id="label-category-filter" class="w-full rounded border px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800" onchange="filterByCategory()">
                    <option value="">@lang('inventory-plus::app.admin.barcode.all-categories')</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat['id'] }}">{{ $cat['name'] }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Product Search --}}
            <div class="flex-1 min-w-[250px] relative">
                <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">
                    @lang('inventory-plus::app.admin.barcode.search-product')
                </label>
                <input type="text"
                       id="label-search-input"
                       class="w-full rounded border px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800"
                       placeholder="@lang('inventory-plus::app.admin.barcode.search-product-placeholder')"
                       oninput="debounceSearch()"
                       onkeydown="if(event.key==='Escape'){closeSearchResults();}">
                {{-- Search Results Dropdown --}}
                <div id="search-results-dropdown" class="absolute z-50 mt-1 hidden w-full max-h-64 overflow-y-auto rounded border bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800">
                </div>
            </div>

            {{-- Load by Category Button --}}
            <div class="flex items-end">
                <button type="button" onclick="loadCategoryProducts()" class="rounded border border-blue-600 bg-blue-50 px-3 py-2 text-sm font-medium text-blue-700 hover:bg-blue-100 dark:border-blue-500 dark:bg-blue-900/30 dark:text-blue-300 dark:hover:bg-blue-900/50">
                    📂 @lang('inventory-plus::app.admin.barcode.load-category')
                </button>
            </div>
        </div>

        {{-- Selected Products Table --}}
        <div id="selected-products-container" class="mb-4 hidden">
            <div class="flex items-center justify-between mb-2">
                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                    @lang('inventory-plus::app.admin.barcode.selected-products') (<span id="selected-count">0</span>)
                </h4>
                <button type="button" onclick="clearAllSelected()" class="text-xs text-red-600 hover:text-red-800 dark:text-red-400">
                    ✕ @lang('inventory-plus::app.admin.barcode.clear-all')
                </button>
            </div>
            <div class="rounded border dark:border-gray-700 overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400">@lang('inventory-plus::app.admin.barcode.product-name')</th>
                            <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400">SKU</th>
                            <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400">@lang('inventory-plus::app.admin.barcode.barcode-status')</th>
                            <th class="px-3 py-2 text-center font-medium text-gray-500 dark:text-gray-400">@lang('inventory-plus::app.admin.barcode.copies')</th>
                            <th class="px-3 py-2 w-10"></th>
                        </tr>
                    </thead>
                    <tbody id="selected-products-tbody">
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Print Form --}}
        <form method="POST" action="{{ route('admin.inventory-plus.barcode.print-labels') }}" target="_blank" id="print-labels-form">
            @csrf
            <div id="hidden-product-ids"></div>

            {{-- Global Copies --}}
            <div class="flex items-center gap-3 mb-3">
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400 whitespace-nowrap">
                    @lang('inventory-plus::app.admin.barcode.global-copies'):
                </label>
                <input type="number" id="global-copies" value="1" min="1" max="100"
                       class="w-20 rounded border px-2 py-1.5 text-center text-sm dark:border-gray-700 dark:bg-gray-800"
                       onchange="applyGlobalCopies()">
                <button type="button" onclick="applyGlobalCopies()" class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400">
                    @lang('inventory-plus::app.admin.barcode.apply-all')
                </button>
            </div>

            <button type="submit" class="primary-button" id="print-btn" disabled onclick="preparePrintForm()">
                🖨️ @lang('inventory-plus::app.admin.barcode.print-labels')
            </button>
            <span id="no-barcode-warning" class="ml-3 hidden text-xs text-amber-600 dark:text-amber-400">
                ⚠️ @lang('inventory-plus::app.admin.barcode.no-barcode-warning')
            </span>
        </form>
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

        function prepareProductIds() {
            // Legacy removed — now handled by preparePrintForm
        }

        // ========== PRINT LABELS: Search, Select & Print ==========
        let selectedProducts = {};  // { productId: { id, sku, name, barcode, barcode_type, price, copies } }
        let searchTimer = null;

        function debounceSearch() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => searchProducts(), 300);
        }

        async function searchProducts() {
            const query = document.getElementById('label-search-input').value.trim();
            const categoryId = document.getElementById('label-category-filter').value;
            const dropdown = document.getElementById('search-results-dropdown');

            if (!query && !categoryId) {
                dropdown.classList.add('hidden');
                return;
            }

            if (query.length > 0 && query.length < 2) return;

            try {
                const response = await fetch('{{ route("admin.inventory-plus.barcode.search-products") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ query, category_id: categoryId || null }),
                });

                const data = await response.json();
                renderSearchResults(data.products || []);
            } catch (e) {
                console.error('Search error:', e);
            }
        }

        function renderSearchResults(products) {
            const dropdown = document.getElementById('search-results-dropdown');

            if (!products.length) {
                dropdown.innerHTML = '<div class="px-3 py-2 text-sm text-gray-400">@lang('inventory-plus::app.admin.barcode.no-results')</div>';
                dropdown.classList.remove('hidden');
                return;
            }

            let html = '';
            products.forEach(p => {
                const isSelected = selectedProducts[p.id];
                const barcodeIcon = p.barcode ? '✅' : '⚠️';
                const selectedClass = isSelected ? 'bg-blue-50 dark:bg-blue-900/20' : 'hover:bg-gray-50 dark:hover:bg-gray-700';

                html += `
                <div class="flex items-center justify-between px-3 py-2 cursor-pointer border-b dark:border-gray-700 last:border-0 ${selectedClass}"
                     onclick="toggleProduct(${JSON.stringify(p).replace(/"/g, '&quot;')})">
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium truncate">${p.name}</div>
                        <div class="text-xs text-gray-400">SKU: ${p.sku} ${p.price ? '· ' + p.price : ''}</div>
                    </div>
                    <div class="flex items-center gap-2 ml-2 flex-shrink-0">
                        <span class="text-xs">${barcodeIcon} ${p.barcode || '@lang('inventory-plus::app.admin.barcode.no-barcode')'}</span>
                        ${isSelected ? '<span class="text-blue-600 text-xs font-bold">✓</span>' : '<span class="text-gray-300 text-xs">+</span>'}
                    </div>
                </div>`;
            });

            dropdown.innerHTML = html;
            dropdown.classList.remove('hidden');
        }

        function closeSearchResults() {
            document.getElementById('search-results-dropdown').classList.add('hidden');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            const container = document.querySelector('.relative');
            if (container && !container.contains(e.target)) {
                closeSearchResults();
            }
        });

        function toggleProduct(product) {
            if (selectedProducts[product.id]) {
                removeProduct(product.id);
            } else {
                addProduct(product);
            }
            searchProducts(); // Re-render dropdown to update checkmarks
        }

        function addProduct(product) {
            if (selectedProducts[product.id]) return;
            const globalCopies = parseInt(document.getElementById('global-copies').value) || 1;
            selectedProducts[product.id] = { ...product, copies: globalCopies };
            renderSelectedProducts();
        }

        function removeProduct(productId) {
            delete selectedProducts[productId];
            renderSelectedProducts();
        }

        function clearAllSelected() {
            selectedProducts = {};
            renderSelectedProducts();
        }

        function renderSelectedProducts() {
            const tbody = document.getElementById('selected-products-tbody');
            const container = document.getElementById('selected-products-container');
            const count = Object.keys(selectedProducts).length;

            document.getElementById('selected-count').textContent = count;
            document.getElementById('print-btn').disabled = count === 0;

            if (count === 0) {
                container.classList.add('hidden');
                document.getElementById('no-barcode-warning').classList.add('hidden');
                return;
            }

            container.classList.remove('hidden');

            let html = '';
            let hasNoBarcodes = false;

            Object.values(selectedProducts).forEach(p => {
                const barcodeHtml = p.barcode
                    ? `<span class="rounded bg-green-100 px-2 py-0.5 text-xs font-mono text-green-700 dark:bg-green-900/30 dark:text-green-400">${p.barcode}</span>`
                    : `<span class="rounded bg-amber-100 px-2 py-0.5 text-xs text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">@lang('inventory-plus::app.admin.barcode.no-barcode')</span>`;

                if (!p.barcode) hasNoBarcodes = true;

                html += `
                <tr class="border-b dark:border-gray-700 last:border-0">
                    <td class="px-3 py-2 text-sm">${p.name}</td>
                    <td class="px-3 py-2 text-sm font-mono text-gray-500">${p.sku}</td>
                    <td class="px-3 py-2">${barcodeHtml}</td>
                    <td class="px-3 py-2 text-center">
                        <input type="number" value="${p.copies}" min="1" max="100"
                               class="w-16 rounded border px-1 py-1 text-center text-xs dark:border-gray-700 dark:bg-gray-800"
                               onchange="updateCopies(${p.id}, this.value)">
                    </td>
                    <td class="px-3 py-2 text-center">
                        <button type="button" onclick="removeProduct(${p.id})" class="text-red-500 hover:text-red-700 text-sm" title="Remove">✕</button>
                    </td>
                </tr>`;
            });

            tbody.innerHTML = html;

            document.getElementById('no-barcode-warning').classList.toggle('hidden', !hasNoBarcodes);
        }

        function updateCopies(productId, copies) {
            if (selectedProducts[productId]) {
                selectedProducts[productId].copies = Math.max(1, Math.min(100, parseInt(copies) || 1));
            }
        }

        function applyGlobalCopies() {
            const copies = parseInt(document.getElementById('global-copies').value) || 1;
            Object.values(selectedProducts).forEach(p => p.copies = copies);
            renderSelectedProducts();
        }

        async function loadCategoryProducts() {
            const categoryId = document.getElementById('label-category-filter').value;
            if (!categoryId) {
                alert('@lang('inventory-plus::app.admin.barcode.select-category-first')');
                return;
            }

            try {
                const response = await fetch('{{ route("admin.inventory-plus.barcode.search-products") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ query: '', category_id: categoryId }),
                });

                const data = await response.json();
                (data.products || []).forEach(p => addProduct(p));
            } catch (e) {
                console.error('Load category error:', e);
            }
        }

        function filterByCategory() {
            const query = document.getElementById('label-search-input').value.trim();
            if (query) {
                searchProducts();
            }
        }

        function preparePrintForm() {
            const container = document.getElementById('hidden-product-ids');
            container.innerHTML = '';

            let hasAny = false;
            Object.values(selectedProducts).forEach(p => {
                if (!p.barcode) return; // Skip products without barcodes

                for (let i = 0; i < p.copies; i++) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'product_ids[]';
                    input.value = p.id;
                    container.appendChild(input);
                }

                hasAny = true;
            });

            // Set copies to 1 since we're already duplicating by copies count
            let copiesInput = container.querySelector('input[name="copies"]');
            if (!copiesInput) {
                copiesInput = document.createElement('input');
                copiesInput.type = 'hidden';
                copiesInput.name = 'copies';
                container.appendChild(copiesInput);
            }
            copiesInput.value = '1';

            if (!hasAny) {
                event.preventDefault();
                alert('@lang('inventory-plus::app.admin.barcode.no-products-barcode')');
            }
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
