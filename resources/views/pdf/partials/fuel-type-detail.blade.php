<div class="detail-section">
    <table class="detail-table">
        <tr>
            <td class="label">Saldo Awal</td>
            <td class="value">Rp {{ number_format($summary['initial_balance'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Penambahan</td>
            <td class="value">Rp {{ number_format($summary['deposit'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Jumlah</td>
            <td class="value">Rp {{ number_format($summary['total_amount'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Pemakaian</td>
            <td class="value">Rp {{ number_format($summary['usage'], 0, ',', '.') }}</td>
        </tr>
        <tr class="total">
            <td class="label">Saldo Sekarang</td>
            <td class="value">Rp {{ number_format($summary['current_balance'], 0, ',', '.') }}</td>
        </tr>
    </table>
</div>

<style>
.detail-section {
    margin: 0;
    padding: 8px;
}

.detail-table {
    width: 100%;
    border-collapse: collapse;
}

.detail-table td {
    padding: 4px 8px;
    line-height: 1.2;
    border: 1px solid #e5e7eb;
    font-size: 10px;
}

.detail-table .label {
    width: 120px;
    color: #1e40af;
}

.detail-table .value {
    text-align: right;
}

.detail-table .total {
    font-weight: bold;
}

.detail-table .total td {
    border-top: 2px solid #e5e7eb;
    background-color: #f0f7ff;
    color: #2563eb;
}
</style>
