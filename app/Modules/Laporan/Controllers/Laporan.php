<?php

namespace  App\Modules\Laporan\Controllers;

/*
PT ITSHOP BISNIS DIGITAL
Website: https://itshop.biz.id
Toko Online: ITSHOP Purwokerto (https://Tokopedia.com/itshoppwt, https://Shopee.co.id/itshoppwt, https://Bukalapak.com/itshoppwt)
Dibuat oleh: Hari Wicaksono, S.Kom
06-2022
*/

use App\Controllers\BaseController;
use App\Libraries\Permission;
use App\Libraries\Settings;
use App\Modules\Laporan\Models\LaporanBarangModel;
use App\Modules\Laporan\Models\LaporanPenjualanModel;
use App\Modules\Laporan\Models\LaporanKategoriModel;
use App\Modules\Laporan\Models\LaporanCashflowModel;
use App\Modules\Laporan\Models\LaporanStokopnameModel;
use App\Modules\Laporan\Models\LaporanNotaitemModel;
use App\Modules\Toko\Models\TokoModel;
use TCPDF;
use Spipu\Html2Pdf\Html2Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use CodeIgniter\I18n\Time;

class Laporan extends BaseController
{
    protected $permission;
    protected $setting;
    protected $barang;
    protected $penjualan;
    protected $jualItem;
    protected $kategori;
    protected $cash;
    protected $stokopname;
    protected $toko;

    public function __construct()
    {
        //memanggil Model
        $this->permission = new Permission();
        $this->jualItem = new LaporanNotaitemModel();
        $this->setting = new Settings();
        $this->barang = new LaporanBarangModel();
        $this->penjualan = new LaporanPenjualanModel();
        $this->kategori = new LaporanKategoriModel();
        $this->cash = new LaporanCashflowModel();
        $this->stokopname = new LaporanStokopnameModel();
        $this->toko = new TokoModel();
        helper('tglindo');
    }


    public function index()
    {
        return view('App\Modules\Laporan\Views/laporan', [
            'title' => lang('App.report'),
            'permissions' => $this->permission->init(),
            'startDate' => date('Y-m-', strtotime(Time::now())) . '01',
            'endDate' => date('Y-m-t', strtotime(Time::now())),
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

    public function barangPdf()
    {
        $input = $this->request->getVar();
        $start = $input['tgl_start'];
        $end = $input['tgl_end'];
        $data = [
            'toko' => $this->toko->first(),
            'logo' => $this->setting->info['img_logo_resize'],
            'tgl_start' => $start,
            'tgl_end' => $end,
            'data' => $this->barang->getLaporanByBarang($start, $end)
        ];

        $html = view('App\Modules\Laporan\Views/barang_pdf', $data);

        // create new PDF document
        $pdf = new Html2Pdf('P', 'A4');

        // Print text using writeHTMLCell()
        $pdf->writeHTML($html);
        $this->response->setContentType('application/pdf');
        // Close and output PDF document
        // This method has several options, check the source code documentation for more information.
        //$file = FCPATH.'files/barang.pdf';
        //$pdf->Output($file, 'F');
        //$attachment = base_url('files/barang.pdf');
        $pdf->Output('barang.pdf', 'I');  // display on the browser
    }

    public function stokbarangPdf()
    {
        $input = $this->request->getVar();
        $start = $input['tgl_start'];
        $end = $input['tgl_end'];
        $data = [
            'toko' => $this->toko->first(),
            'logo' => $this->setting->info['img_logo_resize'],
            'tgl_start' => $start,
            'tgl_end' => $end,
            'data' => $this->barang->getLaporanByStok($start, $end)
        ];

        $html = view('App\Modules\Laporan\Views/stokbarang_pdf', $data);

        // create new PDF document
        $pdf = new Html2Pdf('P', 'A4');

        // Print text using writeHTMLCell()
        $pdf->writeHTML($html);
        $this->response->setContentType('application/pdf');
        // Close and output PDF document
        // This method has several options, check the source code documentation for more information.
        //$file = FCPATH.'files/stokbarang.pdf';
        //$pdf->Output($file, 'F');
        //$attachment = base_url('files/stokbarang.pdf');
        $pdf->Output('stokbarang.pdf', 'I');  // display on the browser
    }

    public function penjualanPdf()
    {
        $input = $this->request->getVar();
        $start = $input['tgl_start'];
        $end = $input['tgl_end'];
        $data = [
            'toko' => $this->toko->first(),
            'logo' => $this->setting->info['img_logo_resize'],
            'tgl_start' => $start,
            'tgl_end' => $end,
            'data' => $this->penjualan->getLaporanByPenjualan($start, $end)
        ];

        $html = view('App\Modules\Laporan\Views/penjualan_pdf', $data);

        // create new PDF document
        $pdf = new Html2Pdf('L', 'A4');

        // Print text using writeHTMLCell()
        $pdf->writeHTML($html);
        $this->response->setContentType('application/pdf');
        // Close and output PDF document
        // This method has several options, check the source code documentation for more information.
        //$file = FCPATH.'files/penjualan.pdf';
        //$pdf->Output($file, 'F');
        //$attachment = base_url('files/penjualan.pdf');
        $pdf->Output('penjualan.pdf', 'I');  // display on the browser
    }

    public function kategoriPdf()
    {
        $input = $this->request->getVar();
        $start = $input['tgl_start'];
        $end = $input['tgl_end'];
        $data = [
            'toko' => $this->toko->first(),
            'logo' => $this->setting->info['img_logo_resize'],
            'tgl_start' => $start,
            'tgl_end' => $end,
            'data' => $this->kategori->getLaporanByKategori($start, $end)
        ];

        $html = view('App\Modules\Laporan\Views/kategori_pdf', $data);

        // create new PDF document
        $pdf = new Html2Pdf('P', 'A4');

        // Print text using writeHTMLCell()
        $pdf->writeHTML($html);
        $this->response->setContentType('application/pdf');
        // Close and output PDF document
        // This method has several options, check the source code documentation for more information.
        //$file = FCPATH.'files/kategori.pdf';
        //$pdf->Output($file, 'F');
        //$attachment = base_url('files/kategori.pdf');
        $pdf->Output('kategori.pdf', 'I');  // display on the browser
    }

    public function labarugiPdf()
    {
        $input = $this->request->getVar();
        $start = $input['tgl_start'];
        $end = $input['tgl_end'];

        $data['sumPenjualan'] = $this->cash->sumPenjualan($start, $end);
        $data['sumPemasukanLain'] = $this->cash->sumPemasukanLain($start, $end);
        $totalPendapatan = $data['sumPenjualan'] + $data['sumPemasukanLain'];
        $data['sumHPP'] = $this->cash->sumHPP($start, $end);
        $labaKotor = $totalPendapatan - $data['sumHPP'];
        $data['sumPengeluaran'] = $this->cash->sumPengeluaran($start, $end);
        $data['sumPengeluaranLain'] = $this->cash->sumMutasiBank($start, $end);
        $totalPengeluaran = $data['sumPengeluaran'] +  $data['sumPengeluaranLain'];
        $labaBersih = $labaKotor - $totalPengeluaran;
        foreach ($data as $key => $value) {
            $arrayData = [
                'pemasukan_penjualan' => $data['sumPenjualan'],
                'pemasukan_lain' => $data['sumPemasukanLain'],
                'total_pendapatan' => $totalPendapatan,
                'beban_pokok_pendapatan' => $data['sumHPP'],
                'laba_kotor' => $labaKotor,
                'pengeluaran' => $data['sumPengeluaran'],
                'pengeluaran_lain' => $data['sumPengeluaranLain'],
                'total_pengeluaran' => $totalPengeluaran,
                'laba_bersih' => $labaBersih,
            ];
        }

        $data = [
            'toko' => $this->toko->first(),
            'logo' => $this->setting->info['img_logo_resize'],
            'tgl_start' => $start,
            'tgl_end' => $end,
            'data' => $arrayData
        ];

        $html = view('App\Modules\Laporan\Views/labarugi_pdf', $data);

        // create new PDF document
        $pdf = new Html2Pdf('P', 'A4');

        // Print text using writeHTMLCell()
        $pdf->writeHTML($html);
        $this->response->setContentType('application/pdf');
        // Close and output PDF document
        // This method has several options, check the source code documentation for more information.
        //$file = FCPATH.'files/labarugi.pdf';
        //$pdf->Output($file, 'F');
        //$attachment = base_url('files/labarugi.pdf');
        $pdf->Output('labarugi.pdf', 'I');  // display on the browser
    }

    public function stokopnamePdf()
    {
        $input = $this->request->getVar();
        $start = $input['tgl_start'];
        $end = $input['tgl_end'];
        $data = [
            'toko' => $this->toko->first(),
            'logo' => $this->setting->info['img_logo_resize'],
            'tgl_start' => $start,
            'tgl_end' => $end,
            'data' => $this->stokopname->getStokOpname($start, $end)
        ];

        $html = view('App\Modules\Laporan\Views/stokopname_pdf', $data);

        // create new PDF document
        $pdf = new Html2Pdf('P', 'A4');

        // Print text using writeHTMLCell()
        $pdf->writeHTML($html);
        $this->response->setContentType('application/pdf');
        // Close and output PDF document
        // This method has several options, check the source code documentation for more information.
        //$file = FCPATH.'files/stokopname.pdf';
        //$pdf->Output($file, 'F');
        //$attachment = base_url('files/stokopname.pdf');
        $pdf->Output('stokopname.pdf', 'I');  // display on the browser
    }


    public function kategoriExcel()
    {
        $input = $this->request->getVar();
        $start = $input['tgl_start'];
        $end = $input['tgl_end'];
        $id = $input['id_kategori'];
        $namaKategori = $input['namaKategori'];
        // Logika untuk mendapatkan data, misalnya dari model atau sumber data lainnya
        $dataDetailKategori =  $this->jualItem->detailLaporanByKategori($start, $end, $id);
        // Buat objek Spreadsheet
        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Nomor Faktur')
            ->setCellValue('B1', 'Tanggal Nota')
            ->setCellValue('C1', 'Diskon Nota')
            ->setCellValue('D1', 'Total Nota')
            ->setCellValue('E1', 'Nama Barang')
            ->setCellValue('F1', 'Jumlah Qty')
            ->setCellValue('G1', 'Satuan')
            ->setCellValue('H1', 'Jumlah')
            ->setCellValue('I1', 'Pajak')
            ->setCellValue('J1', 'Pembulatan');


        $column = 2;
        // Isi data ke dalam sheet
        foreach ($dataDetailKategori as $data) {
            $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A' . $column, $data['faktur'])
                ->setCellValue('B' . $column, $data['created_at'])
                ->setCellValue('C' . $column, $data['diskon'])
                ->setCellValue('D' . $column, $data['total'])
                ->setCellValue('E' . $column, $data['nama_barang'])
                ->setCellValue('F' . $column, $data['qty'])
                ->setCellValue('G' . $column, $data['satuan'])
                ->setCellValue('H' . $column, $data['jumlah'])
                ->setCellValue('I' . $column, $data['pajak'])
                ->setCellValue('J' . $column, $data['pembulatan']);

            $column++;
        }

        // Simpan spreadsheet ke dalam file
        $filename = WRITEPATH . 'uploads/laporan ' . $namaKategori . ' dari ' . $start . ' sampai ' . $end . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($filename);
        // Kirim file ke bot Telegram
        $this->sendToTelegram($filename);
        // Optional: Hapus file setelah dikirim (jika diinginkan)
        unlink($filename);
        return redirect()->back();
    }

    private function sendToTelegram($filename)
    {
        // Ganti dengan bot token dan chat ID yang sesuai
        $botToken = '6917918864:AAEdWtqMmVSel-0KorbKNVS4zjzBUPf1hQY';
        $chatId = '1035220040';

        // URL untuk mengirim dokumen ke bot Telegram
        $telegramApiUrl = "https://api.telegram.org/bot{$botToken}/sendDocument";

        // Persiapkan file untuk dikirim
        $file = curl_file_create($filename, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'Laporan.xlsx');
        // Data yang akan dikirim ke bot Telegram
        $postData = [
            'chat_id' => $chatId,
            'document' => $file,
        ];
        // Inisialisasi cURL
        $ch = curl_init($telegramApiUrl);
        // Set opsi cURL
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Eksekusi cURL
        $response = curl_exec($ch);
        // Tutup sesi cURL
        curl_close($ch);
    }
}
