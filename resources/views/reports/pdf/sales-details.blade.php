@extends('layouts.pdf')

@section('reportName', 'Sale Details Report (' . (!empty($shopId) ? $shopId : 'All Shop') . ')')

@section('content')

<style>
    body {
        font-size: 11px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 14px;
    }

    th, td {
        border: 1px solid #000;
        padding: 4px;
    }

    th {
        background: #f2f2f2;
    }

    thead {
        display: table-header-group;
    }

    tr {
        page-break-inside: avoid;
    }

    .text-right {
        text-align: right;
    }

    .category-row td {
        background: #e9e9e9;
        font-weight: bold;
        font-size: 12px;
    }

    .total-row td {
        font-weight: bold;
    }
</style>

@foreach($rows as $category => $items)
<table>
    <thead>
        <tr class="category-row">
            <td colspan="7">
                CATEGORY NAME: {{ $category }}
            </td>
        <tr style="background:#f2f2f2;">
            <th width="10%">Barcode</th>
            <th width="34%">Item Name</th>
            <th width="5%">MRP</th>
            <th width="4%">Qty</th>
            <th width="10%">GrsAmt</th>
            <th width="9%">DiscAmt</th>
            <th width="9%">NetAmt</th>
        </tr>
    </thead>

    <tbody>
        @foreach($items as $item)
        <tr>
            <td>{{ $item->Barcode }}</td>
            <td class="left">{{ $item->Item }}</td>
            <td class="text-right">{{ $item->MRP }}</td>
            <td class="text-right">{{ $item->Qty }}</td>
            <td class="text-right">{{ number_format($item->GrsAmt, 2) }}</td>
            <td class="text-right">{{ number_format($item->DiscAmt, 2) }}</td>
            <td class="text-right">{{ number_format($item->SaleAmount, 2) }}</td>
        </tr>
        @endforeach

        <tr class="total-row">
            <td colspan="3">
                Total Item ({{ $items->count() }})
            </td>
            <td class="text-right">{{ $items->sum('Qty') }}</td>
            <td class="text-right">{{ number_format($items->sum('GrsAmt'), 2) }}</td>
            <td class="text-right">{{ number_format($items->sum('DiscAmt'), 2) }}</td>
            <td class="text-right">{{ number_format($items->sum('SaleAmount'), 2) }}</td>
        </tr>
    </tbody>
</table>

@endforeach
<table>
    <tbody>
<tr class="total-row">
    <td width="49%" class="text-right underline text-red">
        Grand Total: {{ $summary->totalItems }}
    </td>
    <td width="4%" class="text-right underline text-red">{{ $summary->totalQuantity }}</td>
    <td width="10%" class="text-right underline text-red">{{ number_format($summary->totalGrsAmt, 2) }}</td>
    <td width="9%" class="text-right underline text-red">{{ number_format($summary->totalDiscAmt, 2) }}</td>
    <td width="9%" class="text-right underline text-red">{{ number_format($summary->totalNetAmt, 2) }}</td>
</tr>
</tbody>
</table>

@endsection
