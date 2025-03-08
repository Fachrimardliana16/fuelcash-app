<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 20px;
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
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            page-break-inside: auto;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 3px 4px;
            text-align: left;
            font-size: 10px;
            vertical-align: top;
        }

        th {
            background-color: #f2f2f2;
        }

        .text-right {
            text-align: right;
            white-space: nowrap;
        }

        .summary {
            margin: 20px 0;
        }

        .description {
            max-width: none;
            word-wrap: break-word;
            padding-right: 10px;
        }

        .date-col {
            width: 60px;
        }

        .vehicle-col {
            width: 110px;
        }

        .fueltype-col {
            width: 70px;
        }

        .amount-col {
            width: 65px;
        }

        .balance-col {
            width: 70px;
        }

        .description-col {
            width: 220px;
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
    </style>
</head>

<body>
    <div class="header">
        <img src="{{ storage_path('app/public/kop_surat.png') }}" class="letterhead-img">
    </div>

    <div class="title">LAPORAN TRANSAKSI BBM</div>
    <div class="summary">
        <table>
            <tr>
                <td style="width: 200px;">Periode</td>
                <td>: {{ $dateRange }}</td>
            </tr>
            <tr>
                <td>Saldo Awal</td>
                <td>: Rp {{ number_format($initialBalance + $transactions->sum('amount'), 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Total Transaksi</td>
                <td>: Rp {{ number_format($transactions->sum('amount'), 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Sisa Saldo</td>
                <td>: Rp {{ number_format($initialBalance, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th class="date-col">Tanggal</th>
                <th class="vehicle-col">Kendaraan</th>
                <th class="fueltype-col">Jenis BBM</th>
                <th class="description-col">Uraian</th>
                <th class="amount-col">Jumlah (Rp)</th>
                <th class="balance-col">Sisa Saldo</th>
            </tr>
        </thead>
        <tbody>
            @php
                $remainingBalance = $initialBalance + $transactions->sum('amount');
            @endphp
            @foreach ($transactions as $index => $transaction)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $transaction->usage_date instanceof \Carbon\Carbon
                        ? $transaction->usage_date->format('d/m/Y')
                        : \Carbon\Carbon::parse($transaction->usage_date)->format('d/m/Y') }}
                    </td>
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
                    @php
                        $remainingBalance -= $transaction->amount;
                    @endphp
                    <td class="text-right">{{ number_format($remainingBalance, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-right"><strong>Total:</strong></td>
                <td class="text-right"><strong>{{ number_format($transactions->sum('amount'), 0, ',', '.') }}</strong>
                </td>
                <td class="text-right"><strong>{{ number_format($initialBalance, 0, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
    </table>
</body>

</html>
