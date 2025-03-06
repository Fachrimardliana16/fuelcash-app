<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Transaksi BBM</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .table th {
            background-color: #f4f4f4;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Laporan Transaksi BBM</h2>
        <p>Periode: {{ $dateRange }}</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Plat Nomor</th>
                <th>Jenis Kendaraan</th>
                <th>Pemilik</th>
                <th>Jenis BBM</th>
                <th>Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->usage_date instanceof \Carbon\Carbon
                        ? $transaction->usage_date->format('d/m/Y')
                        : \Carbon\Carbon::parse($transaction->usage_date)->format('d/m/Y') }}
                    </td>
                    <td>{{ $transaction->vehicle->license_plate }}</td>
                    <td>{{ $transaction->vehicle->vehicleType->name }}</td>
                    <td>{{ $transaction->owner }}</td>
                    <td>{{ $transaction->fuelType->name }}</td>
                    <td>{{ number_format($transaction->amount, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" style="text-align: right"><strong>Total:</strong></td>
                <td><strong>{{ number_format($transactions->sum('amount'), 0, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
    </table>
</body>

</html>
