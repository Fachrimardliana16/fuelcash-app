<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 1.5cm 1.5cm;
            /* Reduced margins */
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 0;
            padding: 0;
            line-height: 1.4;
            /* Reduced line height */
            color: #333;
            background: #fff;
            font-size: 11pt;
            /* Slightly smaller base font size */
        }

        .container {
            position: relative;
            padding: 0 10px;
        }

        .letterhead {
            text-align: center;
            margin-bottom: 15px;
            /* Reduced margin */
            border-bottom: 2px double #000;
            padding-bottom: 5px;
            /* Reduced padding */
        }

        .letterhead-img {
            width: 100%;
            max-height: 100px;
            /* Reduced height */
            object-fit: contain;
        }

        .letter-metadata {
            margin: 10px 0;
            /* Reduced margin */
        }

        .letter-number {
            float: left;
            font-size: 10pt;
            /* Smaller font */
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        .letter-subject {
            text-align: center;
            margin: 15px 0;
            /* Reduced margin */
        }

        .letter-subject h2 {
            font-size: 14pt;
            /* Reduced font size */
            font-weight: bold;
            text-transform: uppercase;
            margin: 0;
            padding: 3px 15px;
            /* Reduced padding */
            display: inline-block;
            border-bottom: 1px solid #000;
        }

        .recipient {
            margin-bottom: 15px;
            line-height: 1.3;
            text-align: left;
            float: right;
            width: 70%;
            padding-left: 50%;
        }

        .letter-body {
            margin: 3px 0;
            text-align: justify;
        }

        .letter-body p {
            margin: 3px 0;
            line-height: 1.2;
        }

        .amount {
            font-weight: bold;
        }

        .signature-section {
            margin-top: 10px;
            page-break-inside: avoid;
        }

        .signature-container {
            width: 100%;
            margin-bottom: 10px;
            /* Reduced margin */
            position: relative;
            height: 80px;
            /* Reduced height */
        }

        .signature-box-left {
            position: absolute;
            left: 10px;
            /* Adjusted position */
            text-align: center;
            width: 180px;
        }

        .signature-box-right {
            position: absolute;
            right: 10px;
            /* Adjusted position */
            text-align: center;
            width: 180px;
        }

        .signature-box p {
            margin: 0;
            padding: 2px 0;
            /* Reduced padding */
            font-size: 10pt;
            /* Smaller font */
        }

        .signature-title {
            font-weight: bold;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            margin-bottom: 10px;
        }

        .signature-table td {
            width: 50%;
            vertical-align: top;
            padding: 0 5px;
            text-align: center;
            border: none;
        }

        .signature-name {
            font-weight: bold;
            padding-top: 3px;
            border-top: 1px solid #000;
            display: inline-block;
            min-width: 150px;
            font-size: 10pt;
            margin-top: 45px;
        }

        .signature-bottom {
            margin-top: 15px;
            text-align: center;
        }

        .signature-bottom .signature-name {
            margin-top: 45px;
        }

        .signature-title {
            font-weight: bold;
            margin-bottom: 60px;
            /* Adjusted margin */
        }

        .signature-box-bottom {
            text-align: center;
            margin-top: 30px;
            /* Reduced margin */
            position: relative;
        }

        .signature-space {
            height: 40px;
            /* Reduced height */
        }

        .date-right {
            text-align: right;
            margin: 5px 0;
            font-size: 10pt;
        }

        .stamp-placeholder {
            position: absolute;
            width: 50px;
            height: 50px;
            left: 50%;
            margin-left: -25px;
            top: -10px;
            opacity: 0.3;
            transform: rotate(-15deg);
            font-size: 9px;
            text-align: center;
            border: 1px dashed #999;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .signature-name {
            font-weight: bold;
            padding-top: 3px;
            border-top: 1px solid #000;
            display: inline-block;
            min-width: 200px;
            font-size: 10pt;
        }

        .signature-nip {
            font-size: 9pt;
            margin-top: 0;
        }

        .letter-footer {
            margin-top: 8px;
            font-size: 8pt;
            color: #666;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 2px;
        }

        .compact-text {
            margin: 0;
            padding: 0;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            /* Adjusted margin */
        }

        .signature-table td {
            width: 50%;
            vertical-align: top;
            padding: 0 20px;
            /* Adjusted padding */
            text-align: center;
            border: none;
        }

        .signature-bottom {
            margin-top: 40px;
            /* Adjusted margin */
            text-align: center;
        }

        .stamp-area {
            position: relative;
            height: 40px;
            margin-top: -5px;
        }

        .letter-number {
            float: left;
            font-size: 10pt;
            margin-bottom: 20px;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Recipient on right -->
        <div class="recipient" style="width: 45%; margin-right: 0; padding-left: 50%;">
            <p class="compact-text" style="text-align: left;">
                Kepada Yth.<br>
                Direktur Utama<br>
                <strong>{!! nl2br(e($company->company_name)) !!}</strong><br>
                di Tempat
            </p>
        </div>

        <!-- Clear float -->
        <div class="clearfix"></div>

        <!-- Letter subject -->
        <div class="letter-subject">
            <h2>SURAT PERMOHONAN</h2>
        </div>

        <div class="clearfix"></div>

        <!-- Letter body -->
        <div class="letter-body">
            <p class="compact-text">Dengan hormat,</p>

            <p class="compact-text">Yang bertanda tangan di bawah ini, selaku pengelola Dana Bahan Bakar Minyak Perumda
                Air Minum Tirta Perwira Kabupaten Purbalingga, dengan ini mengajukan permohonan pengisian kembali Dana
                Bahan Bakar dikarenakan saldo telah mendekati batas minimal.</p>

            <p class="compact-text">Adapun pengajuan dana yang kami mohon sebesar <span class="amount">Rp
                    {{ number_format($balance->deposit_amount, 0, ',', '.') }}</span> (<em>{{ $terbilang }}
                    rupiah</em>).</p>

            <p class="compact-text">Sebagai bahan pertimbangan, bersama ini kami lampirkan rekap penggunaan Dana Bahan
                Bakar periode sebelumnya.</p>

            <p class="compact-text">Demikian surat permohonan ini kami sampaikan. Atas perhatian dan persetujuan
                Bapak/Ibu, kami mengucapkan terima kasih.</p>
        </div>

        <!-- Signature section -->
        <div class="signature-section">
            <div class="date-right">
                Purbalingga, {{ \Carbon\Carbon::parse($balance->date)->isoFormat('D MMMM Y') }}
            </div>

            <!-- Table-based signature layout for better alignment -->
            <table class="signature-table">
                <tr>
                    @foreach($signatures->where('order', '<', 3) as $signature)
                    <td>
                        <p class="signature-title">{{ $signature->position }}</p>
                        <p class="signature-name">{{ $signature->name }}</p>
                        @if($signature->nip)
                        <p class="signature-nip">NIP. {{ $signature->nip }}</p>
                        @endif
                    </td>
                    @endforeach
                </tr>
            </table>

            <div class="signature-bottom">
                @if($bottomSignature = $signatures->where('order', '>=', 3)->first())
                <p class="signature-title">{{ $bottomSignature->title }},<br>{{ $bottomSignature->position }}<br>
                    {!! nl2br(e($company->company_name)) !!}</p>
                @if($bottomSignature->show_stamp)
                <div class="stamp-area">
                    <div class="stamp-placeholder">Stempel</div>
                </div>
                @endif
                <p class="signature-name">{{ $bottomSignature->name }}</p>
                @if($bottomSignature->nip)
                <p class="signature-nip">NIP. {{ $bottomSignature->nip }}</p>
                @endif
                @endif
            </div>
        </div>

        <!-- Footer -->
        <div class="letter-footer">
            <p class="compact-text">{!! nl2br(e($company->company_name)) !!} | {{ $company->street_address }},
                {{ $company->village }}, {{ $company->district }}, {{ $company->regency }}, {{ $company->province }} {{ $company->postal_code }}</p>
        </div>
    </div>
</body>

</html>
