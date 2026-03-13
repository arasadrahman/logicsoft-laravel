@extends('layouts.pdf')

@section('reportName', 'Invoice Wise Sales Details (' . (!empty($shopId) ? $shopId : 'All Shop') . ')')

@section('content')

@forelse($invoices as $key => $invoice)

<table width="100%" border="1" cellspacing="0" cellpadding="4">

<tr>
    <td colspan="8" class="text-left" style="background-color: #f2f2f2;">
        <strong>Invoice No:</strong> {{ $key }}
        &nbsp;&nbsp;
        <strong>Time:</strong> {{ $invoice['time'] }}
        &nbsp;&nbsp;
        <strong>CounterID:</strong> {{ $invoice['counter'] }}
        &nbsp;&nbsp;
        <strong>UserID:</strong> {{ $invoice['user'] }}
    </td>
</tr>

<tr>
    <th width="12%">Barcode</th>
    <th width="18%">Category</th>
    <th width="34%">Item</th>
    <th width="6%">MRP</th>
    <th width="4%">Qty</th>
    <th width="10%">GrsAmt</th>
    <th width="6%">DiscAmt</th>
    <th width="8%">NetAmt</th>
</tr>


@foreach($invoice['items'] as $row)
<tr>
    <td class="text-left">{{ $row->Barcode }}</td>
    <td class="text-left">{{ $row->Catagory }}</td>
    <td class="text-left">{{ $row->Item }}</td>
    <td class="text-right">{{ $row->MRP }}</td>
    <td class="text-right">{{ $row->Qty }}</td>
    <td class="text-right">{{ $row->GrsAmt }}</td>
    <td class="text-right">{{ $row->DiscAmt }}</td>
    <td class="text-right">{{ $row->SaleAmount }}</td>
</tr>
@endforeach

<tr>
    <td class="text-right" colspan="4">
        <strong>Total Item ({{ $invoice['unique_items'] }})</strong>
    </td>
    <td class="text-right"><strong>{{ $invoice['total_qty'] }}</strong></td>
    <td class="text-right"><strong>{{ $invoice['total_grs'] }}</strong></td>
    <td class="text-right"><strong>{{ $invoice['total_disc'] }}</strong></td>
    <td class="text-right"><strong>{{ $invoice['total_net'] }}</strong></td>
</tr>

</table>

@empty
<p style="text-align:center;">No data available</p>
@endforelse

@endsection
