<!DOCTYPE html>
<html>

<head>
    <title>Balance Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .total {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Balance Report</h2>
        <p>Period: {{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }} -
            {{ \Carbon\Carbon::parse($untilDate)->format('d M Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Deposit Amount</th>
                <th>Remaining Balance</th>
                <th>Transaction Count</th>
                <th>Total Transactions</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalDeposit = 0;
                $totalTransactions = 0;
            @endphp
            @foreach ($balances as $balance)
                @php
                    $totalDeposit += $balance->deposit_amount;
                    $transactionTotal = $balance->transactions->sum('amount');
                    $totalTransactions += $transactionTotal;
                @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($balance->date)->format('d M Y') }}</td>
                    <td>Rp {{ number_format($balance->deposit_amount, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($balance->remaining_balance, 0, ',', '.') }}</td>
                    <td>{{ $balance->transactions->count() }}</td>
                    <td>Rp {{ number_format($transactionTotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="total">
                <td>Total</td>
                <td>Rp {{ number_format($totalDeposit, 0, ',', '.') }}</td>
                <td>-</td>
                <td>{{ $balances->sum(function ($balance) {return $balance->transactions->count();}) }}</td>
                <td>Rp {{ number_format($totalTransactions, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 30px">
        <p>Report generated on: {{ now()->format('d M Y H:i:s') }}</p>
    </div>
</body>

</html>
