@extends('layouts.pdf')

@section('reportName', 'Daily Expense Report (' . (!empty($shopId) ? $shopId : 'All Shop') . ')')

@section('content')

<style>
table{
    width:100%;
    border-collapse:collapse;
    table-layout:fixed;
    font-size:11px;
}
th,td{
    padding:4px;
    word-wrap:break-word;
}
thead{
    display:table-header-group;
}
tr{
    page-break-inside:avoid;
}
.date-col{
    width:110px;
    white-space:nowrap;
}
</style>

@foreach($expHeads->chunk($chunkSize) as $chunkHeads)

<table border="1">
    <thead>
        <tr>
            <th class="date-col">Date</th>

            @foreach($chunkHeads as $head)
                <th>{{ $head }}</th>
            @endforeach

            @if($loop->last)
                <th>Total</th>
            @endif
        </tr>
    </thead>

    <tbody>
        @forelse($table as $date => $rows)

        <tr>
            <td class="date-col">{{ $date }}</td>

            @foreach($chunkHeads as $head)
                <td align="right" class="text-right">
                    {{ number_format($rows[$head],2) }}
                </td>
            @endforeach

            @if($loop->parent->last)
                <td align="right" class="text-right">
                    <strong>{{ number_format($rows['_total'],2) }}</strong>
                </td>
            @endif
        </tr>

        @empty
        <tr>
            <td colspan="{{ $chunkHeads->count()+($loop->last?2:1) }}" align="center">
                No data available
            </td>
        </tr>
        @endforelse
        
{{-- ⭐ COLUMN TOTAL ROW --}}
@if($loop->last)
<tr style="font-weight:bold; background:#f2f2f2;">
    <td>Total</td>

    @foreach($chunkHeads as $head)
        <td align="right" class="text-right">
            {{ number_format($columnTotals[$head],2) }}
        </td>
    @endforeach

    <td align="right" class="text-right">
        {{ number_format($totalAmount,2) }}
    </td>
</tr>
@endif
    </tbody>
</table>

@if(!$loop->last)
<div style="page-break-after:always;"></div>
@endif

@endforeach

@endsection
