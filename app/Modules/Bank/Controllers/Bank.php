<?php

namespace App\Modules\Bank\Controllers;
/*
PT ITSHOP BISNIS DIGITAL
Website: https://itshop.biz.id
Toko Online: ITSHOP Purwokerto (https://Tokopedia.com/itshoppwt, https://Shopee.co.id/itshoppwt, https://Bukalapak.com/itshoppwt)
Dibuat oleh: Hari Wicaksono, S.Kom
03-2023
*/

use App\Controllers\BaseController;
use App\Modules\Bank\Models\BankAkunModel;
use App\Modules\Bank\Models\BankModel;
use App\Modules\Toko\Models\TokoModel;
use CodeIgniter\I18n\Time;

class Bank extends BaseController
{
    protected $bank;
    protected $bankAkun;
    protected $toko;

    public function __construct()
    {
        //memanggil function di model
        $this->bank = new BankModel();
        $this->bankAkun = new BankAkunModel();
        $this->toko = new TokoModel();
    }

    public function index()
    {
        $toko = $this->toko->first();
        $bankUtama = $toko['id_bank_akun'];
        $bankAkun = $this->bankAkun->find($bankUtama);
        return view('App\Modules\Bank\Views/bank', [
            'title' => 'Bank',
            'idBankUtama' => $bankUtama,
            'bankAkun' => $bankAkun,
            'hariini' => date('Y-m-d', strtotime(Time::now())),
			'tujuhHari' => date('Y-m-d', strtotime('-1 week', strtotime(Time::now()))),
			'awalBulan' => date('Y-m-', strtotime(Time::now())) . '01',
            'akhirBulan' => date('Y-m-t', strtotime(Time::now())),
			'awalTahun' => date('Y-', strtotime(Time::now())) . '01-01',
            'akhirTahun' => date('Y-', strtotime(Time::now())) . '12-31',
            'awalTahunLalu' => date('Y-', strtotime('-1 year', strtotime(Time::now()))) . '01-01',
            'akhirTahunLalu' => date('Y-', strtotime('-1 year', strtotime(Time::now()))) . '12-31',
        ]);
    }

    
}
