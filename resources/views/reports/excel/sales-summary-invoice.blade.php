@extends('layouts.excel')

@section('reportName', 'Invoice Wise Sales Summary (' . (!empty($shopId) ? $shopId : 'All Shop') . ')')

@section('content')
<tbody>
@forelse($groupedData as $shop => $rows)
    <tr>
        <td colspan="8" style="font-weight:bold; background:#f2f2f2;">
            Shop ID : {{ $shop }}
        </td>
    </tr>
    <tr>
        <th>Invoice</th>
        <th>Qty</th>
        <th>Amount</th>
        <th>PayType</th>
        <th>UserID</th>
        <th>SaleDT</th>
        <th>SaleTM</th>
        <th>CounterID</th>
    </tr>
    @foreach($rows as $row)
        <tr>
            <td>{{ $row->Invoice }}</td>
            <td>{{ $row->Qty }}</td>
            <td>{{ $row->SaleAmount }}</td>
            <td>{{ $row->PayType }}</td>
            <td>{{ $row->UserID }}</td>
            <td>{{ $row->SaleDT }}</td>
            <td>{{ $row->SaleTM }}</td>
            <td>{{ $row->CounterID }}</td>
        </tr>
    @endforeach

    <tr>
        <td colspan="1"><strong>Shop Total</strong></td>
        <td><strong>{{ $rows->sum('Qty') }}</strong></td>
        <td><strong>{{ $rows->sum('SaleAmount') }}</strong></td>
        <td colspan="5"></td>
    </tr>
    
    <tr><td height="20px" colspan="8"></td></tr>
@empty
    <tr>
        <td colspan="8" class="text-center">No data available</td>
    </tr>
@endforelse
</tbody>
@endsection
