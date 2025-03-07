<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { 
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }
        .letterhead {
            text-align: center;
            margin-bottom: 30px;
        }
        .letter-body {
            margin: 20px 0;
        }
        .signature-section {
            margin-top: 50px;
        }
        .signature-container {
            width: 100%;
            margin-bottom: 30px;
            position: relative;
            height: 150px;
        }
        .signature-row {
            width: 100%;
            position: relative;
            height: 120px;
        }
        .signature-box-left {
            position: absolute;
            left: 50px;
            text-align: center;
            width: 200px;
        }
        .signature-box-right {
            position: absolute;
            right: 50px;
            text-align: center;
            width: 200px;
        }
        .signature-box p {
            margin: 0;
            padding: 3px 0;
        }
        .signature-box-bottom {
            text-align: center;
            margin-top: 70px;
        }
        .signature-space {
            height: 70px;
        }
        .final-signature {
            text-align: center;
            margin-top: 20px;
        }
        .date-right {
            text-align: right;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="letter-body">
        <p>Kepada Yth.<br>
        Direktur Utama<br>
        Perumda Air Minum Tirta Perwira Kab. Purbalingga</p>
        <br>
        <p>Dengan Hormat,</p>

        <p>Sehubungan dengan telah mendekati saldo minimal dari saldo Dana Bahan Bakar Minyak. Maka kami mengajukan pengisian kembali Dana Bahan Bakar Sebesar Rp {{ number_format($balance->deposit_amount, 0, ',', '.') }} ({{ $terbilang }} rupiah).</p>

        <p>Sebagai bahan pertimbangan kami lampirkan rekap pemakaiannya.</p>

        <p>Demikian untuk menjadikan periksa dan guna seperlunya, atas keputusannya kami sampaikan terimakasih.</p>

        <div class="date-right">
            Purbalingga, {{ \Carbon\Carbon::parse($balance->date)->isoFormat('D MMMM Y') }}
        </div>
            
            <div class="signature-container">
                <div class="signature-box-left">
                    <p>Diperiksa Oleh<br>Kasubag Umum</p>
                    <div class="signature-space"></div>
                    <p>(Irawan Tridesi WH, S.ST)</p>
                </div>
                
                <div class="signature-box-right">
                    <p> <br>Bendahara BBM</p>
                    <div class="signature-space"></div>
                    <p>(Wahyuningtyas P, S.Sos)</p>
                </div>
            </div>

            <div class="signature-box-bottom">
                <p>Menyetujui<br>
                Kabag Umum<br>
                Perumda Air Minum Tirta Perwira Kab. Purbalingga</p>
                <div class="signature-space"></div>
                <p>(Endah Susilowati, S.H.)</u></p>
            </div>
        </div>
    </div>
</body>
</html>
