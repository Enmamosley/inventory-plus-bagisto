<x-admin::layouts>
    <x-slot:title>
        @lang('inventory-plus::app.admin.transfers.details') - {{ $transfer->reference_number }}
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            {{ $transfer->reference_number }}
        </p>

        <div class="flex items-center gap-2">
            @if($transfer->status->value === 'pending')
                <form method="POST" action="{{ route('admin.inventory-plus.transfers.ship', $transfer->id) }}">
                    @csrf
                    <button type="submit" class="primary-button" onclick="return confirm('Ship this transfer?')">
                        @lang('inventory-plus::app.admin.transfers.ship-btn')
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.inventory-plus.transfers.cancel', $transfer->id) }}">
                    @csrf
                    <button type="submit" class="transparent-button text-red-500" onclick="return confirm('Cancel this transfer?')">
                        @lang('inventory-plus::app.admin.transfers.cancel-btn')
                    </button>
                </form>
            @elseif($transfer->status->value === 'in_transit')
                <form method="POST" action="{{ route('admin.inventory-plus.transfers.receive', $transfer->id) }}">
                    @csrf
                    <button type="submit" class="primary-button" onclick="return confirm('Receive this transfer?')">
                        @lang('inventory-plus::app.admin.transfers.receive-btn')
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.inventory-plus.transfers.cancel', $transfer->id) }}">
                    @csrf
                    <button type="submit" class="transparent-button text-red-500" onclick="return confirm('Cancel? Stock will be returned.')">
                        @lang('inventory-plus::app.admin.transfers.cancel-btn')
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="mt-3.5 grid grid-cols-2 gap-4">
        {{-- Info --}}
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <h3 class="mb-3 text-lg font-semibold">@lang('inventory-plus::app.admin.transfers.details')</h3>

            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">@lang('inventory-plus::app.admin.transfers.status')</span>
                    <span class="rounded px-2 py-1 text-xs font-semibold
                        @if($transfer->status->value === 'completed') bg-green-100 text-green-800
                        @elseif($transfer->status->value === 'in_transit') bg-blue-100 text-blue-800
                        @elseif($transfer->status->value === 'cancelled') bg-red-100 text-red-800
                        @else bg-yellow-100 text-yellow-800 @endif
                    ">{{ $transfer->status->label() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">@lang('inventory-plus::app.admin.transfers.from')</span>
                    <span>{{ $transfer->source->name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">@lang('inventory-plus::app.admin.transfers.to')</span>
                    <span>{{ $transfer->destination->name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">@lang('inventory-plus::app.admin.transfers.date')</span>
                    <span>{{ $transfer->created_at->format('Y-m-d H:i') }}</span>
                </div>
                @if($transfer->shipped_at)
                    <div class="flex justify-between">
                        <span class="text-gray-600">Shipped At</span>
                        <span>{{ $transfer->shipped_at->format('Y-m-d H:i') }}</span>
                    </div>
                @endif
                @if($transfer->received_at)
                    <div class="flex justify-between">
                        <span class="text-gray-600">Received At</span>
                        <span>{{ $transfer->received_at->format('Y-m-d H:i') }}</span>
                    </div>
                @endif
                @if($transfer->notes)
                    <div class="mt-2 border-t pt-2">
                        <span class="text-gray-600">@lang('inventory-plus::app.admin.transfers.notes'):</span>
                        <p class="mt-1">{{ $transfer->notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Items --}}
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <h3 class="mb-3 text-lg font-semibold">@lang('inventory-plus::app.admin.transfers.items')</h3>

            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b">
                        <th class="px-3 py-2 text-left">Product</th>
                        <th class="px-3 py-2 text-right">@lang('inventory-plus::app.admin.transfers.qty-requested')</th>
                        <th class="px-3 py-2 text-right">@lang('inventory-plus::app.admin.transfers.qty-shipped')</th>
                        <th class="px-3 py-2 text-right">@lang('inventory-plus::app.admin.transfers.qty-received')</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transfer->items as $item)
                        <tr class="border-b">
                            <td class="px-3 py-2">
                                {{ $item->product?->name ?? 'Product #' . $item->product_id }}
                                <br>
                                <span class="text-xs text-gray-500">{{ $item->product?->sku }}</span>
                            </td>
                            <td class="px-3 py-2 text-right">{{ $item->qty_requested }}</td>
                            <td class="px-3 py-2 text-right">{{ $item->qty_shipped }}</td>
                            <td class="px-3 py-2 text-right">{{ $item->qty_received }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-admin::layouts>
