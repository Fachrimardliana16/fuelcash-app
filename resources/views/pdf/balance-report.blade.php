<!DOCTYPE html>
<html>

<head>
    <title>Balance Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
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

        .summary {
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Balance Report</h2>
        <p>Date: {{ $balance->date->format('d M Y') }}</p>
    </div>

    <div class="summary">
        <h3>Balance Summary</h3>
        <p>Deposit Amount: Rp {{ number_format($balance->deposit_amount, 0, ',', '.') }}</p>
        <p>Remaining Balance: Rp {{ number_format($balance->remaining_balance, 0, ',', '.') }}</p>
    </div>

    @if ($transactions->count() > 0)
        <div class="transactions">
            <h3>Related Transactions</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->date->format('d M Y') }}</td>
                            <td>Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                            <td>{{ $transaction->description }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</body>

</html>
