@extends('layouts.app')

@section('content')
<div x-data="{loading:false}">

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Filter Options</h3>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('invoice.wise.gross.profit.loss') }}" @submit="loading=true">
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
                            <a href="{{ route('invoice.wise.gross.profit.loss.excel', request()->query()) }}"
                                class="btn btn-success ml-2">
                                    <i class="fas fa-file-excel"></i> Export Excel
                            </a>

                            <a target="_blank"
                                href="{{ route('invoice.wise.gross.profit.loss.pdf', request()->all()) }}"
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

<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body text-center" style="font-size:18px;">
                <div class="row">
                    <div class="col-md-4"><b>Total Invoice:</b> <span class="text-green"><b>{{ $total }}</b></span></div>
                    <div class="col-md-4"><b>Total Qty:</b> <span class="text-red"><b>{{ $totalQty }}</b></span></div>
                    <div class="col-md-4"><b>Total Cost Amount:</b> <span class="text-red"><b>৳ {{ number_format($totalCostAmount,2) }}</b></span></div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-4"><b>Total Sale Amount:</b> <span class="text-green"><b>৳ {{ number_format($totalSaleAmount,2) }}</b></span></div>
                    <div class="col-md-4"><b>Total GP Amount:</b> <span class="text-green"><b>৳ {{ number_format($totalGrossAmount,2) }}</b></span></div>
                    <div class="col-md-4"><b>Shop:</b> <span class="text-green"><b>{{ $shopId ?? 'All' }}</b></span></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Qty</th>
                            <th>CostAmt</th>
                            <th>SaleAmount</th>
                            <th>GPAmt</th>
                            <th>ShopID</th>
                            <th>UserID</th>
                            <th>SaleDT</th>
                            <th>SaleTM</th>
                            <th>Counter</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td>{{ $row->Invoice }}</td>
                                <td>{{ $row->Qty }}</td>
                                <td>{{ $row->CostAmount }}</td>
                                <td>{{ $row->SaleAmount }}</td>
                                <td>{{ $row->GPAmt }}</td>
                                <td>{{ $row->ShopID }}</td>
                                <td>{{ $row->UserID }}</td>
                                <td>{{ $row->SaleDT }}</td>
                                <td>{{ $row->SaleTM }}</td>
                                <td>{{ $row->CounterID }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">No data available</td>
                            </tr>
                        @endforelse

                        @if($rows->count())
                        <tr class="text-danger font-weight-bold">
                            <td>Total</td>
                            <td>{{ $totalQty }}</td>
                            <td>{{ $totalCostAmount }}</td>
                            <td>{{ $totalSaleAmount }}</td>
                            <td>{{ $totalGrossAmount }}</td>
                            <td colspan="5"></td>
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
