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
            width: 50px;
        }

        .vehicle-col {
            width: 100px;
        }

        .fueltype-col {
            width: 60px;
        }

        .amount-col {
            width: 70px;
        }

        .balance-col {
            width: 70px;
        }

        .description-col {
            width: 180px;
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
            width: 55px;
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
                <th class="volume-col">Volume (L)</th>
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
                <td colspan="5" class="text-right"><strong>Total:</strong></td>
                <td class="text-right"><strong>{{ number_format($transactions->sum('amount'), 0, ',', '.') }}</strong></td>
                <td class="text-right"><strong>{{ number_format($transactions->sum('volume'), 2, ',', '.') }}</strong></td>
                <td class="text-right"><strong>{{ number_format($initialBalance, 0, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
    </table>
</body>

</html>
