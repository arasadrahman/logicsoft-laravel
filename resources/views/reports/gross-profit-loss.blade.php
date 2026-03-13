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
                <form method="GET" action="{{ route('gross.profit.loss') }}" @submit="loading=true">
                    <div class="row">
                        <div class="col-md-4">
                            <label>Shop ID</label>
                            <select name="shopId" class="form-control">
                                <option value="">All Shop</option>
                                @foreach($shops as $shop)
                                    <option value="{{ $shop->ShopID }}"
                                        {{ $shopId == $shop->ShopID ? 'selected' : '' }}>
                                        {{ $shop->ShopID }}
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
                            <a href="{{ route('gross.profit.loss.excel', request()->query()) }}"
                                class="btn btn-success ml-2">
                                    <i class="fas fa-file-excel"></i> Export Excel
                            </a>

                            <a target="_blank"
                                href="{{ route('gross.profit.loss.pdf', request()->all()) }}"
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
                    <div class="col-md-6">
                        <b>Total GP Amount:</b>
                        <span class="text-green">
                            <b>৳ {{ number_format($totalGPAmt,2) }}</b>
                        </span>
                    </div>
                    <div class="col-md-6">
                        <b>Shop:</b>
                        <span class="text-green">
                            <b>{{ $shopId ?? 'All' }}</b>
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
                            <th>Invoice</th>
                            <th>Qty</th>
                            <th>Cost Price</th>
                            <th>Sale Price</th>
                            <th>GP Amt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $tInv = $tQty = $tCost = $tSale = $tGP = 0;
                        @endphp

                        @forelse($rows as $row)
                            <tr>
                                <td>{{ $row->SaleDT }}</td>
                                <td>{{ $row->Inv }}</td>
                                <td>{{ $row->Qty }}</td>
                                <td>{{ $row->CostAmt }}</td>
                                <td>{{ $row->SaleAmount }}</td>
                                <td>{{ $row->GPAmt }}</td>
                            </tr>

                            @php
                                $tInv  += $row->Inv;
                                $tQty  += $row->Qty;
                                $tCost += $row->CostAmt;
                                $tSale += $row->SaleAmount;
                                $tGP   += $row->GPAmt;
                            @endphp
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No data available</td>
                            </tr>
                        @endforelse

                        @if(count($rows))
                        <tr class="text-danger font-weight-bold">
                            <td>Total</td>
                            <td>{{ $tInv }}</td>
                            <td>{{ $tQty }}</td>
                            <td>{{ $tCost }}</td>
                            <td>{{ $tSale }}</td>
                            <td>{{ $tGP }}</td>
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
