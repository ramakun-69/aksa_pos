<?php

namespace  App\Modules\Penjualan\Controllers;
/*
PT ITSHOP BISNIS DIGITAL
Website: https://itshop.biz.id
Toko Online: ITSHOP Purwokerto (https://Tokopedia.com/itshoppwt, https://Shopee.co.id/itshoppwt, https://Bukalapak.com/itshoppwt)
Dibuat oleh: Hari Wicaksono, S.Kom
03-2023
*/

use App\Controllers\BaseController;
use App\Libraries\Settings;
use App\Modules\Toko\Models\TokoModel;
use App\Modules\Bank\Models\BankAkunModel;
use App\Modules\Shift\Models\ShiftOpenCloseModel;
use CodeIgniter\I18n\Time;

class Pointofsales extends BaseController
{
	protected $setting;
	protected $toko;
	protected $bankAkun;
	protected $openclose;

	public function __construct()
	{
		//memanggil Model
		$this->setting = new Settings();
		$this->toko = new TokoModel();
		$this->bankAkun = new BankAkunModel();
		$this->openclose = new ShiftOpenCloseModel();
	}

	public function index()
	{
		// Check OpenClose Cashier
		$checkIsOpen = $this->openclose->where(['type' => 'open', 'id_login' => session()->get('id'), 'tanggal' => date('Y-m-d')])->findAll();
		$checkIsClose = $this->openclose->where(['type' => 'close', 'id_login' => session()->get('id'), 'tanggal' => date('Y-m-d')])->findAll();
		//var_dump($checkIsClose);die;
		if (empty($checkIsOpen)) {
			$this->session->setFlashdata('error', lang('App.opencloseInfo'));
			return redirect()->to('/openclose_cashier');
		}
		if (!empty($checkIsClose) && count($checkIsOpen) == 1 || count($checkIsClose) > 1) {
			$this->session->setFlashdata('error', lang('App.opencloseInfo') . '. Info: Anda sudah Close Cashier');
			return redirect()->to('/openclose_cashier');
		}
		//
		$toko = $this->toko->first();
		$bankUtama = $toko['id_bank_akun'];
		$bankAkun = $this->bankAkun->find($bankUtama);
		$jatuhtempo = $toko['jatuhtempo_hari_tanggal'];
		$tempoHari = $toko['jatuhtempo_hari'];
		$tempoTanggal = $toko['jatuhtempo_tanggal'];
		if ($jatuhtempo == 0) {
			$tanggalTempo = Date('Y-m-d', strtotime("+$tempoHari days"));
			$hariTempo = $tempoHari;
		} else {
			$date1 = new \DateTime(date("Y-m-d"));
			$date2 = new \DateTime(date('Y-m-' . $tempoTanggal, strtotime("+1 months")));
			$diff = $date2->diff($date1);
			$tanggalTempo = $date2->format('Y-m-d');
			$hariTempo = $diff->d;
		}
		return view('App\Modules\Penjualan\Views/point_of_sales', [
			'title' => 'Point of Sales (POS)',
			'namaToko' => $toko['nama_toko'],
			'cetakUSB' => $toko['printer_usb'],
			'cetakBluetooth' => $toko['printer_bluetooth'],
			'scanKeranjang' => $toko['scan_keranjang'],
			'ppn' => $toko['PPN'],
			'logo' => $this->setting->info['img_logo'],
			'idBankUtama' => $bankUtama,
			'bankAkun' => $bankAkun,
			'cashierpayPos' => $this->setting->info['cashierpay_position'],
			'jatuhTempo' => $tanggalTempo,
			'tempoHari' => $hariTempo,
			'pembulatan' => $toko['pembulatan'],
			'pembulatan_keatas' => $toko['pembulatan_keatas'],
			'pembulatan_max' => $toko['pembulatan_max'],
			'navbarColor' => $this->setting->info['navbar_color'],
			'ppnInclude' => $toko['include_ppn']
		]);
	}
}
