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
            text-align: right;
            float: right;
            width: 50%;
        }

        .letter-body {
            margin: 10px 0;
            /* Reduced margin */
            text-align: justify;
        }

        .letter-body p {
            margin: 7px 0;
            /* Reduced margin */
            line-height: 1.4;
            /* Reduced line height */
        }

        .amount {
            font-weight: bold;
        }

        .signature-section {
            margin-top: 15px;
            /* Reduced margin */
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
            margin: 10px 0;
            /* Reduced margin */
            font-size: 10pt;
            /* Smaller font */
        }

        .stamp-placeholder {
            position: absolute;
            width: 70px;
            /* Reduced size */
            height: 70px;
            /* Reduced size */
            left: 50%;
            margin-left: -35px;
            top: -20px;
            /* Adjusted position */
            opacity: 0.3;
            transform: rotate(-15deg);
            font-size: 9px;
            /* Smaller font */
            text-align: center;
            border: 1px dashed #999;
            /* Thinner border */
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .signature-name {
            font-weight: bold;
            padding-top: 3px;
            /* Reduced padding */
            border-top: 1px solid #000;
            display: inline-block;
            min-width: 150px;
            /* Reduced width */
            font-size: 10pt;
            /* Smaller font */
        }

        .signature-nip {
            font-size: 9pt;
            /* Smaller font */
            margin-top: 0;
        }

        .letter-footer {
            margin-top: 15px;
            /* Reduced margin */
            font-size: 8pt;
            /* Smaller font */
            color: #666;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 5px;
            /* Reduced padding */
        }

        .compact-text {
            margin: 0;
            padding: 0;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .signature-table td {
            width: 50%;
            vertical-align: top;
            padding: 0 10px;
            text-align: center;
            border: none;
        }

        .signature-bottom {
            margin-top: 20px;
            text-align: center;
        }

        .stamp-area {
            position: relative;
            height: 40px;
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
        <div class="recipient" style="width: 35%; margin-right: 0;">
            <p class="compact-text" style="text-align: left;">
                Kepada Yth.<br>
                Direktur Utama<br>
                <strong>Perumda Air Minum Tirta Perwira</strong><br>
                <strong>Tirta Perwira</strong><br>
                <strong>Kabupaten Purbalingga</strong><br>
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
                    <td>
                        <p class="signature-title" style="margin-bottom: 40px;">Diperiksa Oleh,<br>Kasubag Umum</p>
                        <p class="signature-name">Irawan Tridesi WH, S.ST</p>
                        <p class="signature-nip">NIP. 196705121990031002</p>
                    </td>
                    <td>
                        <p class="signature-title" style="margin-bottom: 60px;"><br>Bendahara BBM</p>
                        <p class="signature-name">Wahyuningtyas P, S.Sos</p>
                        <p class="signature-nip">NIP. 198503122010012022</p>
                    </td>
                </tr>
            </table>

            <div class="signature-bottom">
                <p class="signature-title">Menyetujui,<br>Kabag Umum<br>
                    Perumda Air Minum Tirta Perwira Kab. Purbalingga</p>
                <div class="stamp-area">
                    <div class="stamp-placeholder">Stempel</div>
                </div>
                <p class="signature-name">Endah Susilowati, S.H.</p>
                <p class="signature-nip">NIP. 197209151998032003</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="letter-footer">
            <p class="compact-text">Perumda Air Minum Tirta Perwira Kabupaten Purbalingga | Jl. Jenderal Sudirman No.
                23, Purbalingga, Jawa Tengah 53311</p>
        </div>
    </div>
</body>

</html>
