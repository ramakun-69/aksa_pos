<?php

namespace  App\Modules\Dashboard\Controllers;
/*
PT ITSHOP BISNIS DIGITAL
Website: https://itshop.biz.id
Toko Online: ITSHOP Purwokerto (https://Tokopedia.com/itshoppwt, https://Shopee.co.id/itshoppwt, https://Bukalapak.com/itshoppwt)
Dibuat oleh: Hari Wicaksono, S.Kom
06-2022
*/

use App\Controllers\BaseController;
use App\Modules\Dashboard\Models\DashboardModel;

class Dashboard extends BaseController
{
    protected $dashboard;

	public function __construct()
	{
        //memanggil Model
        $this->dashboard = new DashboardModel();
	}

	public function index()
	{
        $data['title'] = 'Dashboard';
        $data['countTrxHariini'] = $this->dashboard->countTrxHariini();
        $data['countTrxHarikemarin'] = $this->dashboard->countTrxHarikemarin();
        $data['totalTrxHariini'] = $this->dashboard->totalTrxHariini();
		$data['totalTrxHarikemarin'] = $this->dashboard->totalTrxHarikemarin();
        $data['jmlBarang'] = $this->dashboard->getcountBarang();
        $data['jmlKontak'] = $this->dashboard->getCountKontak();
        $data['jmlUser'] = $this->dashboard->getCountUser();
        $data['kasMasuk'] = $this->dashboard->kasMasukHariini();
        $data['kasKeluar'] = $this->dashboard->kasKeluarHariini();
        $data['bankMasuk'] = $this->dashboard->bankMasukHariini();
        $data['bankKeluar'] = $this->dashboard->bankKeluarHariini();
        $data['sisaHutang'] = $this->dashboard->sisaHutang();
        $data['sisaPiutang'] = $this->dashboard->sisaPiutang();
        $data['sisaPiutangHariini'] = $this->dashboard->sisaPiutangHariini();
        $data['sisaPiutangHarikemarin'] = $this->dashboard->sisaPiutangHarikemarin();
        $data['hutangAkanTempo'] = $this->dashboard->hutangAkanTempo();
        $data['hutangTempoHariini'] = $this->dashboard->hutangTempoHariini();
        $data['hutangLewatTempo'] = $this->dashboard->hutangLewatTempo();
        $data['piutangAkanTempo'] = $this->dashboard->piutangAkanTempo();
        $data['piutangTempoHariini'] = $this->dashboard->piutangTempoHariini();
        $data['piutangLewatTempo'] = $this->dashboard->piutangLewatTempo();

        /* var_dump($this->dashboard->getLastQuery()->getQuery());
        die; */

		$bln = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
        $data['transaksi'] = [];
        foreach ($bln as $b) {
            $date = date('Y-') . $b;
            $data['transaksi'][] = $this->dashboard->chartTransaksi($date);
        }

        $jam = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '00'];
        $data['jam'] = [];
        foreach ($jam as $j) {
            $date = date('Y-m-d') . ' ' . $j;
            $data['harian'][] = $this->dashboard->chartHarian($date);
        }

        $tgl = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31'];
        $data['tgl'] = [];
        foreach ($tgl as $t) {
            $date = date('Y-m-') . $t;
            $data['pemasukan'][] = $this->dashboard->chartPemasukan($date);
        }

        // User Agent Class
		$agent = $this->request->getUserAgent();
		if ($agent->isMobile()) {
			return view('App\Modules\Dashboard\Views/index_mobile', $data);
		} else {
			return view('App\Modules\Dashboard\Views/index', $data);
		}
	}

    public function blocked()
	{
        return view('App\Modules\Dashboard\Views/blocked', [
            'title' => 'Access Denied'
        ]);
    }

}
