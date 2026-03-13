@extends('layouts.app')

@section('content')

<div x-data="{loading:false}">

{{-- FILTER --}}
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Filter Options</h3>
            </div>
            <div class="card-body">
                {{-- Ensure the route name matches your web.php definition --}}
                <form method="GET" action="{{ route('net.profit.loss') }}" @submit="loading=true">
                    <div class="row">
                        <div class="col-md-4">
                            <label>Shop ID</label>
                            <select name="shopId" class="form-control">
                                <option value="">All Shop</option>
                                @foreach($shops as $shopIdItem)
                                    <option value="{{ $shopIdItem }}"
                                        {{ $shopId == $shopIdItem ? 'selected' : '' }}>
                                        {{ $shopIdItem }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label>Start Date</label>
                            <input type="date" name="startDate" class="form-control" value="{{ $startDate }}">
                        </div>

                        <div class="col-md-4">
                            <label>End Date</label>
                            <input type="date" name="endDate" class="form-control" value="{{ $endDate }}">
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center">
                            <button class="btn btn-primary">
                                <span x-show="!loading">Submit</span>
                                <span x-show="loading">
                                    <i class="fas fa-spinner fa-spin"></i> Loading...
                                </span>
                            </button>

                            <div>
                            <a href="{{ route('net.profit.loss.excel', request()->query()) }}" 
                                class="btn btn-success ml-2">
                                    <i class="fas fa-file-excel"></i> Export Excel
                            </a>

                            <a target="_blank" 
                                href="{{ route('net.profit.loss.pdf', request()->all()) }}" 
                                class="btn btn-danger">
                                    <i class="fas fa-file-pdf"></i> Export PDF
                            </a>
                            </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- SUMMARY --}}
<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body text-center" style="font-size:18px;">
                <div class="row">
                    <div class="col-md-4">
                        <b>Total GP:</b>
                        <span class="text-primary">
                            <b>৳ {{ number_format($totals['gpamt'], 2) }}</b>
                        </span>
                    </div>
                    <div class="col-md-4">
                        <b>Total Expense:</b>
                        <span class="text-danger">
                            <b>৳ {{ number_format($totals['expamt'], 2) }}</b>
                        </span>
                    </div>
                    <div class="col-md-4">
                        <b>Net Profit/Loss:</b>
                        <span class="{{ $totals['netamount'] >= 0 ? 'text-success' : 'text-danger' }}">
                            <b>৳ {{ number_format($totals['netamount'], 2) }}</b>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- TABLE --}}
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th class="text-right">Cost Amt</th>
                            <th class="text-right">Sale Amt</th>
                            <th class="text-right">Gross Profit</th>
                            <th class="text-right text-danger">Expense</th>
                            <th class="text-right">Net Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $tSale = $tCost = $tGP = $tExp = $tNet = 0;
                        @endphp

                        @forelse($rows as $row)
                            <tr>
                                <td>{{ $row->date }}</td>
                                <td class="text-right">{{ number_format($row->costamt, 2) }}</td>
                                <td class="text-right">{{ number_format($row->netamt, 2) }}</td>
                                <td class="text-right text-primary">{{ number_format($row->gpamt, 2) }}</td>
                                <td class="text-right text-danger">{{ number_format($row->expamt, 2) }}</td>
                                <td class="text-right font-weight-bold {{ $row->netamount >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($row->netamount, 2) }}
                                </td>
                            </tr>

                            @php
                                $tSale += $row->netamt;
                                $tCost += $row->costamt;
                                $tGP   += $row->gpamt;
                                $tExp  += $row->expamt;
                                $tNet  += $row->netamount;
                            @endphp
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No data available for the selected criteria.</td>
                            </tr>
                        @endforelse

                        @if(count($rows))
                        <tr class="bg-light font-weight-bold">
                            <td class="text-center">GRAND TOTAL</td>
                            <td class="text-right">{{ number_format($tCost, 2) }}</td>
                            <td class="text-right">{{ number_format($tSale, 2) }}</td>
                            <td class="text-right text-primary">{{ number_format($tGP, 2) }}</td>
                            <td class="text-right text-danger">{{ number_format($tExp, 2) }}</td>
                            <td class="text-right {{ $tNet >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($tNet, 2) }}
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</div>

@endsection