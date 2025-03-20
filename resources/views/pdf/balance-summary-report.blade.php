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
            color: #2563eb;
        }
        .report-period {
            text-align: center;
            margin-bottom: 15px;
            font-size: 12px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 10px;
        }
        th, td {
            border: 1px solid #e5e7eb;
            padding: 5px 8px;
            text-align: left;
        }
        th {
            background-color: #2563eb;
            color: white;
            font-weight: bold;
        }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        tfoot tr td {
            background-color: #f0f7ff;
            font-weight: bold;
            padding: 6px 8px;
        }
        tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        tbody tr td {
            line-height: 1.2;
        }
        .footer-section {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
        }
        .fuel-type-header {
            background-color: #2563eb;
            color: white;
            font-weight: bold;
            padding: 6px 10px;
            margin: 10px 0 0 0;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .page-break {
            page-break-before: always;
        }
        .avoid-break {
            page-break-inside: avoid;
        }
        .fuel-details-container {
            display: block;
            margin-top: 20px;
        }

        .fuel-type-block {
            width: 100%;
            margin-bottom: 20px;
        }

        .detail-section {
            margin: 0;
            border: 1px solid #e5e7eb;
            border-top: none;
            background: #fff;
        }

        .detail-table {
            margin: 0;
        }

        .detail-table td {
            padding: 6px 12px;
            border: none;
            border-bottom: 1px solid #f0f0f0;
        }

        .detail-table tr:last-child td {
            border-bottom: none;
        }

        .detail-table .label {
            width: 180px;
            color: #64748b;
            font-weight: normal;
        }

        .detail-table .total td {
            background-color: #f8fafc;
            font-weight: bold;
            color: #2563eb;
            padding: 10px 12px;
        }

        .detail-heading {
            font-weight: bold;
            font-size: 11px;
            color: #374151;
            margin: 25px 0 10px 0;
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

    <div class="title">LAPORAN REKAPITULASI UANG MUKA BBM</div>
    <div class="report-period">
        Periode: {{ date('d/m/Y', strtotime($start_date)) }} - {{ date('d/m/Y', strtotime($end_date)) }}
    </div>

    @php
        $totalInitialBalance = 0;
        $totalDeposit = 0;
        $totalAmount = 0;
        $totalUsage = 0;
        $totalCurrentBalance = 0;

        // Calculate totals
        foreach($summaries as $summary) {
            $totalInitialBalance += $summary['initial_balance'];
            $totalDeposit += $summary['deposit'];
            $totalAmount += $summary['total_amount'];
            $totalUsage += $summary['usage'];
            $totalCurrentBalance += $summary['current_balance'];
        }
    @endphp

    @if(empty($selectedFuelTypeId))
        {{-- Summary for all fuel types --}}
        <table class="summary-table">
            <thead>
                <tr>
                    <th>Jenis BBM</th>
                    <th class="text-right">Saldo Awal</th>
                    <th class="text-right">Penambahan Saldo</th>
                    <th class="text-right">Jumlah</th>
                    <th class="text-right">Pemakaian</th>
                    <th class="text-right">Saldo Sekarang</th>
                </tr>
            </thead>
            <tbody>
                @foreach($summaries as $fuelTypeId => $summary)
                    <tr>
                        <td>{{ $summary['fuel_type_name'] }}</td>
                        <td class="text-right">Rp {{ number_format($summary['initial_balance'], 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($summary['deposit'], 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($summary['total_amount'], 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($summary['usage'], 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($summary['current_balance'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td class="font-bold">TOTAL</td>
                    <td class="text-right">Rp {{ number_format($totalInitialBalance, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($totalDeposit, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($totalAmount, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($totalUsage, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($totalCurrentBalance, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        <div class="fuel-details-container">
            <div class="detail-heading">DETAIL REKAPITULASI SALDO</div>
            @foreach($summaries as $fuelTypeId => $summary)
                <div class="fuel-type-block">
                    <div class="fuel-type-header">
                        {{ $summary['fuel_type_name'] }}
                    </div>
                    @include('pdf.partials.fuel-type-detail', ['summary' => $summary])
                </div>
            @endforeach
        </div>
    @else
        {{-- Detail for single fuel type --}}
        @foreach($summaries as $fuelTypeId => $summary)
            <div class="avoid-break">
                <div class="fuel-type-header">
                    {{ $summary['fuel_type_name'] }}
                </div>
                @include('pdf.partials.fuel-type-detail', ['summary' => $summary])
            </div>
        @endforeach
    @endif

    <div class="footer-section">
        <p>Data diambil pada tanggal {{ date('d F Y H:i:s') }} | Dicetak oleh: {{ auth()->user()->name }}</p>
    </div>
</body>
</html>
