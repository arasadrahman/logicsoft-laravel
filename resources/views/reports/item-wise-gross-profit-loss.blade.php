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
                <form method="GET" action="{{ route('item.wise.gross.profit.loss') }}" @submit="loading=true">
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
                            <a href="{{ route('item.wise.gross.profit.loss.excel', request()->query()) }}"
                                class="btn btn-success ml-2">
                                    <i class="fas fa-file-excel"></i> Export Excel
                            </a>

                            <a target="_blank"
                                href="{{ route('item.wise.gross.profit.loss.pdf', request()->all()) }}"
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
                    <div class="col-md-3">
                        <b>Total Item:</b>
                        <span class="text-green"><b>{{ $total }}</b></span>
                    </div>
                    <div class="col-md-3">
                        <b>Total Cost Amount:</b>
                        <span class="text-red"><b>৳ {{ number_format($totalCostAmount,2) }}</b></span>
                    </div>
                    <div class="col-md-3">
                        <b>Total Sale Amount:</b>
                        <span class="text-green"><b>৳ {{ number_format($totalSaleAmount,2) }}</b></span>
                    </div>
                    <div class="col-md-3">
                        <b>Total GP Amount:</b>
                        <span class="text-green"><b>৳ {{ number_format($totalGrossAmount,2) }}</b></span>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col">
                        <b>Shop:</b>
                        <span class="text-green"><b>{{ $shopId ?? 'All' }}</b></span>
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

                @forelse($grouped as $category => $items)
                    <table class="table table-striped" border="1">
                        <thead>
                            <tr>
                                <th colspan="7">
                                    <h5>
                                        Catagory No:
                                        <span class="text-blue"><b>{{ $category }}</b></span>
                                    </h5>
                                </th>
                            </tr>
                            <tr>
                                <th>Barcode</th>
                                <th>Item Name</th>
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
                                <tr>
                                    <td>{{ $item->Barcode }}</td>
                                    <td>{{ $item->Item }}</td>
                                    <td>{{ $item->MRP }}</td>
                                    <td>{{ $item->Qty }}</td>
                                    <td>{{ $item->CostAmt }}</td>
                                    <td>{{ $item->SaleAmount }}</td>
                                    <td>{{ $item->GPAmt }}</td>
                                </tr>

                                @php
                                    $cQty  += $item->Qty;
                                    $cCost += $item->CostAmt;
                                    $cSale += $item->SaleAmount;
                                    $cGP   += $item->GPAmt;
                                @endphp
                            @endforeach

                            <tr class="text-danger font-weight-bold">
                                <td colspan="3">
                                    Total Item ({{ $items->unique('Item')->count() }})
                                </td>
                                <td>{{ $cQty }}</td>
                                <td>{{ $cCost }}</td>
                                <td>{{ $cSale }}</td>
                                <td>{{ $cGP }}</td>
                            </tr>
                        </tbody>
                    </table>
                @empty
                    <div class="text-center">No data available</div>
                @endforelse

            </div>
        </div>
    </div>
</div>

</div>

@endsection
