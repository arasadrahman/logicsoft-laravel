@extends('layouts.app')

@section('title', $pageTitle)

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><b><u>Billing Information</u></b></h3>
            </div>
            <div class="card-body">
                Present Due :
                <span class="text-danger">
                    <b>{{ $data->DueAmt ?? 0 }}</b>
                </span><br>

                Minimum Payment :
                <span class="text-dark">
                    <b>{{ $data->MinPay ?? 0 }}</b>
                </span><br>

                Last Payment Date :
                <span class="text-danger">
                    <b>{{ $data->LastPmtDT ?? '' }}</b>
                </span><br>

                Amount :
                <span class="text-dark">
                    <b>{{ $data->Amount ?? 0 }}</b>
                </span><br>

                Payment Type :
                <span class="text-dark">
                    <b>{{ $data->PayType ?? '' }}</b>
                </span><br>

                License Expire Date :
                <span class="text-danger">
                    <b>{{ $data->ExpDT ?? '' }}</b>
                </span><br>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-6">
        <img src="https://my.logicsoftltd.com/assets/img/branding.jpg" class="img-fluid" alt="Responsive Image">
    </div>
</div>
@endsection
