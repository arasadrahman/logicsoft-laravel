@extends('layouts.excel')

@section('reportName', 'Supplier Payment Report (' . (!empty($shopId) ? $shopId : 'All Shop') . ')')

@section('content')
    <thead>
        <tr align="right">
            <th align="left">Date</th>
            @foreach($expHeads as $head)
                <th>{{ $head }}</th>
            @endforeach
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        @forelse($table as $date => $rows)
            @php $rowTotal = 0; @endphp
            <tr align="right">
                <td align="left">{{ $date }}</td>
                @foreach($expHeads as $head)
                    @php
                        $amount = $rows[$head] ?? 0;
                        $rowTotal += $amount;
                    @endphp
                    <td>{{ number_format($amount, 2) }}</td>
                @endforeach
                <td class="text-primary font-weight-bold">
                    {{ number_format($rowTotal, 2) }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="{{ count($expHeads) + 2 }}" class="text-center">
                    No data available
                </td>
            </tr>
        @endforelse

        @if(count($table))
            <tr class="text-danger font-weight-bold" align="right">
                <td align="left">Grand Total</td>
                @foreach($expHeads as $head)
                    <td>
                        {{ number_format(collect($table)->sum(fn($row) => $row[$head] ?? 0), 2) }}
                    </td>
                @endforeach
                <td>{{ number_format($totalAmount, 2) }}</td>
            </tr>
        @endif
    </tbody>
@endsection
