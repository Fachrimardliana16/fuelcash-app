<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 40px 30px;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #1e3a8a;
            line-height: 1.6;
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
            color: #000;
        }

        .company-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
            color: #000;
        }

        .company-address {
            font-size: 12px;
            margin-top: 5px;
            color: #000;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
            padding: 10px;
            background-color: #f0f7ff;
            border-radius: 5px;
            color: #2563eb;
            border-left: 4px solid #2563eb;
        }

        .summary-card {
            background-color: #f8fafc;
            border: 1px solid #93c5fd;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }

        .summary-title {
            font-size: 14px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 10px;
            border-bottom: 2px solid #bfdbfe;
            padding-bottom: 5px;
        }

        .summary-grid {
            display: table;
            width: 100%;
            margin-top: 10px;
        }

        .summary-item {
            display: table-row;
            line-height: 2;
        }

        .summary-label {
            display: table-cell;
            width: 200px;
            color: #64748b;
            font-size: 12px;
        }

        .summary-value {
            display: table-cell;
            font-weight: bold;
            color: #1e3a8a;
            font-size: 12px;
        }

        .transactions-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 11px;
        }

        .transactions-table th {
            background-color: #2563eb;
            color: white;
            padding: 10px;
            text-align: left;
        }

        .transactions-table td {
            padding: 8px;
            border-bottom: 1px solid #e2e8f0;
        }

        .transactions-table tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .amount {
            text-align: right;
            font-family: 'Courier New', monospace;
        }

        .positive {
            color: #059669;
        }

        .negative {
            color: #dc2626;
        }

        .fuel-type-header {
            background-color: #dbeafe;
            color: #1e3a8a;
            padding: 10px;
            font-weight: bold;
            margin-top: 20px;
            border-radius: 4px;
            font-size: 13px;
        }

        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            font-size: 9px;
            text-align: center;
            color: #64748b;
        }

        .footer-info {
            margin-top: 5px;
            font-size: 8px;
        }

        .balance-summary {
            background-color: #f0f7ff;
            padding: 10px;
            border-radius: 4px;
            margin-top: 15px;
            border: 1px solid #bfdbfe;
        }

        .balance-info {
            font-weight: bold;
            color: #2563eb;
            font-size: 12px;
            margin-bottom: 5px;
        }

        .company-info {
            font-size: 12px;
            margin-bottom: 5px;
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

    <div class="title">BUKU KAS PEMAKAIAN BBM</div>

    <div class="summary-card">
        <div class="summary-title">RINGKASAN KAS BBM</div>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Periode Laporan</div>
                <div class="summary-value">: {{ $dateRange }}</div>
            </div>
            @if($selectedFuelType)
            <div class="summary-item">
                <div class="summary-label">Jenis BBM</div>
                <div class="summary-value">: {{ $selectedFuelType }}</div>
            </div>
            @endif
            <div class="summary-item">
                <div class="summary-label">Saldo Per Tanggal</div>
                <div class="summary-value">: Rp {{ number_format($initialBalance, 0, ',', '.') }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Pengisian Kembali</div>
                <div class="summary-value">: Rp {{ number_format($totalDeposits, 0, ',', '.') }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Kas</div>
                <div class="summary-value">: Rp {{ number_format($totalCash, 0, ',', '.') }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Pemakaian</div>
                <div class="summary-value">: Rp {{ number_format($totalUsage, 0, ',', '.') }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Saldo Akhir</div>
                <div class="summary-value">: Rp {{ number_format($finalBalance, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    @foreach($cashBookData as $fuelType => $data)
        <div class="fuel-type-header">{{ $fuelType }}</div>
        <table class="transactions-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 12%;">Tanggal</th>
                    <th style="width: 15%;">No. Transaksi</th>
                    <th style="width: 28%;">Keterangan</th>
                    <th style="width: 13%;">Debit</th>
                    <th style="width: 13%;">Kredit</th>
                    <th style="width: 14%;">Saldo</th>
                </tr>
            </thead>
            <tbody>
                @php $balance = $data['initial_balance']; $no = 1; @endphp
                <tr>
                    <td>{{ $no++ }}</td>
                    <td>{{ $startDate }}</td>
                    <td>-</td>
                    <td>Saldo Awal</td>
                    <td class="amount positive">{{ number_format($data['initial_balance'], 0, ',', '.') }}</td>
                    <td class="amount">-</td>
                    <td class="amount">{{ number_format($balance, 0, ',', '.') }}</td>
                </tr>
                @foreach($data['transactions'] as $transaction)
                    <tr>
                        <td>{{ $no++ }}</td>
                        <td>{{ $transaction['date'] }}</td>
                        <td>{{ $transaction['number'] }}</td>
                        <td>{{ $transaction['description'] }}</td>
                        <td class="amount positive">{{ $transaction['debit'] ? number_format($transaction['debit'], 0, ',', '.') : '-' }}</td>
                        <td class="amount negative">{{ $transaction['credit'] ? number_format($transaction['credit'], 0, ',', '.') : '-' }}</td>
                        @php
                            $balance += ($transaction['debit'] ?? 0) - ($transaction['credit'] ?? 0);
                        @endphp
                        <td class="amount">{{ number_format($balance, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="balance-summary">
            <div class="balance-info">Saldo Akhir {{ $fuelType }}: Rp {{ number_format($balance, 0, ',', '.') }}</div>
        </div>
    @endforeach

    <div class="footer">
        <p>Dokumen ini dihasilkan secara otomatis oleh sistem FuelCash App &copy; {{ date('Y') }}</p>
        <div class="footer-info">
            ID Laporan: RPT-{{ now()->format('YmdHis') }} |
            Diunduh oleh: {{ auth()->user()->name ?? 'System' }} |
            Tanggal: {{ \Carbon\Carbon::now()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s') }} WIB
        </div>
    </div>
</body>
</html>
