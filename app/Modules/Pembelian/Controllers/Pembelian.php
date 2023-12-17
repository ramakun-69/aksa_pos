<?php

namespace  App\Modules\Pembelian\Controllers;
/*
PT ITSHOP BISNIS DIGITAL
Website: https://itshop.biz.id
Toko Online: ITSHOP Purwokerto (https://Tokopedia.com/itshoppwt, https://Shopee.co.id/itshoppwt, https://Bukalapak.com/itshoppwt)
Dibuat oleh: Hari Wicaksono, S.Kom
06-2022
*/

use App\Controllers\BaseController;
use App\Libraries\Settings;
use App\Modules\Toko\Models\TokoModel;
use CodeIgniter\I18n\Time;

class Pembelian extends BaseController
{
	protected $setting;
	protected $toko;

	public function __construct()
	{
		//memanggil Model
		$this->setting = new Settings();
		$this->toko = new TokoModel();
	}


	public function index()
	{
		$cari = $this->request->getVar('faktur');
		$toko = $this->toko->first();
		return view('App\Modules\Pembelian\Views/pembelian', [
			'title' => lang('App.purchases'),
			'cetakUSB' => $toko['printer_usb'],
			'cetakBluetooth' => $toko['printer_bluetooth'],
			'scanKeranjang' => $toko['scan_keranjang'],
			'search' => $cari,
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

	public function add()
	{
		$toko = $this->toko->first();
		
		return view('App\Modules\Pembelian\Views/pembelian_baru', [
			'title' => lang('App.addPurchase'),
			'cetakUSB' => $toko['printer_usb'],
			'cetakBluetooth' => $toko['printer_bluetooth'],
			'scanKeranjang' => $toko['scan_keranjang'],
		]);
	}

}
