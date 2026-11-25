@props(['concepts' => []])

@if(count($concepts) > 0)
<div class="overflow-x-auto">
    <table class="table table-sm">
        <thead>
            <tr class="text-sm uppercase">
                <th>Nombre / Descripción</th>
                <th class="text-center">Cantidad</th>
                <th class="text-right">Precio USD</th>
                <th class="text-center">Descuento %</th>
                <th class="text-right">Total USD</th>
            </tr>
        </thead>
        <tbody>
        @foreach($concepts as $concept)
            <tr class="hover:bg-base-300/50 transition-colors">
                <td>
                    <div class="font-semibold text-sm">{{ $concept['name'] }}</div>
                    @if($concept['description'])
                        <div class="text-xs text-gray-400">{{ $concept['description'] }}</div>
                    @endif
                </td>
                <td class="text-center">{{ number_format($concept['quantity'], 0) }}</td>
                <td class="text-right">$ {{ number_format($concept['unit_price'], 2) }}</td>
                <td class="text-center">{{ number_format($concept['discount'], 2) }} %</td>
                <td class="text-right font-semibold">
                    $ {{ number_format($concept['unit_price'] * $concept['quantity'] * (1 - $concept['discount'] / 100), 2) }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@else
    <div class="text-center py-8">
        <x-mary-icon name="o-document-text" class="w-12 h-12 mx-auto text-gray-400 mb-3" />
        <p class="text-sm text-gray-500">No hay conceptos adicionales en esta cotización</p>
    </div>
@endif
