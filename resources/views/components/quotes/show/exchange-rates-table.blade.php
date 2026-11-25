@props(['exchangeRates'])

<div class="card bg-base-100 shadow-sm">
    <div class="card-body">
        <h2 class="card-title">Tasas de Cambio Aplicadas</h2>
        <p class="text-sm text-gray-500 mb-4">
            Estas son las tasas que se utilizaron al momento de crear esta cotización.
            Se han congelado para mantener la trazabilidad histórica.
        </p>
        <div class="overflow-x-auto">
            <table class="table table-sm">
                <thead>
                <tr>
                    <th>De</th>
                    <th>A</th>
                    <th>Tasa</th>
                    <th>Vigente desde</th>
                    <th>Vigente hasta</th>
                </tr>
                </thead>
                <tbody>
                @foreach($exchangeRates as $rate)
                    <tr>
                        <td class="font-mono">{{ $rate['from_currency'] }}</td>
                        <td class="font-mono">{{ $rate['to_currency'] }}</td>
                        <td class="font-mono font-bold">{{ number_format($rate['rate'], 4) }}</td>
                        <td class="text-sm">
                            {{ \Carbon\Carbon::parse($rate['created_at'])->format('d/m/Y') }} 00:00 hrs
                        </td>
                        <td class="text-sm">
                            {{ \Carbon\Carbon::parse($rate['created_at'])->format('d/m/Y') }} 24:00 hrs
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>