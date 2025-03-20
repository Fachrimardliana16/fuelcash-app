<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 40px 30px;  /* Increased margins */
        }

        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .letterhead-img {
            width: 100%;
            max-height: 150px;
            object-fit: contain;
        }

        .title {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
            padding: 10px;
            background-color: #f0f7ff;
            border-radius: 5px;
            color: #2563eb;
            border-left: 4px solid #2563eb;
        }

        table {
            width: 100%;
            table-layout: fixed;  /* Fixed table layout */
            margin: 10px 0;
            border-collapse: collapse;
            page-break-inside: auto;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px 4px;  /* Reduced padding */
            text-align: left;
            font-size: 9px;    /* Smaller font */
            vertical-align: top;
        }

        th {
            background-color: #2563eb;
            color: white;
            font-size: 11px;
            padding: 8px;
        }

        td {
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
            font-size: 11px;
        }

        tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .text-right {
            text-align: right;
            white-space: nowrap;
            color: #2563eb;
            font-weight: bold;
        }

        .summary {
            margin: 15px 0;
            background-color: #f8fafc;
            border: 1px solid #93c5fd;
            border-radius: 4px;
            padding: 12px;
        }

        .summary table td {
            padding: 4px 8px;
            border: none;
        }

        .description {
            max-width: none;
            word-wrap: break-word;
            padding-right: 10px;
        }

        .date-col {
            width: 10%;  /* Increased width for date column */
            white-space: nowrap;  /* Prevent date wrapping */
        }

        .vehicle-col {
            width: 15%;
        }

        .fueltype-col {
            width: 10%;
        }

        .amount-col {
            width: 12%;
        }

        .balance-col {
            width: 14%;
        }

        .description-col {
            width: 25%;
        }

        .vehicle-info {
            line-height: 1.1;
        }

        .vehicle-owner {
            font-weight: bold;
        }

        .vehicle-plate {
            color: #666;
        }

        .fuel-info {
            line-height: 1.1;
            font-size: 9px;
        }

        .fuel-type {
            font-weight: bold;
            margin-bottom: 1px;
        }

        .fuel-name {
            color: #666;
        }

        .volume-col {
            width: 12%;
        }

        .letterhead {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #000;
            width: 100%;
            display: table;
        }
        .letterhead-left {
            display: table-cell;
            width: 15%;
            vertical-align: top;
            padding-right: 20px;
        }
        .letterhead-right {
            display: table-cell;
            width: 85%;
            vertical-align: middle;
            text-align: center;
            padding-right: 15%;
        }
        .company-logo {
            max-width: 100px;
            height: auto;
        }
        .govt-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .regency-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .company-address {
            font-size: 12px;
            margin-top: 5px;
        }

        tfoot td {
            background-color: #f0f7ff;
            font-weight: bold;
            color: #2563eb;
        }

        .filter-info {
            margin: 10px 0;
            padding: 8px;
            background-color: #f0f7ff;
            border: 1px solid #93c5fd;
            border-radius: 4px;
            font-size: 11px;
            color: #2563eb;
        }

        .fuel-type-header {
            margin: 20px 0 10px 0;
            padding: 8px;
            background-color: #f0f7ff;
            border-left: 4px solid #2563eb;
            color: #2563eb;
            font-weight: bold;
            font-size: 14px;
        }

        .no-col { width: 4%; }

        .page-break {
            page-break-after: always;
        }

        /* Add style for filter info labels */
        .filter-label {
            display: inline-block;
            width: 120px;  /* Fixed width for labels */
            text-align: right;
            padding-right: 8px;
        }

        /* Add style for summary table */
        .summary table td:first-child {
            width: 120px;  /* Fixed width for labels */
            text-align: right;
            padding-right: 8px;
        }

        .summary table td:nth-child(2) {
            padding-left: 0;  /* Remove left padding after colon */
        }

        /* Modified filter info styles */
        .filter-info {
            /* existing styles */
        }
        .filter-info strong {
            display: inline-block;
            width: 120px;
            text-align: left;
        }
        .filter-info strong::after {
            content: ":";
            margin-left: 5px;
        }

        /* Modified summary table styles */
        .summary table {
            width: 100%;
        }
        .summary table td:first-child {
            width: 120px;
            text-align: left;
            padding-right: 8px;
        }
        .summary table td.colon {
            width: 15px;
            padding: 4px 0;
            text-align: center;
        }
        .summary table td:last-child {
            padding-left: 0;
        }

        /* Modified styles for labels and values alignment */
        .filter-info {
            /* existing styles */
        }
        .info-row {
            display: flex;
            align-items: flex-start;
            margin: 2px 0;
        }
        .info-label {
            width: 100px;
            text-align: left;
        }
        .info-colon {
            width: 15px;
            text-align: center;
        }
        .info-value {
            flex: 1;
            padding-left: 5px;
        }

        /* Modified summary table styles */
        .summary table td.label {
            width: 100px;
            text-align: left;
            padding-right: 0;
        }
        .summary table td.colon {
            width: 15px;
            text-align: center;
            padding: 4px 0;
        }
        .summary table td.value {
            text-align: left;
            padding-left: 5px;
        }

        /* Modified styles for label alignment */
        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 4px;
            font-size: 11px;  /* Add this line */
            color: #2563eb;   /* Add this line */
        }
        .info-label {
            display: table-cell;
            width: 100px;
            text-align: left;
            padding-right: 0;
            font-size: 11px;  /* Add this line */
        }
        .info-colon {
            display: table-cell;
            width: 15px;
            text-align: center;
            font-size: 11px;  /* Add this line */
        }
        .info-value {
            display: table-cell;
            padding-left: 5px;
            font-size: 11px;  /* Add this line */
        }

        /* Modified summary table styles */
        .summary table {
            margin: 0;
        }
        .summary table td.label {
            width: 100px;
            text-align: left;
            padding-right: 0;
        }
        .summary table td.colon {
            width: 15px;
            text-align: center;
        }
        .summary table td.value {
            text-align: left;
            padding-left: 5px;
        }

        /* Modified summary table styles */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-table td {
            border: none;
            padding: 2px 0;
        }
        .summary-table .label {
            width: 100px;
            text-align: left;
            padding-right: 0;
        }
        .summary-table .colon {
            width: 15px;
            text-align: center;
            padding: 0;
        }
        .summary-table .value {
            text-align: left;
            padding-left: 5px;
        }

        /* Replace footer styles */
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            font-size: 9px;
            color: #666;
            text-align: center;
        }
        .footer p {
            margin-bottom: 5px;
            color: #2563eb;
        }
        .footer-info {
            display: inline-flex;
            gap: 15px;
            color: #666;
            align-items: center;
        }
        .footer-info span {
            white-space: nowrap;
        }
        .footer-separator {
            color: #93c5fd;
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

    <div class="title">LAPORAN TRANSAKSI BBM</div>

    <div class="filter-info">
        @if(!empty($vehicleType))
        <div class="info-row">
            <span class="info-label">Jenis Kendaraan</span>
            <span class="info-colon">:</span>
            <span class="info-value">{{ $vehicleType }}</span>
        </div>
        @endif
        <div class="info-row">
            <span class="info-label">Jenis BBM</span>
            <span class="info-colon">:</span>
            <span class="info-value">{{ !empty($fuelType) ? $fuelType : 'Semua' }}</span>
        </div>
        @if(!empty($vehiclePlate))
        <div class="info-row">
            <span class="info-label">Nomor Kendaraan</span>
            <span class="info-colon">:</span>
            <span class="info-value">{{ $vehiclePlate }}</span>
        </div>
        @endif
        <div class="info-row">
            <span class="info-label">Periode</span>
            <span class="info-colon">:</span>
            <span class="info-value">{{ $dateRange }}</span>
        </div>
    </div>

    @foreach($transactionsByFuelType as $fuelTypeName => $fuelTypeTransactions)
        <div class="fuel-type-header">{{ $fuelTypeName }}</div>

        <div class="summary">
            <div class="info-row">
                <span class="info-label">Saldo Awal</span>
                <span class="info-colon">:</span>
                <span class="info-value">Rp {{ number_format($totals[$fuelTypeName]['initial_balance'], 0, ',', '.') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Total Transaksi</span>
                <span class="info-colon">:</span>
                <span class="info-value">Rp {{ number_format($totals[$fuelTypeName]['total_amount'], 0, ',', '.') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Sisa Saldo</span>
                <span class="info-colon">:</span>
                <span class="info-value">Rp {{ number_format($totals[$fuelTypeName]['remaining_balance'], 0, ',', '.') }}</span>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th class="no-col">No</th>
                    <th class="date-col">Tanggal</th>
                    <th class="vehicle-col">Kendaraan</th>
                    <th class="fueltype-col">BBM</th>
                    <th class="description-col">Uraian</th>
                    <th class="amount-col">Jumlah (Rp)</th>
                    <th class="volume-col">Volume (L)</th>
                    <th class="balance-col">Sisa Saldo</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $remainingBalance = $totals[$fuelTypeName]['initial_balance'];
                @endphp
                @foreach($fuelTypeTransactions as $index => $transaction)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ date('d/m/Y', strtotime($transaction->usage_date)) }}</td>
                        <td>
                            <div class="vehicle-info">
                                <div class="vehicle-owner">{{ $transaction->owner }}</div>
                                <div class="vehicle-plate">{{ $transaction->vehicle->license_plate }}</div>
                            </div>
                        </td>
                        <td>
                            <div class="fuel-info">
                                <div class="fuel-type">{{ $transaction->fuelType->name }}</div>
                                <div class="fuel-name">{{ $transaction->fuel->name }}</div>
                            </div>
                        </td>
                        <td>{{ $transaction->usage_description }}</td>
                        <td class="text-right">{{ number_format($transaction->amount, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($transaction->volume, 2, ',', '.') }}</td>
                        @php
                            $remainingBalance -= $transaction->amount;
                        @endphp
                        <td class="text-right">{{ number_format($remainingBalance, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-right"><strong>Total {{ $fuelTypeName }}:</strong></td>
                    <td class="text-right"><strong>{{ number_format($totals[$fuelTypeName]['total_amount'], 0, ',', '.') }}</strong></td>
                    <td class="text-right"><strong>{{ number_format($totals[$fuelTypeName]['total_volume'], 2, ',', '.') }}</strong></td>
                    <td class="text-right"><strong>{{ number_format($totals[$fuelTypeName]['remaining_balance'], 0, ',', '.') }}</strong></td>
                </tr>
            </tfoot>
        </table>

        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach

    <div class="footer">
        <p>Dokumen ini dihasilkan secara otomatis oleh sistem FuelCash App &copy; {{ date('Y') }}</p>

        <div class="footer-info">
            <span>ID Laporan: RPT-{{ now()->format('YmdHis') }}</span>
            <span class="footer-separator">|</span>
            <span>Diunduh oleh: {{ auth()->user()->name ?? 'System' }}</span>
            <span class="footer-separator">|</span>
            <span>Tanggal: {{ \Carbon\Carbon::now()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s') }} WIB</span>
        </div>
    </div>
</body>

</html>
