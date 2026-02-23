<x-admin::layouts>
    <x-slot:title>
        @lang('inventory-plus::app.admin.movements.product-history', ['name' => $product->name])
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('inventory-plus::app.admin.movements.product-history', ['name' => $product->name])
        </p>

        <a href="{{ route('admin.inventory-plus.movements.index') }}" class="transparent-button">
            &larr; Back to All Movements
        </a>
    </div>

    <div class="mt-3.5">
        <div class="box-shadow rounded bg-white dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800">
                            <th class="px-4 py-3 text-left font-semibold">@lang('inventory-plus::app.admin.movements.date')</th>
                            <th class="px-4 py-3 text-left font-semibold">@lang('inventory-plus::app.admin.movements.type')</th>
                            <th class="px-4 py-3 text-left font-semibold">@lang('inventory-plus::app.admin.movements.source')</th>
                            <th class="px-4 py-3 text-right font-semibold">@lang('inventory-plus::app.admin.movements.qty-before')</th>
                            <th class="px-4 py-3 text-right font-semibold">@lang('inventory-plus::app.admin.movements.qty-change')</th>
                            <th class="px-4 py-3 text-right font-semibold">@lang('inventory-plus::app.admin.movements.qty-after')</th>
                            <th class="px-4 py-3 text-left font-semibold">@lang('inventory-plus::app.admin.movements.reason')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movements as $movement)
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <td class="px-4 py-3">{{ $movement->created_at->format('Y-m-d H:i') }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded px-2 py-1 text-xs font-medium
                                        @if(in_array($movement->type->value, ['receipt', 'transfer_in', 'return', 'import'])) bg-green-100 text-green-800
                                        @elseif(in_array($movement->type->value, ['sale', 'transfer_out'])) bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ $movement->type->label() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">{{ $movement->inventorySource?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-right">{{ $movement->qty_before }}</td>
                                <td class="px-4 py-3 text-right font-mono font-bold
                                    @if($movement->qty_change > 0) text-green-600 @else text-red-600 @endif">
                                    {{ $movement->qty_change > 0 ? '+' : '' }}{{ $movement->qty_change }}
                                </td>
                                <td class="px-4 py-3 text-right font-bold">{{ $movement->qty_after }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $movement->reason ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">No movements found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin::layouts>
