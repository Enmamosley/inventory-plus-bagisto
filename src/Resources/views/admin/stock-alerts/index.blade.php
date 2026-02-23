<x-admin::layouts>
    <x-slot:title>
        @lang('inventory-plus::app.admin.stock-alerts.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('inventory-plus::app.admin.stock-alerts.title')
        </p>

        <div class="flex items-center gap-x-2.5">
            <form method="POST" action="{{ route('admin.inventory-plus.alerts.check') }}">
                @csrf
                <button type="submit" class="secondary-button">
                    @lang('inventory-plus::app.admin.stock-alerts.check-now')
                </button>
            </form>

            <a href="{{ route('admin.inventory-plus.alerts.create') }}" class="primary-button">
                @lang('inventory-plus::app.admin.stock-alerts.new-rule')
            </a>
        </div>
    </div>

    {{-- Alert Rules --}}
    <div class="mt-3.5 box-shadow rounded bg-white dark:bg-gray-900">
        <div class="p-4">
            <h3 class="text-lg font-semibold">@lang('inventory-plus::app.admin.stock-alerts.rules')</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full table-auto">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-medium">@lang('inventory-plus::app.admin.stock-alerts.name')</th>
                        <th class="px-4 py-3 text-left text-sm font-medium">@lang('inventory-plus::app.admin.stock-alerts.product')</th>
                        <th class="px-4 py-3 text-left text-sm font-medium">@lang('inventory-plus::app.admin.stock-alerts.source')</th>
                        <th class="px-4 py-3 text-left text-sm font-medium">@lang('inventory-plus::app.admin.stock-alerts.low-threshold')</th>
                        <th class="px-4 py-3 text-left text-sm font-medium">@lang('inventory-plus::app.admin.stock-alerts.critical-threshold')</th>
                        <th class="px-4 py-3 text-left text-sm font-medium">@lang('inventory-plus::app.admin.stock-alerts.email-enabled')</th>
                        <th class="px-4 py-3 text-center text-sm font-medium">@lang('inventory-plus::app.admin.stock-alerts.actions')</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($rules as $rule)
                        <tr>
                            <td class="px-4 py-3 text-sm">{{ $rule->name }}</td>
                            <td class="px-4 py-3 text-sm">
                                @if ($rule->product)
                                    {{ $rule->product->name }} <span class="text-gray-400">({{ $rule->product->sku }})</span>
                                @else
                                    <span class="text-gray-400">@lang('inventory-plus::app.admin.stock-alerts.all-products')</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if ($rule->inventorySource)
                                    {{ $rule->inventorySource->name }}
                                @else
                                    <span class="text-gray-400">@lang('inventory-plus::app.admin.stock-alerts.all-sources')</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm font-mono">{{ $rule->low_stock_threshold }}</td>
                            <td class="px-4 py-3 text-sm font-mono">{{ $rule->critical_stock_threshold }}</td>
                            <td class="px-4 py-3 text-sm">
                                @if ($rule->notify_email)
                                    <span class="rounded bg-green-100 px-2 py-1 text-xs text-green-700">✓</span>
                                @else
                                    <span class="rounded bg-gray-100 px-2 py-1 text-xs text-gray-500">✗</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <form method="POST" action="{{ route('admin.inventory-plus.alerts.destroy', $rule->id) }}"
                                      onsubmit="return confirm('@lang('inventory-plus::app.admin.stock-alerts.confirm-delete')')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm text-red-600 hover:text-red-800">
                                        @lang('inventory-plus::app.admin.stock-alerts.delete')
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-gray-400">
                                @lang('inventory-plus::app.admin.stock-alerts.no-rules')
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent Alert Logs --}}
    <div class="mt-4 box-shadow rounded bg-white dark:bg-gray-900">
        <div class="p-4">
            <h3 class="text-lg font-semibold">@lang('inventory-plus::app.admin.stock-alerts.recent-alerts')</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full table-auto">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-medium">@lang('inventory-plus::app.admin.stock-alerts.date')</th>
                        <th class="px-4 py-3 text-left text-sm font-medium">@lang('inventory-plus::app.admin.stock-alerts.type')</th>
                        <th class="px-4 py-3 text-left text-sm font-medium">@lang('inventory-plus::app.admin.stock-alerts.product')</th>
                        <th class="px-4 py-3 text-left text-sm font-medium">@lang('inventory-plus::app.admin.stock-alerts.current-qty')</th>
                        <th class="px-4 py-3 text-left text-sm font-medium">@lang('inventory-plus::app.admin.stock-alerts.threshold')</th>
                        <th class="px-4 py-3 text-left text-sm font-medium">@lang('inventory-plus::app.admin.stock-alerts.notified')</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($recentAlerts as $alert)
                        <tr>
                            <td class="px-4 py-3 text-sm">{{ $alert->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3 text-sm">
                                @php
                                    $alertColors = [
                                        'low_stock' => 'bg-yellow-100 text-yellow-700',
                                        'critical_stock' => 'bg-orange-100 text-orange-700',
                                        'out_of_stock' => 'bg-red-100 text-red-700',
                                        'back_in_stock' => 'bg-green-100 text-green-700',
                                    ];
                                    $color = $alertColors[$alert->alert_type->value] ?? 'bg-gray-100 text-gray-700';
                                @endphp
                                <span class="rounded px-2 py-1 text-xs {{ $color }}">
                                    {{ str_replace('_', ' ', ucfirst($alert->alert_type->value)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                {{ $alert->product?->name ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-3 text-sm font-mono">{{ $alert->current_qty }}</td>
                            <td class="px-4 py-3 text-sm font-mono">{{ $alert->threshold }}</td>
                            <td class="px-4 py-3 text-sm">
                                @if ($alert->notified)
                                    <span class="text-green-600">✓</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-gray-400">
                                @lang('inventory-plus::app.admin.stock-alerts.no-alerts')
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin::layouts>
