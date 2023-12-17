<?php

namespace App\Modules\Pajak\Controllers;
/*
PT ITSHOP BISNIS DIGITAL
Website: https://itshop.biz.id
Toko Online: ITSHOP Purwokerto (https://Tokopedia.com/itshoppwt, https://Shopee.co.id/itshoppwt, https://Bukalapak.com/itshoppwt)
Dibuat oleh: Hari Wicaksono, S.Kom
06-2022
*/

use App\Controllers\BaseController;
use App\Modules\Pajak\Models\PajakModel;

class Pajak extends BaseController
{
    protected $pajak;

    public function __construct()
    {
        //memanggil function di model
        $this->pajak = new PajakModel();
    }

    public function index()
    {
        return view('App\Modules\Pajak\Views/pajak', [
            'title' => 'Pajak',
        ]);
    }

    
}
