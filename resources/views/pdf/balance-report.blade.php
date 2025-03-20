<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .letterhead {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
            width: 100%;
            display: table;
        }
        .letterhead-left {
            display: table-cell;
            width: 15%;
            vertical-align: top;
            padding-right: 15px;
        }
        .letterhead-right {
            display: table-cell;
            width: 85%;
            vertical-align: middle;
            text-align: center;
            padding-right: 15%;
        }
        .company-logo {
            max-width: 80px;
            height: auto;
        }
        .govt-name {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 3px;
            text-transform: uppercase;
        }
        .company-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 3px;
            text-transform: uppercase;
        }
        .company-address {
            font-size: 11px;
            margin-top: 3px;
            color: #666;
        }
        .title {
            font-size: 14px;
            font-weight: bold;
            margin: 15px 0;
            text-align: center;
            text-transform: uppercase;
            color: #2563eb;  /* Changed to blue */
        }
        .report-period {
            text-align: center;
            margin-bottom: 15px;
            font-size: 12px;
            color: #666;
        }
        .summary-box {
            border: 1px solid #93c5fd;  /* Light blue border */
            padding: 12px;
            margin: 15px 0;
            background-color: #f0f7ff;  /* Very light blue background */
            border-radius: 4px;
        }
        .summary-box h3 {
            font-size: 13px;
            margin: 0 0 10px 0;
            color: #2563eb;  /* Blue color */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 11px;
        }
        th, td {
            border: 1px solid #e5e7eb;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #2563eb;  /* Blue background */
            font-weight: bold;
            color: #ffffff;  /* White text */
        }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .fuel-type-header {
            margin: 15px 0;
            font-size: 13px;
            font-weight: bold;
            background-color: #f0f7ff;  /* Very light blue */
            padding: 8px;
            border-left: 3px solid #2563eb;  /* Blue accent */
            color: #2563eb;
        }
        .page-break { page-break-after: always; }
        .footer-section {
            margin-top: 30px;
            text-align: center;  /* Changed to center */
            font-size: 9px;  /* Smaller font size */
            color: #64748b;  /* Slate gray color */
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
        }
        .footer-section p {
            margin: 0;
            display: inline;  /* Make paragraphs inline */
        }
        .footer-section p:first-child::after {
            content: " | ";  /* Add separator between elements */
            margin: 0 5px;
        }
        tfoot tr td {
            background-color: #f0f7ff;  /* Very light blue */
            font-weight: bold;
            color: #2563eb;  /* Blue text */
        }
        tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
    </style>
</head>
<body>
    <div class="letterhead">
        <div class="letterhead-left">
            @if($company->company_logo)
                <img src="{{ storage_path('app/public/' . $company->company_logo) }}" class="company-logo">
            @endif
        </div>
        <div class="letterhead-right">
            <div class="govt-name">{{ $company->government_name }}</div>
            <div class="company-name">{!! nl2br(e($company->company_name)) !!}</div>
            <div class="company-address">
                {{ $company->street_address }} Telp. {{ $company->phone_number }}
            </div>
        </div>
    </div>

    <div class="title">LAPORAN SALDO BAHAN BAKAR</div>
    <div class="report-period">
        Periode: {{ date('d/m/Y', strtotime($start_date)) }} - {{ date('d/m/Y', strtotime($end_date)) }}
    </div>

    <div class="summary-box">
        <h3>Ringkasan Saldo</h3>
        <table>
            @foreach($totals as $fuelType => $data)
            <tr>
                <td style="width: 200px;">{{ $data['name'] }}</td>
                <td>:</td>
                <td>
                    Total Deposit: Rp {{ number_format($data['total_deposit'], 0, ',', '.') }}<br>
                    Saldo Saat Ini: Rp {{ number_format($data['current_balance'], 0, ',', '.') }}
                </td>
            </tr>
            @endforeach
        </table>
    </div>

    @foreach($balancesByFuelType as $fuelType => $fuelBalances)
    <div class="fuel-type-header">
        {{ $totals[$fuelType]['name'] }}
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Keterangan</th>
                <th>Debit</th>
                <th>Kredit</th>
                <th>Saldo</th>
            </tr>
        </thead>
        <tbody>
            @php $balance = 0; @endphp
            @foreach($fuelBalances as $index => $record)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ date('d/m/Y', strtotime($record->date)) }}</td>
                <td>Deposit BBM</td>
                <td class="text-right">Rp {{ number_format($record->deposit_amount, 0, ',', '.') }}</td>
                <td class="text-right">-</td>
                <td class="text-right">Rp {{ number_format($record->remaining_balance, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-right font-bold">Total</td>
                <td class="text-right font-bold">Rp {{ number_format($totals[$fuelType]['total_deposit'], 0, ',', '.') }}</td>
                <td class="text-right font-bold">-</td>
                <td class="text-right font-bold">Rp {{ number_format($totals[$fuelType]['current_balance'], 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    @if(!$loop->last)
    <div class="page-break"></div>
    @endif
    @endforeach

    <div class="footer-section">
        <p>Data diambil pada tanggal {{ date('d F Y H:i:s') }}</p>
        <p>Dicetak oleh: {{ auth()->user()->name }}</p>
    </div>
</body>
</html>
