@extends('layouts.pdf')

@section('reportName', 'Invoice Wise Gross Profit Loss Report (' . (!empty($shopId) ? $shopId : 'All Shop') . ')')

@section('content')
<table class="table table-striped" border="1">
    <thead>
        <tr align="right">
            <th align="left">Invoice</th>
            <th>Qty</th>
            <th>CostAmt</th>
            <th>SaleAmount</th>
            <th>GPAmt</th>
            <th>ShopID</th>
            <th>UserID</th>
            <th>SaleDT</th>
            <th>SaleTM</th>
            <th>Counter</th>
        </tr>
    </thead>
    <tbody>
        @forelse($rows as $row)
            <tr align="right">
                <td align="left">{{ $row->Invoice }}</td>
                <td>{{ number_format($row->Qty) }}</td>
                <td>{{ number_format($row->CostAmount, 2) }}</td>
                <td>{{ number_format($row->SaleAmount, 2) }}</td>
                <td class="{{ $row->GPAmt < 0 ? 'text-danger font-weight-bold' : 'text-success font-weight-bold' }}">
                    {{ number_format($row->GPAmt, 2) }}
                </td>
                <td>{{ $row->ShopID }}</td>
                <td>{{ $row->UserID }}</td>
                <td>{{ $row->SaleDT }}</td>
                <td>{{ $row->SaleTM }}</td>
                <td>{{ $row->CounterID }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="10" class="text-center">No data available</td>
            </tr>
        @endforelse

        @if($rows->count())
            <tr class="text-danger font-weight-bold" align="right">
                <td align="left">Grand Total</td>
                <td>{{ number_format($totalQty) }}</td>
                <td>{{ number_format($totalCostAmount, 2) }}</td>
                <td>{{ number_format($totalSaleAmount, 2) }}</td>
                <td>{{ number_format($totalGrossAmount, 2) }}</td>
                <td colspan="5"></td>
            </tr>
        @endif
    </tbody>
</table>
@endsection
