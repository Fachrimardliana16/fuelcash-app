<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 30px; }
        .logo { margin-bottom: 10px; }
        .company-name { font-size: 20px; font-weight: bold; margin-bottom: 5px; }
        .address { margin-bottom: 5px; }
        .contact { margin-bottom: 20px; }
        .title { font-size: 18px; font-weight: bold; margin: 20px 0; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .summary { margin: 20px 0; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
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

    <div class="title">LAPORAN SALDO</div>
    <div class="summary">
        <table>
            <tr>
                <td style="width: 200px;">Total Deposit</td>
                <td>: Rp {{ number_format($total_deposit, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Saldo Saat Ini</td>
                <td>: Rp {{ number_format($current_balance, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Jumlah Deposit</th>
                <th>Sisa Saldo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($balances as $index => $balance)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ date('d/m/Y', strtotime($balance->date)) }}</td>
                <td class="text-right">Rp {{ number_format($balance->deposit_amount, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($balance->remaining_balance, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
