<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
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
        .letterhead-img {
            width: 100%;
            max-height: 150px;
            object-fit: contain;
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
            padding-right: 15%;  /* Add padding to offset the logo space */
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
            <div class="company-name">{{ $company->company_type }} {{ $company->company_name }}</div>
            <div class="regency-name">{{ strtoupper($company->regency) }}</div>
            <div class="company-address">
                {{ $company->street_address }} Telp. {{ $company->phone_number }}
            </div>
        </div>
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
