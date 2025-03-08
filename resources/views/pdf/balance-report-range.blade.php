<!DOCTYPE html>
<html>

<head>
    <title>Balance Report</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #fff;
            line-height: 1.6;
        }

        .container {
            width: 100%;
            padding: 30px 40px;
        }

        .letterhead {
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 15px;
            margin-bottom: 30px;
            position: relative;
        }

        .letterhead-content {
            display: flex;
            justify-content: space-between;
        }

        .company-info {
            text-align: right;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin: 0;
        }

        .company-details {
            font-size: 12px;
            color: #7f8c8d;
        }

        .document-title {
            text-align: center;
            margin: 30px 0;
        }

        .document-title h2 {
            font-size: 22px;
            color: #2c3e50;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .document-title p {
            font-size: 14px;
            margin: 10px 0 0 0;
        }

        .reference-number {
            font-size: 14px;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
            font-size: 14px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #f6f8fa;
            color: #2c3e50;
            font-weight: 600;
        }

        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .total {
            font-weight: bold;
            background-color: #edf2f7;
        }

        .amount {
            text-align: right;
        }

        .footer {
            margin-top: 40px;
            font-size: 14px;
        }

        .signature-section {
            margin-top: 60px;
        }

        .signature-container {
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            width: 45%;
        }

        .signature-line {
            border-top: 1px solid #333;
            margin-top: 60px;
            padding-top: 5px;
            font-weight: bold;
        }

        .report-date {
            font-size: 12px;
            color: #7f8c8d;
            text-align: right;
            margin-top: 40px;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Letterhead -->
        <div class="letterhead">
            <div class="letterhead-content">
                <div class="logo">
                    <!-- Logo placeholder - replace with your company logo -->
                    <div style="width: 150px; height: 50px; font-weight: bold; font-size: 20px;">FuelCash</div>
                </div>
                <div class="company-info">
                    <p class="company-name">FuelCash Management</p>
                    <p class="company-details">
                        123 Business Avenue, Suite 101<br>
                        Jakarta, Indonesia 12345<br>
                        Phone: +62 21 1234 5678<br>
                        Email: info@fuelcash.com
                    </p>
                </div>
            </div>
        </div>

        <!-- Reference Number -->
        <div class="reference-number">
            <strong>Ref:</strong> BAL/{{ now()->format('Ymd') }}/{{ rand(1000, 9999) }}
        </div>

        <!-- Document Title -->
        <div class="document-title">
            <h2>Balance Report Statement</h2>
            <p>Period: {{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }} -
                {{ \Carbon\Carbon::parse($untilDate)->format('d M Y') }}</p>
        </div>

        <!-- Introduction Text -->
        <p>Dear Valued Stakeholder,</p>
        <p>
            Please find below the detailed balance report for the specified period. This report outlines
            all deposit amounts, transaction counts, and remaining balances during this timeframe.
        </p>

        <!-- Table Content -->
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
                        <td class="amount">Rp {{ number_format($balance->deposit_amount, 0, ',', '.') }}</td>
                        <td class="amount">Rp {{ number_format($balance->remaining_balance, 0, ',', '.') }}</td>
                        <td>{{ $balance->transactions->count() }}</td>
                        <td class="amount">Rp {{ number_format($transactionTotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr class="total">
                    <td>Total</td>
                    <td class="amount">Rp {{ number_format($totalDeposit, 0, ',', '.') }}</td>
                    <td>-</td>
                    <td>{{ $balances->sum(function ($balance) {return $balance->transactions->count();}) }}</td>
                    <td class="amount">Rp {{ number_format($totalTransactions, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Conclusion Text -->
        <p>
            This report provides a comprehensive overview of financial activities during the specified period.
            If you have any questions or require further clarification regarding this statement, please do not
            hesitate to contact our finance department.
        </p>

        <p>Thank you for your continued trust.</p>

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-container">
                <div class="signature-box">
                    <div class="signature-line">Finance Manager</div>
                </div>
                <div class="signature-box">
                    <div class="signature-line">Accounting Director</div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p class="report-date">Report generated on: {{ now()->format('d M Y H:i:s') }}</p>
        </div>
    </div>
</body>

</html>
