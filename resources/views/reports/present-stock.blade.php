@extends('layouts.app')

@section('content')
<div x-data="{loading:false}">
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Filter Options</h3></div>
            <div class="card-body">
                <form method="GET" action="{{ route('present.stock') }}" @submit="loading=true">
                    <div class="row">
                        <div class="col-md-6">
                            <label>Shop ID</label>
                            <select name="shopId" class="form-control">
                                <option value="">All Shop</option>
                                @foreach($shops as $shop)
                                    <option value="{{ $shop->ShopID }}" {{ $shopId == $shop->ShopID ? 'selected' : '' }}>
                                        {{ $shop->ShopID }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label>Category</label>
                            <select name="cat" class="form-control">
                                <option value="">All Category</option>
                                @foreach($categories as $c)
                                    <option value="{{ $c->GroupName }}" {{ $cat == $c->GroupName ? 'selected' : '' }}>
                                        {{ $c->GroupName }}
                                    </option>
                                @endforeach
                            </select>
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
                            <a href="{{ route('present.stock.excel', request()->query()) }}"
                                class="btn btn-success ml-2">
                                    <i class="fas fa-file-excel"></i> Export Excel
                            </a>

                            <a target="_blank"
                                href="{{ route('present.stock.pdf', request()->all()) }}"
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
                <div class="row card-header">
                    <div class="col-md-3"><b>Stock Quantity:</b> <span class="text-green"><b>{{ $TQty }}</b></span></div>
                    <div class="col-md-3"><b>Stock Cost Amt:</b> <span class="text-green"><b>{{ $TCP }}</b></span></div>
                    <div class="col-md-3"><b>Stock Sale Amt:</b> <span class="text-green"><b>{{ $TSP }}</b></span></div>
                    <div class="col-md-3"><b>Shop:</b> <span class="text-green"><b>{{ $shopId ?? 'All' }}</b></span></div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-12">
                        <b>Last Update:</b>
                        <span class="text-green"><b>{{ $StockDT }}</b></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body table-responsive">
                @php
                    $grouped = $rows->groupBy('GroupName');
                @endphp

                @forelse($grouped as $group => $items)
                    <table class="table table-striped" border="1">
                        <thead>
                            <tr>
                                <th colspan="4">
                                    <h5>Category: <span class="text-blue"><b>{{ $group }}</b></span></h5>
                                </th>
                            </tr>
                            <tr>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Total Cost</th>
                                <th>Total Sale</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                                <tr>
                                    <td>{{ $item->PrdName }}</td>
                                    <td>{{ $item->TQty }}</td>
                                    <td>{{ $item->TCP }}</td>
                                    <td>{{ $item->TSP }}</td>
                                </tr>
                            @endforeach
                            <tr class="text-danger font-weight-bold">
                                <td>Total Items ({{ $items->count() }})</td>
                                <td>{{ $items->sum('TQty') }}</td>
                                <td>{{ $items->sum('TCP') }}</td>
                                <td>{{ $items->sum('TSP') }}</td>
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

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <canvas id="chartQty" width="400" height="600"></canvas>
            </div>
        </div>
    </div>
</div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const labels = @json($stock_pname);
const data   = @json($stock_qty);

const ctx = document.getElementById('chartQty').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
            label: 'Stock Quantity',
            data: data,
            borderWidth: 1
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            x: {
                beginAtZero: true,
                ticks: { precision: 0 }
            }
        }
    }
});
</script>
@endpush
