@extends('layouts.excel-ps')

@section('reportName', 'Present Stock Report (' . ($shopId ?: 'All Shops') . ')')

@section('content')

@php
    $grouped = $rows->groupBy('GroupName');
    $totalGroups = $grouped->count();
    $currentGroupCount = 0;
@endphp

@forelse($grouped as $group => $items)
    @php $currentGroupCount++; @endphp
    
        <thead>
            <tr style="background-color: #e9ecef;">
                <th colspan="4" style="font-size: 12px;">
                    Category: {{ $group }}
                </th>
            </tr>
            <tr style="background-color: #f1f1f1;">
                <th width="40%" class="text-left">Product Name</th>
                <th width="20%" class="text-right">Quantity</th>
                <th width="20%" class="text-right">Total Cost</th>
                <th width="20%" class="text-right">Total Sale</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td class="text-left">{{ $item->PrdName }}</td>
                    <td class="text-right">{{ number_format($item->TQty) }}</td>
                    <td class="text-right">{{ number_format($item->TCP, 2) }}</td>
                    <td class="text-right">{{ number_format($item->TSP, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        
        {{-- Category Sub-Total --}}
        <tfoot>
            <tr style="font-weight: bold; background-color: #fafafa;">
                <td class="text-left">Category Total:</td>
                <td class="text-right">{{ number_format($items->sum('TQty')) }}</td>
                <td class="text-right">{{ number_format($items->sum('TCP'), 2) }}</td>
                <td class="text-right">{{ number_format($items->sum('TSP'), 2) }}</td>
            </tr>

            {{-- Grand Total Section: Only shows after the very last category table --}}
            @if($currentGroupCount === $totalGroups)
                <tr style="background-color: #28a745; color: white; font-size: 14px;">
                    <td class="text-left"><strong>GRAND TOTAL (ALL STOCK)</strong></td>
                    <td class="text-right"><strong>{{ number_format($TQty) }}</strong></td>
                    <td class="text-right"><strong>{{ number_format($TCP, 2) }}</strong></td>
                    <td class="text-right"><strong>{{ number_format($TSP, 2) }}</strong></td>
                </tr>
            @endif
        </tfoot>

    {{-- Add spacing between tables except for the last one --}}
    @if($currentGroupCount < $totalGroups)
        <div style="margin-bottom: 20px;"></div>
    @endif

@empty
    <div style="text-align: center; padding: 20px; border: 1px solid #ccc;">
        No stock data available for the selected criteria.
    </div>
@endforelse

@endsection