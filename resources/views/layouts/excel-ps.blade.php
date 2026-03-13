<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Report</title>
    <style>
        body {
            margin: 0;
            padding: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th, td {
            border: 1px solid #000;
            padding: 6px 8px;
            word-wrap: break-word;
            white-space: normal;
        }

        .text-center {
            text-align: center;
            vertical-align: middle;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .company-name {
            font-size: 20pt;
            font-weight: bold;
            color: #ff0000;
            line-height: 1.3;
            word-break: break-word;
            white-space: normal;
        }

        .report-title {
            font-size: 14pt;
            font-weight: bold;
        }

        .no-gap {
            border: none !important;
            padding: 0 !important;
            height: 2px;
        }

        thead th,
        tfoot td {
            text-align: center;
            vertical-align: middle;
        }
    </style>
</head>
<body>

<table>
    <thead>
        <tr>
            <th colspan="7" class="company-name text-center">
                {{ $clientInfo->CompanyName ?? '' }}
            </th>
        </tr>
        <tr>
            <th colspan="7" class="text-center">
                {{ $clientInfo->Address ?? '' }}
            </th>
        </tr>
        <tr>
            <th colspan="7" class="report-title text-center">
                @yield('reportName')
            </th>
        </tr>
        <tr>
            <th colspan="7" class="text-center">
                Last Update :
                <strong>{{ \Carbon\Carbon::parse($StockDT)->format('d M Y,  h:m:i A') }}</strong>
            </th>
        </tr>
        <tr>
            <th colspan="7" class="no-gap"></th>
        </tr>
    </thead>

    @yield('content')

    <tfoot>
        <tr>
            <td colspan="7" class="no-gap"></td>
        </tr>
        <tr>
            <td colspan="7" class="text-center">
                Developed By: LogicSoft Ltd. |
                Export Date: {{ now()->format('m/d/Y h:i:s A') }}
            </td>
        </tr>
    </tfoot>
</table>

</body>
</html>
