@extends('layouts.pdf')

@section('reportName', 'Daily Collection Report (' . (!empty($shopId) ? $shopId : 'All Shop') . ')')

@section('content')
<table>
    <thead>
        <tr align="right">
            <th>Date</th>
            @foreach($payTypes as $type)
                <th>{{ $type }}</th>
            @endforeach
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $date => $payments)
            @php $rowTotal = 0; @endphp
            <tr align="right">
                <td>{{ $date }}</td>
                @foreach($payTypes as $type)
                    @php
                        $amount = $payments[$type] ?? 0;
                        $rowTotal += $amount;
                    @endphp
                    <td class="{{ $type == 'Cash' ? 'text-success font-weight-bold' : '' }}">
                        {{ number_format($amount) }}
                    </td>
                @endforeach
                <td class="text-primary font-weight-bold">
                    {{ number_format($rowTotal) }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="100%" class="text-center">No data available</td>
            </tr>
        @endforelse

        @if(count($data))
            <tr class="text-danger font-weight-bold" align="right">
                <td>Total</td>
                @foreach($payTypes as $type)
                    <td>
                        {{ number_format(collect($data)->sum(fn($d) => $d[$type] ?? 0)) }}
                    </td>
                @endforeach
                <td>{{ number_format($grandTotal) }}</td>
            </tr>
        @endif
    </tbody>
</table>
@endsection
