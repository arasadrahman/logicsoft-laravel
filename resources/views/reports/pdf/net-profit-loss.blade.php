@extends('layouts.pdf')

@section('reportName', 'Date Wise Net Profit & Loss Report (' . (!empty($shopId) ? $shopId : 'All Shop') . ')')

@section('content')
<table class="table">
    <thead>
        <tr>
            <th>Date</th>
            <th class="text-right">Cost Price</th>
            <th class="text-right">Sale Price</th>
            <th class="text-right">Gross Profit</th>
            <th class="text-right">Expense</th>
            <th class="text-right">Net Profit</th>
        </tr>
    </thead>
    <tbody>
        @php
            $tSale = $tCost = $tGP = $tExp = $tNet = 0;
        @endphp

        @foreach($rows as $row)
            <tr>
                <td>{{ $row->date }}</td>
                <td class="text-right">{{ number_format($row->costamt, 2) }}</td>
                <td class="text-right">{{ number_format($row->netamt, 2) }}</td>
                
                <td class="text-right" style="color: #0000FF;">
                    {{ number_format($row->gpamt, 2) }}
                </td>

                <td class="text-right" style="color: #d9534f;">
                    {{ number_format($row->expamt, 2) }}
                </td>

                <td class="text-right" style="font-weight: bold; color: {{ $row->netamount >= 0 ? '#28a745' : '#FF0000' }};">
                    {{ number_format($row->netamount, 2) }}
                </td>
            </tr>

            @php
                $tSale += $row->netamt;
                $tCost += $row->costamt;
                $tGP   += $row->gpamt;
                $tExp  += $row->expamt;
                $tNet  += $row->netamount;
            @endphp
        @endforeach

        <tr style="font-weight: bold; background-color: #f8f9fa;">
            <td>Total</td>
            <td class="text-right">{{ number_format($tCost, 2) }}</td>
            <td class="text-right">{{ number_format($tSale, 2) }}</td>
            
            <td class="text-right" style="color: #0000FF;">
                {{ number_format($tGP, 2) }}
            </td>

            <td class="text-right" style="color: #d9534f;">
                {{ number_format($tExp, 2) }}
            </td>

            <td class="text-right" style="color: {{ $tNet >= 0 ? '#28a745' : '#FF0000' }};">
                {{ number_format($tNet, 2) }}
            </td>
        </tr>
    </tbody>
</table>
@endsection