<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link rel="icon" href="{{ asset('assets/img/favicon.ico') }}">
    <title>@yield('reportName')</title>

    <style>
        @page {
            margin: 160px 30px 40px 30px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background-color: #e6e6e6;
            color: #000;
            font-weight: bold;
            text-transform: uppercase;
        }

        th, td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
        }

        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .underline { text-decoration: underline; }
        .text-red { color: red; }
        .text-blue { color: blue; }

        .header {
            position: fixed;
            top: -160px;
            left: 0;
            right: 0;
            height: 110px;
            line-height: -0.75px;
        }

        .footer {
            position: fixed;
            bottom: -60px;
            left: 0;
            right: 0;
            height: 40px;
            font-size: 11px;
        }

        .header-table td {
            border: none;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: red;
        }

        .company-address {
            font-size: 12px;
        }

        .report-title {
            text-align: center;
            margin-top: 5px;
        }

        .footer div {
            display: inline-block;
            width: 33%;
        }

        .left { text-align: left; }
        .center { text-align: center; }
        .right { text-align: right; }

        .pagenum:before {
            content: counter(page);
        }

        .pagecount:before {
            content: counter(pages);
        }
    </style>
</head>

<body>

<div class="header">
    <table class="header-table">
        <tr>
            <td width="20%" align="left">
                <img src="{{ auth()->user()->Logo
                    ? public_path('assets/logo/' . auth()->user()->Logo)
                    : public_path('assets/img/CLMSLogo.png') }}"
                     style="width:90px;">
            </td>

            <td width="60%" align="center">
                <div class="company-name">
                    {{ $clientInfo->CompanyName ?? '' }}
                </div>
                <div class="company-address">
                    {{ $clientInfo->Address ?? '' }}
                </div>
            </td>

            <td width="20%"></td>
        </tr>
    </table>

    <hr>

    <div class="report-title">
        <h4 style="margin:0; text-decoration: underline;">
            @yield('reportName')
        </h4>
        <p style="margin:0; text-decoration: underline;">
            Date From :
            <strong>{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}</strong>
            To :
            <strong>{{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</strong>
        </p>
    </div>
</div>


<div class="footer">
    <div class="left">
        Developed By: LogicSoft Ltd.
    </div>
    <div class="center">
        Print Date: {{ now()->format('m/d/Y h:i:s A') }}
    </div>
    <div class="right">
        Page <span class="pagenum"></span> of {{ $numPagesTotal }}
    </div>
</div>


@yield('content')

</body>
</html>
