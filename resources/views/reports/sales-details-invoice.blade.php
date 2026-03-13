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
                <form method="GET" action="{{ route('sales.details.invoice') }}">
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
                            <a href="{{ route('sales.details.invoice.excel', request()->query()) }}"
                                class="btn btn-success ml-2">
                                    <i class="fas fa-file-excel"></i> Export Excel
                            </a>

                            <a target="_blank"
                                href="{{ route('sales.details.invoice.pdf', request()->all()) }}"
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

{{-- SUMMARY CARD (DESIGN SAME) --}}
<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body text-center" style="font-size:18px;">
                <div class="row">
                    <div class="col-md-3">
                        <b>Total Invoice:</b>
                        <span class="text-green"><b>{{ $summary->totalInvoice ?? 0 }}</b></span>
                    </div>
                    <div class="col-md-3">
                        <b>Total Sale Quantity:</b>
                        <span class="text-green"><b>{{ $summary->totalQuantity ?? 0 }}</b></span>
                    </div>
                    <div class="col-md-3">
                        <b>Total Sale Amount:</b>
                        <span class="text-green"><b>৳ {{ $summary->totalAmount ?? 0 }}</b></span>
                    </div>
                    <div class="col-md-3">
                        <b>Shop:</b>
                        <span class="text-green"><b>{{ $shopId ?? 'All' }}</b></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- INVOICE WISE TABLES (SAME STRUCTURE AS YOUR JS VERSION) --}}
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body table-responsive">

                @forelse($data as $invoiceKey => $items)

                <table class="table table-striped" border="1">
                    <thead>
                        <tr>
                            <th colspan="8">
                                <h5>
                                    Invoice No:
                                    <span class="text-blue"><b>{{ $invoiceKey }}</b></span>
                                    &nbsp;&nbsp;Time:
                                    <span class="text-blue"><b>{{ $items[0]->SaleDT }} {{ $items[0]->SaleTM }}</b></span>
                                    &nbsp;&nbsp;CounterID:
                                    <span class="text-blue"><b>{{ $items[0]->CounterID }}</b></span>
                                    &nbsp;&nbsp;UserID:
                                    <span class="text-blue"><b>{{ $items[0]->UserID }}</b></span>
                                </h5>
                            </th>
                        </tr>
                        <tr>
                            <th>Barcode</th>
                            <th>Category</th>
                            <th>Item</th>
                            <th>MRP</th>
                            <th>Qty</th>
                            <th>GrsAmt</th>
                            <th>DiscAmt</th>
                            <th>NetAmt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $row)
                        <tr>
                            <td>{{ $row->Barcode }}</td>
                            <td>{{ $row->Catagory }}</td>
                            <td>{{ $row->Item }}</td>
                            <td>{{ $row->MRP }}</td>
                            <td>{{ $row->Qty }}</td>
                            <td>{{ $row->GrsAmt }}</td>
                            <td>{{ $row->DiscAmt }}</td>
                            <td>{{ $row->SaleAmount }}</td>
                        </tr>
                        @endforeach

                        <tr class="text-red">
                            <td colspan="4">
                                <b>Total Item ({{ collect($items)->pluck('Item')->unique()->count() }})</b>
                            </td>
                            <td><b>{{ collect($items)->sum('Qty') }}</b></td>
                            <td><b>{{ collect($items)->sum('GrsAmt') }}</b></td>
                            <td><b>{{ collect($items)->sum('DiscAmt') }}</b></td>
                            <td><b>{{ collect($items)->sum('SaleAmount') }}</b></td>
                        </tr>
                    </tbody>
                </table>

                @empty
                    <p class="text-center">No data available</p>
                @endforelse

            </div>
        </div>
    </div>
</div>
</div>
@endsection
