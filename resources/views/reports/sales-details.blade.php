@extends('layouts.app')

@section('content')

@php
    $todayDate = now()->format('Y-m-d');
@endphp

<div x-data="{loading:false}">
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Filter Options</h3>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('sales.details') }}">
                    <div class="row">
                        <div class="col-md-4">
                            <label>Shop ID</label>
                            <select name="shopId" class="form-control">
                                <option value="">All Shop</option>
                                @foreach($shops as $shop)
                                    <option value="{{ $shop->ShopID }}" {{ request('shopId') == $shop->ShopID ? 'selected' : '' }}>
                                        {{ $shop->ShopID }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label>Start Date</label>
                            <input type="date" name="startDate" class="form-control" value="{{ request('startDate', $todayDate) }}">
                        </div>

                        <div class="col-md-4">
                            <label>End Date</label>
                            <input type="date" name="endDate" class="form-control" value="{{ request('endDate', $todayDate) }}">
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
                            <a href="{{ route('sales.details.excel', request()->query()) }}"
                                class="btn btn-success ml-2">
                                    <i class="fas fa-file-excel"></i> Export Excel
                            </a>

                            <a target="_blank"
                                href="{{ route('sales.details.pdf', request()->all()) }}"
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
            <div class="card-body text-center" style="font-size:18px">
                <div class="row">
                    <div class="col-md-4">
                        <b>Total Sale's Quantity:</b>
                        <span class="text-green"><b>{{ $summary->totalQuantity ?? 0 }}</b></span>
                    </div>
                    <div class="col-md-4">
                        <b>Total Sale's Amount:</b>
                        <span class="text-green"><b>৳{{ number_format($summary->totalAmount ?? 0,2) }}</b></span>
                    </div>
                    <div class="col-md-4">
                        <b>Shop:</b>
                        <span class="text-green"><b>{{ request('shopId') ?: 'All' }}</b></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Chart --}}
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><b>Category Wise Sales Quantity</b></h3>
            </div>
            <div class="card-body">
                <canvas id="categoryChart" height="140"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- Category Tables --}}
<div class="row">
    <div class="col-md-12">
        @foreach($groupedData as $category => $items)
            <div class="card">
                <div class="card-body table-responsive">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th colspan="7">
                                <h5>Category Name:
                                    <span class="text-blue"><b>{{ $category }}</b></span>
                                </h5>
                            </th>
                        </tr>
                        <tr>
                            <th>Barcode</th>
                            <th>Item Name</th>
                            <th>MRP</th>
                            <th>Qty</th>
                            <th>GrsAmt</th>
                            <th>DiscAmt</th>
                            <th>NetAmt</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($items as $item)
                            <tr>
                                <td>{{ $item->Barcode }}</td>
                                <td>{{ $item->Item }}</td>
                                <td>{{ $item->MRP }}</td>
                                <td>{{ $item->Qty }}</td>
                                <td>{{ $item->GrsAmt }}</td>
                                <td>{{ $item->DiscAmt }}</td>
                                <td>{{ $item->SaleAmount }}</td>
                            </tr>
                        @endforeach
                        <tr class="text-danger font-weight-bold">
                            <td colspan="3">Total Item ({{ $items->count() }})</td>
                            <td>{{ $items->sum('Qty') }}</td>
                            <td>{{ $items->sum('GrsAmt') }}</td>
                            <td>{{ $items->sum('DiscAmt') }}</td>
                            <td>{{ $items->sum('SaleAmount') }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
</div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const groupedData = @json($groupedData);

    const labels = [];
    const quantities = [];

    Object.keys(groupedData).forEach(category => {
        labels.push(category);
        let total = 0;
        groupedData[category].forEach(item => {
            total += parseFloat(item.Qty);
        });
        quantities.push(total);
    });

    const ctx = document.getElementById('categoryChart').getContext('2d');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total Quantity',
                data: quantities,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        precision: 0
                    }
                }
            }
        }
    });
});
</script>
@endpush
