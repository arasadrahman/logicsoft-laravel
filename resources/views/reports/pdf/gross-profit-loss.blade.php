@extends('layouts.pdf')

@section('reportName', 'Date Wise Gross Profit Report (' . (!empty($shopId) ? $shopId : 'All Shop') . ')')

@section('content')
<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Invoice</th>
            <th>Qty</th>
            <th>Sale Price</th>
            <th>Cost Price</th>
            <th>GP Amount</th>
            <th>GP %</th>
        </tr>
    </thead>
    <tbody>
        @php
            $tInv = $tQty = $tCost = $tSale = $tGP = 0;
        @endphp

        @foreach($rows as $row)
            @php
                $gpPercent = $row->SaleAmount > 0
                    ? ($row->GPAmt / $row->SaleAmount) * 100
                    : 0;
            @endphp

            <tr>
                <td>{{ $row->SaleDT }}</td>
                <td>{{ $row->Inv }}</td>
                <td>{{ $row->Qty }}</td>
                <td class="text-right">{{ number_format($row->SaleAmount, 2) }}</td>
                <td class="text-right">{{ number_format($row->CostAmt, 2) }}</td>
                <td class="text-right">{{ number_format($row->GPAmt, 2) }}</td>
                <td class="text-right">{{ number_format($gpPercent, 2) }}%</td>
            </tr>

            @php
                $tInv  += $row->Inv;
                $tQty  += $row->Qty;
                $tCost += $row->CostAmt;
                $tSale += $row->SaleAmount;
                $tGP   += $row->GPAmt;
            @endphp
        @endforeach

        @php
        $totalGPPercent = $tSale > 0
            ? ($tGP / $tSale) * 100
            : 0;
        @endphp

        <tr class="text-bold">
            <td>Total</td>
            <td>{{ $tInv }}</td>
            <td>{{ $tQty }}</td>
            <td class="text-right">{{ number_format($tSale, 2) }}</td>
            <td class="text-right">{{ number_format($tCost, 2) }}</td>
            <td class="text-right">{{ number_format($tGP, 2) }}</td>
            <td class="text-right">{{ number_format($totalGPPercent, 2) }}%</td>
        </tr>
    </tbody>
</table>
@endsection