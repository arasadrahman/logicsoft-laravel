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
                    <form method="GET" action="{{ route('bank.withdraw') }}" @submit="loading=true">
                        <div class="row">
                            <div class="col-md-4">
                                <label>Shop ID</label>
                                <select class="form-control" name="shopId">
                                    <option value="">All Shop</option>
                                    @foreach($shops as $shop)
                                        <option value="{{ $shop->ShopID }}" @selected(request('shopId') == $shop->ShopID)>
                                            {{ $shop->ShopID }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label>Start Date</label>
                                <input type="date" class="form-control" name="startDate" value="{{ $startDate }}">
                            </div>

                            <div class="col-md-4">
                                <label>End Date</label>
                                <input type="date" class="form-control" name="endDate" value="{{ $endDate }}">
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
                            <a href="{{ route('bank.withdraw.excel', request()->query()) }}"
                                class="btn btn-success ml-2">
                                    <i class="fas fa-file-excel"></i> Export Excel
                            </a>

                            <a target="_blank"
                                href="{{ route('bank.withdraw.pdf', request()->all()) }}"
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

    <!-- Summary -->
    <div class="row justify-content-center mt-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body text-center" style="font-size:18px">
                    <div class="row">
                        <div class="col-md-6">
                            <b>Total Amount:</b>
                            <span class="text-green"><b>৳{{ number_format($totalAmount, 2) }}</b></span>
                        </div>
                        <div class="col-md-6">
                            <b>Shop:</b>
                            <span class="text-green"><b>{{ $shopId ?? 'All' }}</b></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-responsive">
                    <div class="card-body table-responsive">
                        <table class="table table-striped" border="1">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    @foreach($expHeads as $head)
                                        <th>{{ $head }}</th>
                                    @endforeach
                                    <th class="text-bold">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($table as $date => $rows)
                                    <tr>
                                        <td>{{ $date }}</td>
                                        @php $rowTotal = 0; @endphp
                                        @foreach($expHeads as $head)
                                            @php
                                                $amount = $rows[$head] ?? 0;
                                                $rowTotal += $amount;
                                            @endphp
                                            <td align="right">{{ $amount }}</td>
                                        @endforeach
                                        <td align="right" class="text-bold">{{ $rowTotal }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="100%" class="text-center">No data available</td>
                                    </tr>
                                @endforelse

                                @if($table)
                                    <tr class="text-bold text-danger">
                                        <td>Total</td>
                                        @foreach($expHeads as $head)
                                            <td align="right">
                                                {{ collect($table)->sum(fn($row) => $row[$head] ?? 0) }}
                                            </td>
                                        @endforeach
                                        <td align="right" class="text-bold">{{ $totalAmount }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
