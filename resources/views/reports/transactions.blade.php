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
        .header { text-align: center; margin-bottom: 30px; }
        .company-name { font-size: 20px; font-weight: bold; margin-bottom: 5px; }
        .address { margin-bottom: 5px; }
        .contact { margin-bottom: 20px; }
        .title { font-size: 18px; font-weight: bold; margin: 20px 0; text-align: center; }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px;
            page-break-inside: auto;
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 4px 6px; 
            text-align: left; 
            font-size: 10px; 
        }
        th { background-color: #f2f2f2; }
        .text-right { 
            text-align: right; 
            white-space: nowrap; 
        }
        .summary { margin: 20px 0; }
        .description { 
            max-width: none; 
            word-wrap: break-word;
            padding-right: 10px;
        }
        .date-col { width: 60px; }
        .plate-col { width: 70px; }
        .type-col { width: 60px; }
        .amount-col { width: 80px; }
        .balance-col { width: 85px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">PERUMDA AIR MINUM TIRTA PERWIRA</div>
        <div class="company-name">KABUPATEN PURBALINGGA</div>
        <div class="address">Jl. Letjend S. Parman No. 62 Purbalingga</div>
        <div class="contact">Telp. (0281) 891350 Fax. (0281) 891350</div>
        <hr style="border-top: 2px solid black; margin: 20px 0;">
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
                <th class="plate-col">Plat Nomor</th>
                <th style="width: 100px;">Pemilik</th>
                <th class="type-col">Jenis BBM</th>
                <th class="amount-col">Jumlah (Rp)</th>
                <th class="balance-col">Sisa Saldo</th>
                <th>Keterangan</th>
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
                    <td>{{ $transaction->vehicle->license_plate }}</td>
                    <td>{{ $transaction->owner }}</td>
                    <td>{{ $transaction->fuelType->name }}</td>
                    <td class="text-right">{{ number_format($transaction->amount, 0, ',', '.') }}</td>
                    @php
                        $remainingBalance -= $transaction->amount;
                    @endphp
                    <td class="text-right">{{ number_format($remainingBalance, 0, ',', '.') }}</td>
                    <td class="description">{{ $transaction->usage_description }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-right"><strong>Total:</strong></td>
                <td class="text-right"><strong>{{ number_format($transactions->sum('amount'), 0, ',', '.') }}</strong></td>
                <td class="text-right"><strong>{{ number_format($initialBalance, 0, ',', '.') }}</strong></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
