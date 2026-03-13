@extends('layouts.excel')

@section('reportName', 'Sale Details Report (' . (!empty($shopId) ? $shopId : 'All Shop') . ')')

@section('content')
    <tbody>

        @foreach($rows as $category => $items)

            <tr>
                <td colspan="7" style="font-weight:bold; background-color:#E7E6E6;">
                    CATEGORY NAME: {{ $category }}
                </td>
            </tr>

            <tr>
                <td style="font-weight:bold; background-color:#D9E1F2;">Barcode</td>
                <td style="font-weight:bold; background-color:#D9E1F2;">Item Name</td>
                <td style="font-weight:bold; background-color:#D9E1F2;">MRP</td>
                <td style="font-weight:bold; background-color:#D9E1F2;">Qty</td>
                <td style="font-weight:bold; background-color:#D9E1F2;">GrsAmt</td>
                <td style="font-weight:bold; background-color:#D9E1F2;">DiscAmt</td>
                <td style="font-weight:bold; background-color:#D9E1F2;">NetAmt</td>
            </tr>

            @foreach($items as $item)
                <tr>
                    <td style="vnd.ms-excel.numberformat:@">{{ $item->Barcode }}</td>
                    <td>{{ $item->Item }}</td>
                    <td>{{ $item->MRP }}</td>
                    <td>{{ $item->Qty }}</td>
                    <td>{{ $item->GrsAmt }}</td>
                    <td>{{ $item->DiscAmt }}</td>
                    <td>{{ $item->SaleAmount }}</td>
                </tr>
            @endforeach

        @endforeach

    </tbody>
    <tbody>
        <tr><td colspan="7"></td></tr>
        <tr><td colspan="7"></td></tr>
<tr class="total-row">
    <td colspan="3" class="text-right underline text-red">
        Grand Total: {{ $summary->totalItems }}
    </td>
    <td class="text-right underline text-red">{{ $summary->totalQuantity }}</td>
    <td class="text-right underline text-red">{{ number_format($summary->totalGrsAmt, 2) }}</td>
    <td class="text-right underline text-red">{{ number_format($summary->totalDiscAmt, 2) }}</td>
    <td class="text-right underline text-red">{{ number_format($summary->totalNetAmt, 2) }}</td>
</tr>
</tbody>
@endsection
