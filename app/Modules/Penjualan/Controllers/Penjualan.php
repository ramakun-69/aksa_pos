<?php

namespace  App\Modules\Penjualan\Controllers;
/*
PT ITSHOP BISNIS DIGITAL
Website: https://itshop.biz.id
Toko Online: ITSHOP Purwokerto (https://Tokopedia.com/itshoppwt, https://Shopee.co.id/itshoppwt, https://Bukalapak.com/itshoppwt)
Dibuat oleh: Hari Wicaksono, S.Kom
06-2022
*/

use App\Controllers\BaseController;
use App\Libraries\Settings;
use App\Modules\Dashboard\Models\DashboardModel;
use App\Modules\Toko\Models\TokoModel;
use App\Modules\Penjualan\Models\PenjualanModel;
use App\Modules\Barang\Models\BarangModel;
use App\Modules\Penjualan\Models\PenjualanItemModel;
use App\Modules\Bank\Models\BankAkunModel;
use App\Modules\Bank\Models\BankModel;
use App\Modules\Keranjang\Models\KeranjangModel;
use Spipu\Html2Pdf\Html2Pdf;
use CodeIgniter\I18n\Time;

class Penjualan extends BaseController
{
	protected $setting;
	protected $dashboard;
	protected $toko;
	protected $penjualan;
	protected $barang;
	protected $itemJual;
	protected $bankAkun;
	protected $keranjang;
	protected $bank;

	public function __construct()
	{
		//memanggil Model
		$this->setting = new Settings();
		$this->dashboard = new DashboardModel();
		$this->toko = new TokoModel();
		$this->penjualan = new PenjualanModel();
		$this->barang = new BarangModel();
		$this->itemJual = new PenjualanItemModel();
		$this->bankAkun = new BankAkunModel();
		$this->keranjang = new KeranjangModel();
		$this->bank = new BankModel();
		helper('tglindo');
	}

	public function index()
	{
		$cari = $this->request->getVar('faktur');
		$countTrxHariini = $this->dashboard->countTrxHariini();
		$countTrxHarikemarin = $this->dashboard->countTrxHarikemarin();
		$totalTrxHariini = $this->dashboard->totalTrxHariini();
		$totalTrxHarikemarin = $this->dashboard->totalTrxHarikemarin();
		$sisaPiutangHariini = $this->dashboard->sisaPiutangHariini();
		$sisaPiutangHarikemarin = $this->dashboard->sisaPiutangHarikemarin();
		$toko = $this->toko->first();

		return view('App\Modules\Penjualan\Views/penjualan', [
			'title' => lang('App.sales'),
			'countTrxHariini' => $countTrxHariini,
			'countTrxHarikemarin' => $countTrxHarikemarin,
			'totalTrxHariini' => $totalTrxHariini,
			'totalTrxHarikemarin' => $totalTrxHarikemarin,
			'sisaPiutangHariini' => $sisaPiutangHariini,
			'sisaPiutangHarikemarin' => $sisaPiutangHarikemarin,
			'cetakUSB' => $toko['printer_usb'],
			'cetakBluetooth' => $toko['printer_bluetooth'],
			'logo' => $this->setting->info['img_logo'],
			'search' => $cari,
			'hariini' => date('Y-m-d', strtotime(Time::now())),
			'tujuhHari' => date('Y-m-d', strtotime('-1 week', strtotime(Time::now()))),
			'awalBulan' => date('Y-m-', strtotime(Time::now())) . '01',
            'akhirBulan' => date('Y-m-t', strtotime(Time::now())),
			'awalTahun' => date('Y-', strtotime(Time::now())) . '01-01',
            'akhirTahun' => date('Y-', strtotime(Time::now())) . '12-31',
			'awalTahunLalu' => date('Y-', strtotime('-1 year', strtotime(Time::now()))) . '01-01',
            'akhirTahunLalu' => date('Y-', strtotime('-1 year', strtotime(Time::now()))) . '12-31',
			'pembulatan' => $toko['pembulatan'],
			'pembulatan_keatas' => $toko['pembulatan_keatas'],
			'pembulatan_max' => $toko['pembulatan_max']
		]);
	}

	public function printNotaHtml()
	{
		$input = $this->request->getVar();
		$idPenjualan = $input['id_penjualan'];
		$penjualan = $this->penjualan->getPenjualanById($idPenjualan);
		$faktur = $penjualan['faktur'];
		$data = [
			'title' => '',
			'toko' => $this->toko->first(),
			'logo' => $this->setting->info['img_logo_resize'],
			'penjualan' => $penjualan,
			'item' => $this->itemJual->findNotaCetak($idPenjualan),
			'faktur' => $faktur,
			'user' => session()->get('nama'),
			'appname' => env('appName'),
			'companyname' => env('appCompany'),
		];

		return view('App\Modules\Penjualan\Views/penjualan_html', $data);
	}

	public function printInvoiceA4()
	{
		$input = $this->request->getVar();
		$idPenjualan = $input['id_penjualan'];
		$penjualan = $this->penjualan->getPenjualanById($idPenjualan);
		$faktur = $penjualan['faktur'];
		$data = [
			'title' => '',
			'toko' => $this->toko->first(),
			'logo' => $this->setting->info['img_logo_resize'],
			'penjualan' => $penjualan,
			'item' => $this->itemJual->findNotaCetak($idPenjualan),
			'faktur' => $faktur,
			'user' => session()->get('nama'),
			'appname' => env('appName'),
			'companyname' => env('appCompany'),
		];

		return view('App\Modules\Penjualan\Views/penjualan_invoice_a4', $data);
	}

	public function printSuratjalanA4()
	{
		$input = $this->request->getVar();
		$idPenjualan = $input['id_penjualan'];
		$penjualan = $this->penjualan->getPenjualanById($idPenjualan);
		$faktur = $penjualan['faktur'];
		$data = [
			'title' => '',
			'toko' => $this->toko->first(),
			'logo' => $this->setting->info['img_logo_resize'],
			'penjualan' => $penjualan,
			'item' => $this->itemJual->findNotaCetak($idPenjualan),
			'faktur' => $faktur,
			'user' => session()->get('nama'),
			'appname' => env('appName'),
			'companyname' => env('appCompany'),
		];

		return view('App\Modules\Penjualan\Views/penjualan_suratjalan_a4', $data);
	}
	
	/* public function printNotaPdf()
	{
		$input = $this->request->getVar();
		$idPenjualan = $input['id_penjualan'];
		$data = [
			'toko' => $this->toko->first(),
			'logo' => $this->setting->info['img_logo_resize'],
			'penjualan' => $this->penjualan->getPenjualanById($idPenjualan),
			'item' => $this->itemJual->findNotaCetak($idPenjualan),
			'user' => session()->get('nama'),
			'appname' => env('appName'),
			'companyname' => env('appCompany'),
		];

		$html = view('App\Modules\Penjualan\Views/penjualan_pdf', $data);

		// create new PDF document
		$pdf = new Html2Pdf('P', 'A4');

		// Print text using writeHTMLCell()
		$pdf->writeHTML($html);
		$this->response->setContentType('application/pdf');
		// Close and output PDF document
		// This method has several options, check the source code documentation for more information.
		$file = FCPATH . 'files/cetakpenjualan.pdf';
		$pdf->Output($file, 'F');
		$attachment = base_url('files/cetakpenjualan.pdf');
		$pdf->Output('cetakpenjualan.pdf', 'I');  // display on the browser
	} */

	public function edit($id = null)
    {
        $data = $this->penjualan->find($id);
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
		$bank = $this->bank->where('id_penjualan', $id)->first();

        return view('App\Modules\Penjualan\Views/penjualan_edit', [
            'title' => lang('App.edit') . ' ' . lang('App.sales') . ' ' . $data['faktur'],
            'data' => $data,
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
			'ppnInclude' => $toko['include_ppn'],
			'noref_nokartu' => $bank['noref_nokartu'] ?? ""
        ]);
    }
}
