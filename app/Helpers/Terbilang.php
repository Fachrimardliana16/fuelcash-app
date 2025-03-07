<?php

namespace App\Helpers;

class Terbilang
{
    private $angka = array('', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas');

    public function convert($nilai)
    {
        if ($nilai < 12)
            return $this->angka[$nilai];
        elseif ($nilai < 20)
            return $this->angka[$nilai - 10] . ' belas';
        elseif ($nilai < 100)
            return $this->angka[$nilai / 10] . ' puluh ' . $this->angka[$nilai % 10];
        elseif ($nilai < 200)
            return 'seratus ' . $this->convert($nilai - 100);
        elseif ($nilai < 1000)
            return $this->angka[$nilai / 100] . ' ratus ' . $this->convert($nilai % 100);
        elseif ($nilai < 2000)
            return 'seribu ' . $this->convert($nilai - 1000);
        elseif ($nilai < 1000000)
            return $this->convert($nilai / 1000) . ' ribu ' . $this->convert($nilai % 1000);
        elseif ($nilai < 1000000000)
            return $this->convert($nilai / 1000000) . ' juta ' . $this->convert($nilai % 1000000);
        elseif ($nilai < 1000000000000)
            return $this->convert($nilai / 1000000000) . ' milyar ' . $this->convert($nilai % 1000000000);
    }
}
