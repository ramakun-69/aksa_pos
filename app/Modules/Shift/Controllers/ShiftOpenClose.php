<?php

namespace App\Modules\Shift\Controllers;
/*
PT ITSHOP BISNIS DIGITAL
Website: https://itshop.biz.id
Toko Online: ITSHOP Purwokerto (https://Tokopedia.com/itshoppwt, https://Shopee.co.id/itshoppwt, https://Bukalapak.com/itshoppwt)
Dibuat oleh: Hari Wicaksono, S.Kom
02-2023
*/

use App\Controllers\BaseController;
use App\Modules\Shift\Models\ShiftOpenCloseModel;
use App\Modules\User\Models\UserModel;
use App\Modules\Shift\Models\LaporanOpenCloseModel;
use CodeIgniter\I18n\Time;

class ShiftOpenClose extends BaseController
{
    protected $opencloseCashier;
    protected $user;
    protected $laporan;

    public function __construct()
    {
        //memanggil function di model
        $this->opencloseCashier = new ShiftOpenCloseModel();
        $this->user = new UserModel();
        $this->laporan = new LaporanOpenCloseModel();
    }

    public function index()
    {
        $cari = $this->request->getVar('search');
        return view('App\Modules\Shift\Views/openclose', [
            'title' => 'Open Close Cashier',
            'search' => $cari,
            'hariini' => date('Y-m-d', strtotime(Time::now())),
            'tujuhHari' => date('Y-m-d', strtotime('-1 week', strtotime(Time::now()))),
            'awalBulan' => date('Y-m-', strtotime(Time::now())) . '01',
            'akhirBulan' => date('Y-m-t', strtotime(Time::now())),
            'awalTahun' => date('Y-', strtotime(Time::now())) . '01-01',
            'akhirTahun' => date('Y-', strtotime(Time::now())) . '12-31',
            'awalTahunLalu' => date('Y-', strtotime('-1 year', strtotime(Time::now()))) . '01-01',
            'akhirTahunLalu' => date('Y-', strtotime('-1 year', strtotime(Time::now()))) . '12-31',
            'jam' => date('H:i', strtotime(Time::now())),
        ]);
    }

    public function printReportHtml()
    {
        $input = $this->request->getVar();
        $date = $input['date'];
        $user = $input['user'];

        $dataOpen = $this->opencloseCashier->where(['type' => 'open', 'id_login' => $user, 'tanggal' => $date])->first();
        $dataClose = $this->opencloseCashier->where(['type' => 'close', 'id_login' => $user, 'tanggal' => $date])->first();

        $data = [
            'user' => $this->user->find($user),
            'data_open' => $dataOpen,
            'data_close' => $dataClose,
            'trx_selesai' => $this->laporan->getCountTrxSelesai($user, $date),
            'trx_belum_selesai' => $this->laporan->getCountTrxBelumSelesai($user, $date),
            'total_cash' => $this->laporan->totalCash($user, $date),
            'total_credit' => $this->laporan->totalCredit($user, $date),
            'total_bank' => $this->laporan->totalBank($user, $date),
        ];

        return view('App\Modules\Shift\Views/laporan_openclose_html', $data);
    }
}
