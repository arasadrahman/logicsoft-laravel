@extends('layouts.app')

@section('title', $pageTitle)

@section('content')
<div x-data="{ loading:false }">

    <!-- Filter Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><b>Filter Options</b></h3>
        </div>
        <div class="card-body">
            <form method="GET"
                  action="{{ route('showroom.wise.sales') }}"
                  @submit="loading=true">
                <div class="row">
                    <div class="col-md-3">
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
                    <div class="col-md-3">
                        <label>Start Date</label>
                        <input type="date"
                               name="start_date"
                               class="form-control"
                               value="{{ $startDate }}">
                    </div>
                    <div class="col-md-3">
                        <label>End Date</label>
                        <input type="date"
                               name="end_date"
                               class="form-control"
                               value="{{ $endDate }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button class="btn btn-primary w-100" :disabled="loading">
                            <span x-show="!loading">Submit</span>
                            <span x-show="loading">Loading...</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card mt-3">
        <div class="card-body table-responsive">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>Shop ID</th>
                    <th>Total Invoices</th>
                    <th>Total Qty</th>
                    <th>Total Cost</th>
                    <th>Total Amount</th>
                    <th>Total GP</th>
                    <th>Total DiscAmt</th>
                </tr>
                </thead>
                <tbody>
                @php
                    $totalInv = 0;
                    $totalQty = 0;
                    $totalCost = 0;
                    $totalAmt = 0;
                    $totalGP = 0;
                    $totalDiscAmt = 0;
                @endphp

                @forelse($data as $row)
                    @php
                        $totalInv += $row->TInv;
                        $totalQty += $row->TQty;
                        $totalCost += $row->TCostAmt;
                        $totalAmt += $row->TNetAmt;
                        $totalGP += $row->TGPAmt;
                        $totalDiscAmt += $row->TDiscAmt;
                    @endphp
                    <tr>
                        <td>{{ $row->ShopID }}</td>
                        <td>{{ $row->TInv }}</td>
                        <td>{{ $row->TQty }}</td>
                        <td>{{ number_format($row->TCostAmt,2) }}</td>
                        <td>{{ number_format($row->TNetAmt,2) }}</td>
                        <td>{{ number_format($row->TGPAmt,2) }}</td>
                        <td style="color: #7B3F00;"> {{ number_format($row->TDiscAmt) }} </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No data found</td>
                    </tr>
                @endforelse
                </tbody>

                @if(count($data))
                <tfoot>
                <tr style="font-weight:bold;color:red">
                    <td>Total</td>
                    <td>{{ $totalInv }}</td>
                    <td>{{ $totalQty }}</td>
                    <td>{{ number_format($totalCost,2) }}</td>
                    <td>{{ number_format($totalAmt,2) }}</td>
                    <td>{{ number_format($totalGP,2) }}</td>
                    <td style="color: #7B3F00;"> {{ number_format($totalDiscAmt) }} </td>
                </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    <!-- Charts -->
    <div class="row mt-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><b>Sales Amount by Shop</b></div>
                <div class="card-body">
                    <canvas id="amountChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><b>Invoices & Quantity</b></div>
                <div class="card-body">
                    <canvas id="combinedChart"></canvas>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="card mt-3">
    <div class="card-header">
        <b>Category Wise Sale Summary ({{ $shopId ?? 'All Shop' }})</b>
    </div>
    <div class="card-body table-responsive p-2">
        <table class="table table-striped">
            <thead>
            <tr>
                <th>Category</th>
                <th>Qty</th>
                <th>Cost</th>
                <th>Sale</th>
                <th>GP</th>
                <th>Discount</th>
            </tr>
            </thead>

            <tbody>
            @php
                $cQty=0;$cCost=0;$cAmt=0;$cGP=0;$cDisc=0;
            @endphp

            @foreach($saleItemsData as $row)
                @php
                    $cQty += $row->TQty;
                    $cCost += $row->TCostAmt;
                    $cAmt += $row->TNetAmt;
                    $cGP += $row->TGPAmt;
                    $cDisc += $row->TDiscAmt;
                @endphp
                <tr>
                    <td>{{ $row->Catagory }}</td>
                    <td>{{ $row->TQty }}</td>
                    <td>{{ number_format($row->TCostAmt,2) }}</td>
                    <td>{{ number_format($row->TNetAmt,2) }}</td>
                    <td>{{ number_format($row->TGPAmt,2) }}</td>
                    <td>{{ number_format($row->TDiscAmt) }}</td>
                </tr>
            @endforeach
            </tbody>

            <tfoot>
            <tr style="font-weight:bold;color:green">
                <td>Total</td>
                <td>{{ $cQty }}</td>
                <td>{{ number_format($cCost,2) }}</td>
                <td>{{ number_format($cAmt,2) }}</td>
                <td>{{ number_format($cGP,2) }}</td>
                <td>{{ number_format($cDisc) }}</td>
            </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection


@push('scripts')
<script>
const reportData = @json($data);

const labels = reportData.map(r => r.ShopID);
const amounts = reportData.map(r => r.TNetAmt);
const invoices = reportData.map(r => r.TInv);
const quantities = reportData.map(r => r.TQty);

const amountCtx = document.getElementById('amountChart').getContext('2d');
new Chart(amountCtx, {
    type: 'bar',
    data: {
        labels,
        datasets: [{
            label: 'Sales Amount',
            data: amounts
        }]
    }
});

const combinedCtx = document.getElementById('combinedChart').getContext('2d');
new Chart(combinedCtx, {
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
