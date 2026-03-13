@extends('layouts.app')
@section('title', $pageTitle)

@section('content')
<div x-data="{ loading:false }">

    <!-- Filter -->
    <div class="card">
        <div class="card-header"><b>Filter</b></div>
        <div class="card-body">
            <form method="GET" action="{{ route('sales.summary') }}" @submit="loading=true">
                <div class="row">
                    <div class="col-md-3">
                        <label>Shop</label>
                        <select name="shop_id" class="form-control">
                            <option value="">All Shops</option>
                            @foreach($shops as $shop)
                                <option value="{{ $shop }}" {{ $shop == $shopId ? 'selected' : '' }}>
                                    {{ $shop }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label>Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                    </div>

                    <div class="col-md-3">
                        <label>End Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                    </div>

                    <div class="col-md-3 d-flex align-items-end">
                        <button class="btn btn-primary w-100" :disabled="loading">
                            <span x-show="!loading">Search</span>
                            <span x-show="loading">Loading...</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary -->
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body text-center" style="font-size: 18px;">
                    <div class="row card-header">
                        <div class="col-md-2">
                            <b>Total Invoice:</b>
                            <span class="text-green">
                                <b>{{ $summary->totalInvoice ?? 0 }}</b>
                            </span>
                        </div>

                        <div class="col-md-4">
                            <b>Total Sale's Quantity:</b>
                            <span class="text-green">
                                <b>{{ $summary->totalQuantity ?? 0 }}</b>
                            </span>
                        </div>

                        <div class="col-md-4">
                            <b>Total Sale's Amount:</b>
                            <span class="text-green">
                                <b>৳ {{ number_format($summary->totalAmount ?? 0, 2) }}</b>
                            </span>
                        </div>

                        <div class="col-md-2">
                            <b>Shop:</b>
                            <span class="text-green">
                                <b>{{ $shopId ?? 'All' }}</b>
                            </span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mt-2">
                            <b>
                                <span id="lastdatecount">
                                    {{ \Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate)) + 1 }}
                                    Days
                                </span>
                            </b>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Charts -->
    <div class="row mt-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><b>Sales Amount</b></div>
                <div class="card-body">
                    <canvas id="amountChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><b>Invoices vs Quantity</b></div>
                <div class="card-body">
                    <canvas id="combinedChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card mt-3">
        <div class="card-body table-responsive">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Invoices</th>
                    <th>Quantity</th>
                    <th>Amount</th>
                </tr>
                </thead>
                <tbody>
                @forelse($data as $row)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($row->SaleDT)->format('M d') }}</td>
                        <td>{{ $row->TInv }}</td>
                        <td>{{ $row->TQty }}</td>
                        <td>৳ {{ number_format($row->SaleAmt,2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">No data found</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection


@push('scripts')
<script>
const reportData = @json($data);

const labels = reportData.map(r => {
    const d = new Date(r.SaleDT);
    return d.toLocaleString('default', { month: 'short', day: 'numeric' });
});

const amounts = reportData.map(r => r.SaleAmt);
const invoices = reportData.map(r => r.TInv);
const quantities = reportData.map(r => r.TQty);

// Amount chart
new Chart(document.getElementById('amountChart'), {
    type: 'bar',
    data: {
        labels,
        datasets: [{
            label: 'Sales Amount',
            data: amounts
        }]
    }
});

// Combined chart
new Chart(document.getElementById('combinedChart'), {
    type: 'bar',
    data: {
        labels,
        datasets: [
            {
                label: 'Invoices',
                data: invoices
            },
            {
                label: 'Quantity',
                data: quantities
            }
        ]
    }
});
</script>
@endpush
