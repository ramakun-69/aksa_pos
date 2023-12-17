<?php

namespace  App\Modules\Statistik\Controllers;
/*
PT ITSHOP BISNIS DIGITAL
Website: https://itshop.biz.id
Toko Online: ITSHOP Purwokerto (https://Tokopedia.com/itshoppwt, https://Shopee.co.id/itshoppwt, https://Bukalapak.com/itshoppwt)
Dibuat oleh: Hari Wicaksono, S.Kom
06-2022
*/

use App\Controllers\BaseController;
use App\Modules\Statistik\Models\StatistikModel;

class Statistik extends BaseController
{
    protected $statistik;

    public function __construct()
    {
        //memanggil Model
        $this->statistik = new StatistikModel();
        helper('tglindo');
    }


    public function index()
    {
        $cari = $this->request->getVar('cari')??date('Y-m-d');
        if (!empty($cari)) :
            $data['cari'] = $cari;
        endif;

        $data['title'] = lang('App.statistic');
        $data['countTrxHariini'] = $this->statistik->countTrxHariini($cari);
        $data['countTrxHarikemarin'] = $this->statistik->countTrxHarikemarin();
        $data['totalTrxHariini'] = $this->statistik->totalTrxHariini($cari);
        $data['totalTrxHarikemarin'] = $this->statistik->totalTrxHarikemarin();
        $data['sisaPiutangHariini'] = $this->statistik->sisaPiutangHariini($cari);
        $data['barangTerlaris'] = $this->statistik->barangTerlaris();
        $data['jmlBarang'] = $this->statistik->getcountBarang();
        $data['jmlKontak'] = $this->statistik->getCountKontak();
        $data['jmlUser'] = $this->statistik->getCountUser();

        $bln = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
        $data['transaksi'] = [];
        foreach ($bln as $b) {
            $date = date('Y', strtotime($cari)) . '-' . $b;
            $data['transaksi'][] = $this->statistik->chartTransaksi($date);
        }

        $jam = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '00'];
        $data['jam'] = [];
        foreach ($jam as $j) {
            $date = $cari . ' ' . $j;
            $data['harian'][] = $this->statistik->chartHarian($date);
        }

        $tgl = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31'];
        $data['tgl'] = [];
        foreach ($tgl as $t) {
            $date = date('Y-m', strtotime($cari)) . '-' . $t;
            $data['pemasukan'][] = ($this->statistik->chartPemasukan($date) - $this->statistik->chartSisaPiutang($date));
        }

        return view('App\Modules\Statistik\Views/statistik', $data);
    }
}
