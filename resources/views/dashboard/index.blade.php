@extends('layouts.app')

@section('content')

<style>
#pagination {
    display: flex;
    flex-wrap: wrap;
}
.page-item {
    margin-top: 3px;
}
.last-row {
    font-weight: bold;
    color: red;
}
.text-green {
    color: #28a745;
}
</style>

<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body text-center" style="font-size:18px;">
                <div class="row">
                    <div class="col-md-4">
                        <b>Today Invoices:</b>
                        <span class="text-green"><b>{{ $summary->totalInv ?? 0 }}</b></span>
                    </div>
                    <div class="col-md-4">
                        <b>Today Sale's Quantity:</b>
                        <span class="text-green"><b>{{ $summary->totalQty ?? 0 }}</b></span>
                    </div>
                    <div class="col-md-4">
                        <b>Today Sale's Amount:</b>
                        <span class="text-green"><b>৳{{ $summary->totalAmount ?? 0 }}</b></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Sales Table --}}
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Shop ID</th>
                            <th>Total Invoices</th>
                            <th>Total Sale Qty</th>
                            <th>Total Sale Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $row)
                        <tr>
                            <td>{{ $row->ShopID }}</td>
                            <td>{{ $row->TInv }}</td>
                            <td>{{ $row->TQty }}</td>
                            <td>{{ $row->TNetAmt }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">No data available</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Product Sale Chart --}}
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><b>Today Sale's Product</b></h3>
            </div>
            <div class="card-body">
                <canvas id="productSaleChart"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- Line Charts --}}
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><b>Sales Quantity (Last 10 Days)</b></h3>
            </div>
            <div class="card-body">
                <canvas id="salesQuantityChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><b>Sales Amount (Last 10 Days)</b></h3>
            </div>
            <div class="card-body">
                <canvas id="salesAmountChart"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- Stock Summary --}}
<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body text-center" style="font-size:18px;">
                <div class="row">
                    <div class="col-md-4">
                        <b>Stock Quantity:</b>
                        <span class="text-green"><b>{{ $stockSummary->sq ?? 0 }}</b></span>
                    </div>
                    <div class="col-md-4">
                        <b>Stock's Cost Amount:</b>
                        <span class="text-green"><b>৳{{ $stockSummary->stca ?? 0 }}</b></span>
                    </div>
                    <div class="col-md-4">
                        <b>Stock's Sale Amount:</b>
                        <span class="text-green"><b>৳{{ $stockSummary->stsa ?? 0 }}</b></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Stock Table --}}
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Shop ID</th>
                            <th>Stock Quantity</th>
                            <th>Stock Cost Amount</th>
                            <th>Stock Sale Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stockData as $row)
                        <tr>
                            <td>{{ $row->ShopID }}</td>
                            <td>{{ $row->TQty }}</td>
                            <td>{{ $row->TCP }}</td>
                            <td>{{ $row->TSP }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">No data available</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Stock Quantity Chart --}}
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><b>Present Stock</b></h3>
            </div>
            <div class="card-body">
                <canvas id="stockQuantityChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const productSales = @json($productSales);
const last10Days = @json($last10Days);
const stockChartData = @json($stockData);

function productCharts(labels, data) {
    new Chart(document.getElementById('productSaleChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Sale Quantity',
                data: data,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: { indexAxis: 'y', responsive: true }
    });
}

function createLineChart(canvasId, label, labels, data) {
    new Chart(document.getElementById(canvasId), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: label,
                data: data,
                backgroundColor: 'rgba(75,192,192,0.3)',
                borderColor: 'rgba(75,192,192,1)',
                borderWidth: 2
            }]
        },
        options: { responsive: true }
    });
}

function stockQuantityChart(labels, data) {
    new Chart(document.getElementById('stockQuantityChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Stock Quantity',
                data: data,
                backgroundColor: 'rgba(153,102,255,0.5)',
                borderColor: 'rgba(153,102,255,1)',
                borderWidth: 1
            }]
        },
        options: { indexAxis: 'y', responsive: true }
    });
}

const catLabels = productSales.map(i => i.Catagory);
const catData = productSales.map(i => i.Qty);
productCharts(catLabels, catData);

const dates = last10Days.map(i => i.SaleDT);
const qty = last10Days.map(i => i.Qty);
const amount = last10Days.map(i => i.Amount);
createLineChart('salesQuantityChart', 'Sales Quantity', dates, qty);
createLineChart('salesAmountChart', 'Sales Amount', dates, amount);

const stockLabels = stockChartData.map(i => i.ShopID);
const stockQty = stockChartData.map(i => i.TQty);
stockQuantityChart(stockLabels, stockQty);
</script>

@endsection
