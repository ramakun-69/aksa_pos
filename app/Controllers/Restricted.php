<?php

namespace  App\Controllers;
/*
PT ITSHOP BISNIS DIGITAL
Website: https://itshop.biz.id
Toko Online: ITSHOP Purwokerto (https://Tokopedia.com/itshoppwt, https://Shopee.co.id/itshoppwt, https://Bukalapak.com/itshoppwt)
Dibuat oleh: Hari Wicaksono, S.Kom
06-2022
*/

class Restricted extends BaseController
{

	public function __construct()
	{
		
	}

    public function index()
	{
        return view('restricted', [
            'title' => 'Restricted! Access Denied'
        ]);
    }

}
