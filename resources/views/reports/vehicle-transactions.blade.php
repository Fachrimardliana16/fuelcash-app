<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Transaksi Kendaraan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
        }

        h1 {
            font-size: 18px;
            text-align: center;
            margin-bottom: 20px;
        }

        h2 {
            font-size: 14px;
            margin-bottom: 10px;
        }

        .vehicle-info {
            margin-bottom: 20px;
        }

        .vehicle-info p {
            margin: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #777;
        }
    </style>
</head>

<body>
    <h1>Laporan Transaksi Kendaraan</h1>

    <div class="vehicle-info">
        <h2>Informasi Kendaraan</h2>
        <p><strong>Nomor Kendaraan:</strong> {{ $vehicle->license_plate }}</p>
        <p><strong>Jenis Kendaraan:</strong> {{ $vehicle->vehicleType->name }}</p>
        <p><strong>Pemilik:</strong> {{ $vehicle->owner }}</p>
        <p><strong>Status:</strong> {{ $vehicle->isactive ? 'Aktif' : 'Tidak Aktif' }}</p>
    </div>

    <div class="report-info">
        <p><strong>Periode:</strong> {{ $dateRange }}</p>
        <p><strong>Total Transaksi:</strong> {{ count($transactions) }}</p>
        <p><strong>Total Pengeluaran:</strong> Rp {{ number_format($totalAmount, 0, ',', '.') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Bahan Bakar</th>
                <th>Jumlah (Rp)</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transactions as $index => $transaction)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $transaction->usage_date->format('d/m/Y') }}</td>
                    <td>{{ $transaction->fuel->name ?? ($transaction->fuelType->name ?? 'N/A') }}</td>
                    <td class="text-right">{{ number_format($transaction->amount, 0, ',', '.') }}</td>
                    <td>{{ $transaction->usage_description }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="text-right">Total</th>
                <th class="text-right">{{ number_format($totalAmount, 0, ',', '.') }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Laporan ini dibuat otomatis pada {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>

</html>
