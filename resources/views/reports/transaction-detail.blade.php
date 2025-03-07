<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Detail Transaksi #{{ $transaction->id }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .company-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #2563eb;
        }

        .company-name {
            font-size: 22px;
            font-weight: bold;
            color: #2563eb;
            margin: 0;
        }

        .company-address {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }

        .document-title {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
            padding: 10px;
            background-color: #f3f4f6;
            border-radius: 5px;
            color: #1f2937;
        }

        .header {
            margin-bottom: 30px;
        }

        .transaction-meta {
            width: 100%;
            padding: 10px;
            background-color: #f9fafb;
            border-left: 4px solid #2563eb;
            border-radius: 4px;
        }

        .transaction-meta table {
            width: 100%;
        }

        .transaction-meta table td {
            padding: 5px;
        }

        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            padding: 8px 10px;
            background-color: #e5e7eb;
            border-left: 4px solid #2563eb;
            border-radius: 4px;
            color: #1f2937;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table.data-table {
            margin-bottom: 15px;
            border: 1px solid #e5e7eb;
        }

        table.data-table td {
            padding: 8px 10px;
            vertical-align: top;
            border-bottom: 1px solid #f3f4f6;
        }

        table.data-table tr:nth-child(even) {
            background-color: #fafafa;
        }

        .label {
            font-weight: bold;
            width: 35%;
            color: #4b5563;
        }

        .value {
            width: 65%;
        }

        .money-value {
            font-weight: bold;
            color: #059669;
        }

        .description-box {
            background-color: #f9fafb;
            padding: 10px;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            margin-top: 5px;
        }

        .receipt-container {
            text-align: center;
            margin: 15px 0;
            padding: 10px;
            background-color: #f9fafb;
            border: 1px dashed #d1d5db;
            border-radius: 4px;
        }

        .receipt-image {
            max-width: 90%;
            max-height: 250px;
            margin: 0 auto;
            border: 1px solid #e5e7eb;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .footer {
            margin-top: 40px;
            padding-top: 15px;
            font-size: 10px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            page-break-inside: avoid;
        }

        .footer-info {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed #e5e7eb;
            text-align: center;
        }

        .footer-info span {
            margin: 0 10px;
        }

        .watermark {
            position: fixed;
            top: 50%;
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 100px;
            color: rgba(200, 200, 200, 0.1);
            transform: rotate(-45deg);
            z-index: -1;
        }
    </style>
</head>

<body>
    <div class="watermark">FuelCash App</div>

    <div class="company-header">
        <h1 class="company-name">FUEL CASH APP</h1>
        <div class="company-address">
            Jl. Perusahaan No. 123, Kota Jakarta<br>
            Telp: (021) 1234-5678 | Email: info@fuelcash.app
        </div>
    </div>

    <div class="document-title">LAPORAN DETAIL TRANSAKSI #{{ $transaction->id }}</div>

    <div class="header">
        <div class="transaction-meta">
            <table>
                <tr>
                    <td class="label" style="width:25%">No. Transaksi:</td>
                    <td class="value" style="width:25%"><strong>#{{ $transaction->id }}</strong></td>
                    <td class="label" style="width:25%">Tanggal Transaksi:</td>
                    <td class="value" style="width:25%">
                        {{ $transaction->usage_date ? \Carbon\Carbon::parse($transaction->usage_date)->setTimezone('Asia/Jakarta')->format('d F Y') : '-' }}
                    </td>
                </tr>
                <tr>
                    <td class="label">Dibuat:</td>
                    <td class="value" colspan="3">
                        {{ $transaction->created_at ? \Carbon\Carbon::parse($transaction->created_at)->setTimezone('Asia/Jakarta')->format('d F Y H:i') : '-' }}
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Data Kendaraan</div>
        <table class="data-table">
            <tr>
                <td class="label">Plat Nomor</td>
                <td class="value">{{ $transaction->vehicle->license_plate ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Jenis Kendaraan</td>
                <td class="value">{{ $transaction->vehicle->vehicleType->name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Pemilik</td>
                <td class="value">{{ $transaction->owner ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Data Penggunaan BBM</div>
        <table class="data-table">
            <tr>
                <td class="label">Tanggal Penggunaan</td>
                <td class="value">
                    {{ $transaction->usage_date ? \Carbon\Carbon::parse($transaction->usage_date)->setTimezone('Asia/Jakarta')->format('d F Y') : '-' }}
                </td>
            </tr>
            <tr>
                <td class="label">Jenis BBM</td>
                <td class="value">{{ $transaction->fuelType->name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">BBM</td>
                <td class="value">{{ $transaction->fuel->name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Jumlah</td>
                <td class="value money-value">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label">Sisa Saldo</td>
                <td class="value money-value">Rp
                    {{ number_format($transaction->balance->remaining_balance ?? 0, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Keterangan Penggunaan</div>
        <div class="description-box">
            {{ $transaction->usage_description ?? 'Tidak ada keterangan' }}
        </div>
    </div>

    @if ($fuelReceiptBase64)
        <div class="section">
            <div class="section-title">Struk BBM</div>
            <div class="receipt-container">
                <img src="{{ $fuelReceiptBase64 }}" alt="Struk BBM" class="receipt-image">
            </div>
        </div>
    @endif

    @if ($invoiceBase64)
        <div class="section">
            <div class="section-title">Nota/Kwitansi</div>
            <div class="receipt-container">
                <img src="{{ $invoiceBase64 }}" alt="Nota/Kwitansi" class="receipt-image">
            </div>
        </div>
    @endif

    <div class="footer">
        <p>Dokumen ini dihasilkan secara otomatis oleh sistem FuelCash App &copy; {{ date('Y') }}</p>

        <div class="footer-info">
            <span>ID: {{ $transaction->id }}</span>
            <span>Diunduh oleh: {{ auth()->user()->name ?? 'User' }}</span>
            <span>Tanggal Unduh: {{ \Carbon\Carbon::now()->setTimezone('Asia/Jakarta')->format('d F Y H:i:s') }}</span>
        </div>
    </div>
</body>

</html>
