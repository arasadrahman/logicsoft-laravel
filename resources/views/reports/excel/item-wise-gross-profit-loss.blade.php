@extends('layouts.excel')

@section('reportName', 'Item Wise Gross Profit Loss Report (' . (!empty($shopId) ? $shopId : 'All Shop') . ')')

@section('content')
    @php
        $hasData = false;
        $gQty = $gCost = $gSale = $gGP = 0;
    @endphp

    @forelse($grouped as $category => $items)
        @if($items->isNotEmpty())
            @php $hasData = true; @endphp
            <thead>
                <tr>
                    <th colspan="7" align="left">
                        Catagory No:
                        <span class="text-blue"><b>{{ $category }}</b></span>
                    </th>
                </tr>
                <tr align="right">
                    <th align="left">Barcode</th>
                    <th align="left">Item Name</th>
                    <th>MRP</th>
                    <th>Qty</th>
                    <th>CostAmt</th>
                    <th>SaleAmt</th>
                    <th>GPAmt</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $cQty = $cCost = $cSale = $cGP = 0;
                @endphp

                @foreach($items as $item)
                    @php
                        $cQty  += $item->Qty;
                        $cCost += $item->CostAmt;
                        $cSale += $item->SaleAmount;
                        $cGP   += $item->GPAmt;

                        $gQty  += $item->Qty;
                        $gCost += $item->CostAmt;
                        $gSale += $item->SaleAmount;
                        $gGP   += $item->GPAmt;
                    @endphp
                    <tr align="right">
                        <td align="left">{{ $item->Barcode }}</td>
                        <td align="left">{{ $item->Item }}</td>
                        <td>{{ number_format($item->MRP, 2) }}</td>
                        <td>{{ number_format($item->Qty) }}</td>
                        <td>{{ number_format($item->CostAmt, 2) }}</td>
                        <td>{{ number_format($item->SaleAmount, 2) }}</td>
                        <td class="{{ $item->GPAmt < 0 ? 'text-danger font-weight-bold' : 'text-success font-weight-bold' }}">
                            {{ number_format($item->GPAmt, 2) }}
                        </td>
                    </tr>
                @endforeach

                <tr class="text-primary font-weight-bold" align="right">
                    <td colspan="2" align="left">
                        Total Item ({{ $items->unique('Item')->count() }})
                    </td>
                    <td></td>
                    <td>{{ number_format($cQty) }}</td>
                    <td>{{ number_format($cCost, 2) }}</td>
                    <td>{{ number_format($cSale, 2) }}</td>
                    <td>{{ number_format($cGP, 2) }}</td>
                </tr>
            </tbody>
        @endif
    @empty
        <tbody>
            <tr>
                <td colspan="7" class="text-center">No data available</td>
            </tr>
        </tbody>
    @endforelse

    @if($hasData)
        <tfoot>
            <tr class="text-danger font-weight-bold" align="right">
                <td colspan="2" align="left">Grand Total</td>
                <td></td>
                <td>{{ number_format($gQty) }}</td>
                <td>{{ number_format($gCost, 2) }}</td>
                <td>{{ number_format($gSale, 2) }}</td>
                <td>{{ number_format($gGP, 2) }}</td>
            </tr>
        </tfoot>
    @endif
@endsection
