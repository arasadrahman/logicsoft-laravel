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
                <form method="GET" action="{{ route('collection') }}" @submit="loading=true">
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
                            <a href="{{ route('collection.excel', request()->query()) }}"
                                class="btn btn-success ml-2">
                                    <i class="fas fa-file-excel"></i> Export Excel
                            </a>

                            <a target="_blank"
                                href="{{ route('collection.pdf', request()->all()) }}"
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

{{-- SUMMARY CARD (SAME DESIGN) --}}
<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body text-center" style="font-size:18px;">
                <div class="row">
                    <div class="col-md-6">
                        <b>Total Amount:</b>
                        <span class="text-green"><b>৳ {{ number_format($grandTotal,2) }}</b></span>
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

{{-- TABLE --}}
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-striped" border="1">
                    <thead>
                        <tr align="right">
                            <th>Date</th>
                            @foreach($payTypes as $type)
                                <th>{{ $type }}</th>
                            @endforeach
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $date => $payments)
                        @php $rowTotal = 0; @endphp
                        <tr align="right">
                            <td>{{ $date }}</td>
                            @foreach($payTypes as $type)
                                @php
                                    $amount = $payments[$type] ?? 0;
                                    $rowTotal += $amount;
                                @endphp
                                <td class="{{ $type == 'Cash' ? 'text-success font-weight-bold' : '' }}">
                                    {{ number_format($amount,2) }}
                                </td>
                            @endforeach
                            <td class="text-primary font-weight-bold">
                                {{ number_format($rowTotal,2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="100%" class="text-center">No data available</td>
                        </tr>
                        @endforelse

                        {{-- TOTAL FOOTER --}}
                        @if(count($data))
                        <tr class="text-danger font-weight-bold" align="right">
                            <td>Total</td>
                            @foreach($payTypes as $type)
                                <td>
                                    {{ number_format(collect($data)->sum(fn($d) => $d[$type] ?? 0),2) }}
                                </td>
                            @endforeach
                            <td>{{ number_format($grandTotal,2) }}</td>
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
