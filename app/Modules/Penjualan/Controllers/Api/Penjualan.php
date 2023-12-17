<?php

namespace App\Modules\Penjualan\Controllers\Api;

use Exception;
use App\Controllers\BaseControllerApi;
use App\Libraries\Settings;
use App\Modules\Bank\Models\BankModel;
use App\Modules\Penjualan\Models\PenjualanModel;
use App\Modules\Barang\Models\BarangModel;
use App\Modules\Penjualan\Models\PenjualanItemModel;
use App\Modules\Toko\Models\TokoModel;
use App\Modules\Cashflow\Models\CashflowModel;
use App\Modules\Pajak\Models\PajakModel;
use App\Modules\Piutang\Models\PiutangModel;
use App\Modules\Log\Models\LogModel;
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\RawbtPrintConnector;
use Mike42\Escpos\CapabilityProfile;
use CodeIgniter\I18n\Time;

class Penjualan extends BaseControllerApi
{
    protected $format       = 'json';
    protected $modelName    = PenjualanModel::class;
    protected $setting;
    protected $barang;
    protected $itemJual;
    protected $toko;
    protected $cashflow;
    protected $pajak;
    protected $piutang;
    protected $bank;
    protected $log;

    public function __construct()
    {
        $this->setting = new Settings();
        $this->barang = new BarangModel();
        $this->itemJual = new PenjualanItemModel();
        $this->toko = new TokoModel();
        $this->cashflow = new CashflowModel();
        $this->pajak = new PajakModel();
        $this->piutang = new PiutangModel();
        $this->bank = new BankModel();
        $this->log = new LogModel();
        helper('tglindo');
        helper('text');
    }

    public function index()
    {
        $input = $this->request->getVar();
        $start = $input['tgl_start'] ?? "";
        $end = $input['tgl_end'] ?? "";
        if ($start == "" && $end == "") {
            $data = $this->model->getPenjualan();
        } else {
            $data = $this->model->getPenjualan($start, $end);
        }
        if (!empty($data)) {
            $response = [
                "status" => true,
                "message" => lang('App.getSuccess'),
                "data" => $data
            ];
            return $this->respond($response, 200);
        } else {
            $response = [
                'status' => false,
                'message' => lang('App.noData'),
                'data' => []
            ];
            return $this->respond($response, 200);
        }
    }

    public function show($id = null)
    {
        return $this->respond(["status" => true, "message" => lang("App.getSuccess"), "data" => $this->model->getPenjualanById($id)], 200);
    }

    public function create()
    {
        $rules = [
            'bayar' => [
                'rules'  => 'required',
                'errors' => []
            ],
            'id_kontak' => [
                'rules'  => 'required',
                'errors' => []
            ],
        ];

        if ($this->request->getJSON()) {
            $json = $this->request->getJSON();
            $totalhpp = $json->hpp;
            $subtotal = $json->subtotal;
            $totaldiskon = $json->diskon;
            $diskonpersen = $json->diskon_persen;
            $pembulatan = $json->pembulatan;
            $total = $json->total;
            $bayar = $json->bayar;
            $kembali = $json->kembali;
            $idkontak = $json->id_kontak;
            $ppn = $json->ppn;
            $pajak = $json->pajak;
            $totalLaba = $subtotal - $totalhpp;
            $jatuhtempo = $json->jatuh_tempo;
            $metodeBayar = $json->metode_bayar;
            $catatan = $json->catatan;
        } else {
            $totalhpp = $this->request->getPost('hpp');
            $subtotal = $this->request->getPost('subtotal');
            $totaldiskon = $this->request->getPost('diskon');
            $diskonpersen = $this->request->getPost('diskon_persen');
            $pembulatan = $this->request->getPost('pembulatan');
            $total = $this->request->getPost('total');
            $bayar = $this->request->getPost('bayar');
            $kembali = $this->request->getPost('kembali');
            $idkontak = $this->request->getPost('id_kontak');
            $ppn = $this->request->getPost('ppn');
            $pajak = $this->request->getPost('pajak');
            $totalLaba = $subtotal - $totalhpp;
            $jatuhtempo = $this->request->getPost('jatuh_tempo');
            $metodeBayar = $this->request->getPost('metode_bayar');
            $catatan = $this->request->getPost('catatan');
        }

        if (!$this->validate($rules)) {
            $response = [
                'status' => false,
                'message' => lang('App.isRequired'),
                'data' => $this->validator->getErrors(),
            ];
            return $this->respond($response, 200);
        } else {
            if ($bayar != 0) {
                $tanggal = date('Y-m-d');
                $tanggaltempo = $jatuhtempo;
                //Hitung
                $hitung = $bayar - $total;
                $piutang = $total - $bayar;

                //Ambil request post data
                $input = $this->request->getVar('data');
                foreach ($input as $value) {
                    $id_barang[] = $value[0];
                    $harga_jual[] = $value[1];
                    $stok[] = $value[2];
                    $jumlah[] = $value[3];
                    $satuan[] = $value[4];
                    $harga_beli[] = $value[5];
                    $diskon[] = $value[6];
                    $diskon_persen[] = $value[7];
                    $hpp[] = $value[8];
                    $total_laba[] = $value[9];
                }
                $total_barang = count($id_barang);

                //Ambil Data Toko
                $toko = $this->toko->first();
                //Ambil kode jual toko
                $kdJual = $toko['kode_jual'];
                $kdJualTahun = $toko['kode_jual_tahun'];
                //Ambil timestamp
                $time = Time::now();
                if ($time->getHour() < 10) {
                    $getHour = '0' . $time->getHour();
                } else {
                    $getHour = $time->getHour();
                }
                if ($time->getMinute() < 10) {
                    $getMinute = '0' . $time->getMinute();
                } else {
                    $getMinute = $time->getMinute();
                }
                if ($time->getSecond() < 10) {
                    $getSecond = '0' . $time->getSecond();
                } else {
                    $getSecond = $time->getSecond();
                }
                $timestamp = $getHour . $getMinute . $getSecond;

                if ($kdJualTahun == '1') {
                    //Hitung transaksi tgl-bulan-tahun total tambah 1
                    $query = $this->model->select('DATE(created_at) as date_val, COUNT(*) as total')->groupBy('DATE(created_at)');
                    $hasil = $query->get()->getRowArray();
                    if (empty($hasil)) {
                        $last = 1;
                    } else {
                        $last = $hasil['total'] + 1;
                    }
                    $lastKode = sprintf('%02s', $last);
    
                    $kodeNota = $kdJual . random_string('numeric', 3) . date('dmy') . '-' . $lastKode;
                } else {
                    //Hitung transaksi bulan-tahun total tambah 1
                    $query = $this->model->select('YEAR(created_at) as year_val, MONTH(created_at) as month_val, COUNT(*) as total')->groupBy('YEAR(created_at), MONTH(created_at)');
                    $hasil = $query->get()->getRowArray();
                    if (empty($hasil)) {
                        $last = 1;
                    } else {
                        $last = $hasil['total'] + 1;
                    }
                    $lastKode = sprintf('%02s', $last);
    
                    $kodeNota = $kdJual . random_string('numeric', 3) . date('my') . '-' . $lastKode;
                }

                $dataNota = [
                    'faktur' => $kodeNota,
                    'id_kontak' => $idkontak,
                    'jumlah' => $total_barang,
                    'PPN' => $ppn,
                    'hpp' => $totalhpp,
                    'subtotal' => $subtotal,
                    'diskon' => $totaldiskon,
                    'diskon_persen' => $diskonpersen,
                    'pajak' => $pajak,
                    'pembulatan' => $pembulatan,
                    'total' => $total,
                    'bayar' => $bayar,
                    'kembali' => $kembali,
                    'total_laba' => $totalLaba,
                    'periode' => date('m-Y'),
                    'id_login' => session()->get('id'),
                    'id_toko' => 1,
                    'metode_bayar' => $metodeBayar,
                    'catatan' => $catatan,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => null
                ];
                //Save Nota
                $this->model->save($dataNota);
                $idPenjualan = $this->model->getInsertID();
                //Save Log
                $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Save Penjualan - Cash: ' . $kodeNota]);

                $arrNota = array();
                foreach ($input as $key => $value) {
                    $id_barang = $value[0];
                    $harga_jual = $value[1];
                    $stok = $value[2];
                    $qty = $value[3];
                    $satuan = $value[4];
                    $harga_beli = $value[5];
                    $diskon = $value[6];
                    $diskon_persen = $value[7];
                    $hpp = $value[8];
                    $total_laba = $value[9];
                    if ($toko['include_ppn'] == 1) {
                        $total = ((int)$harga_jual - (int)$diskon) * $qty;
                        $ppn = ($toko['PPN'] / 100);
                        $pajak = $total * $ppn;
                        $jumlah = ((int)$harga_jual - (int)$diskon) * $qty - $pajak;
                    } else {
                        $jumlah = ((int)$harga_jual - (int)$diskon) * $qty;
                        $ppn = ($toko['PPN'] / 100);
                        $pajak = $jumlah * $ppn;
                    }
                    $item = array(
                        'id_barang' => $id_barang,
                        'id_penjualan' => $idPenjualan,
                        'harga_beli' => $harga_beli,
                        'harga_jual' => $harga_jual,
                        'diskon' => $diskon,
                        'diskon_persen' => $diskon_persen,
                        'stok' => $stok,
                        'qty' => $qty,
                        'satuan' => $satuan,
                        'hpp' => $hpp,
                        'jumlah' => $jumlah,
                        'ppn' => $pajak,
                        'total_laba' => $total_laba,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => null
                    );
                    array_push($arrNota, $item);
                }
                $dataItem = $arrNota;
                $this->itemJual->insertBatch($dataItem);

                //Update stok barang dikurangi qty
                $arrStok = array();
                foreach ($input as $key => $value) {
                    $id_barang = $value[0];
                    $harga_jual = $value[1];
                    $stok = $value[2];
                    $qty = $value[3];
                    $satuan = $value[4];
                    $harga_beli = $value[5];
                    $diskon = $value[6];
                    $diskon_persen = $value[7];
                    $hpp = $value[8];
                    $total_laba = $value[9];
                    $stock = array(
                        'id_barang' => $id_barang,
                        'stok' => $stok - $qty,
                    );
                    array_push($arrStok, $stock);
                }
                $dataStok = $arrStok;
                $this->barang->updateBatch($dataStok, 'id_barang');

                //Update status barang (active = 0) jika stok 0 / habis
                $arrStokHabis = array();
                foreach ($input as $key => $value) {
                    $id_barang = $value[0];
                    $harga_jual = $value[1];
                    $stok = $value[2];
                    $qty = $value[3];
                    $satuan = $value[4];
                    $harga_beli = $value[5];
                    $diskon = $value[6];
                    $diskon_persen = $value[7];
                    $hpp = $value[8];
                    $total_laba = $value[9];
                    //Cari data barang/barangnya
                    $barang = $this->barang->where('id_barang', $id_barang)->first();
                    $stok = $barang['stok'];
                    if ($stok == 0) {
                        $stokHabis = array(
                            'id_barang' => $id_barang,
                            'active' => 0,
                        );
                    } else {
                        $stokHabis = array(
                            'id_barang' => $id_barang,
                            'active' => 1,
                        );
                    }
                    array_push($arrStokHabis, $stokHabis);
                }
                $dataStokHabis = $arrStokHabis;
                $this->barang->updateBatch($dataStokHabis, 'id_barang');

                //Data Pajak - Ambil besaran PPN Toko
                if ($toko['PPN'] > 0) :
                    //Ambil kode Pajak
                    $kdPajak = $toko['kode_pajak'];
                    if ($kdJualTahun == '1') {
                        $kodePajak = $kdPajak . date('dmy') . '-' . $timestamp;
                    } else {
                        $kodePajak = $kdPajak . $timestamp;
                    }
                    $dataPajak = [
                        'faktur' =>  $kodePajak,
                        'PPN' => $ppn,
                        'jenis' => 'Keluaran',
                        'nominal' => $pajak,
                        'pembulatan' => $pembulatan,
                        'keterangan' => 'Penjualan: ' . $kodeNota,
                        'id_toko' => 1,
                        'id_login' => session()->get('id'),
                        'id_penjualan' => $idPenjualan,
                    ];
                    //Save Pajak
                    $this->pajak->save($dataPajak);
                    //Save Log
                    $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Save Pajak: ' . $kodePajak . ' Keterangan: Keluaran Penjualan']);
                endif;

                //Jika Lunas
                if ($bayar >= $total) {
                    if ($toko['pembulatan_keatas'] == 1) {
                        $nominalKas = ($total - $pajak);
                    } else {
                        $nominalKas = $total - $pajak - ($pembulatan);
                        //Simpan Pembulatan Kebawah karena Total dikurangi
                        //Data Cashflow
                        $dataKas = [
                            'faktur' => $kodeNota,
                            'jenis' => 'Pengeluaran',
                            'kategori' => 'Penjualan',
                            'tanggal' => date('Y-m-d', strtotime(Time::now())),
                            'waktu' => date('H:i:s', strtotime(Time::now())),
                            'pemasukan' => 0,
                            'pengeluaran' => abs($pembulatan),
                            'keterangan' => 'Pembulatan Kebawah: ' . $kodeNota,
                            'id_penjualan' => $idPenjualan,
                            'id_toko' => 1,
                            'id_login' => session()->get('id'),
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => null
                        ];
                        //Save Kas
                        $this->cashflow->save($dataKas);
                        //Save Log
                        $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Save Cashflow Pembulatan: ' . $kodeNota]);
                    }
                    $dataKas = [
                        'faktur' => $kodeNota,
                        'jenis' => 'Pemasukan',
                        'kategori' => 'Penjualan',
                        'tanggal' => date('Y-m-d', strtotime(Time::now())),
                        'waktu' => date('H:i:s', strtotime(Time::now())),
                        'pemasukan' => $nominalKas,
                        'pengeluaran' => 0,
                        'keterangan' => 'Penjualan: ' . $kodeNota,
                        'id_penjualan' => $idPenjualan,
                        'id_toko' => 1,
                        'id_login' => session()->get('id'),
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => null
                    ];
                    //Save Cashflow
                    $this->cashflow->save($dataKas);
                    //Save Log
                    $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Save Cashflow: ' . $kodeNota]);

                    $response = [
                        'status' => true,
                        'message' => lang('App.saveSuccess'),
                        'data' => ['id_penjualan' => $idPenjualan],
                    ];
                    return $this->respond($response, 200);
                } else {
                    //Bayar kurang Masukkan ke Piutang
                    $dataPiutang = [
                        'id_penjualan' =>  $idPenjualan,
                        'tanggal' => $tanggal,
                        'jatuh_tempo' => $tanggaltempo,
                        'jumlah_piutang' => $piutang,
                        'jumlah_bayar' => 0,
                        'sisa_piutang' => $piutang,
                        'status_piutang' => 0,
                        'keterangan' => '',
                        'id_login' => session()->get('id'),
                        'id_toko' => 1
                    ];
                    //Save Piutang
                    $this->piutang->save($dataPiutang);
                    $idPiutang =  $this->piutang->getInsertID();
                    //Save Log
                    $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Save Piutang: ' . $idPenjualan]);

                    if ($toko['pembulatan_keatas'] == 1) {
                        $nominalKas = ($total - $pajak) - $piutang;
                    } else {
                        $nominalKas = ($total - $pajak) - $piutang - ($pembulatan);
                    }
                    $dataKas = [
                        'faktur' => $kodeNota,
                        'jenis' => 'Pemasukan',
                        'kategori' => 'Penjualan',
                        'tanggal' => date('Y-m-d', strtotime(Time::now())),
                        'waktu' => date('H:i:s', strtotime(Time::now())),
                        'pemasukan' => $nominalKas,
                        'pengeluaran' => 0,
                        'keterangan' => 'Penjualan: ' . $kodeNota . '. Piutang ID: ' . $idPiutang,
                        'id_penjualan' => $idPenjualan,
                        'id_toko' => 1,
                        'id_login' => session()->get('id'),
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => null
                    ];
                    //Save Cashflow
                    $this->cashflow->save($dataKas);
                    //Save Log
                    $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Save Cashflow: ' . $kodeNota]);

                    $response = [
                        'status' => true,
                        'message' => lang('App.saveSuccess') . ". " . lang('App.underPayment') . ". Rp. " . $hitung,
                        'data' => ['id_penjualan' => $idPenjualan],
                    ];
                    return $this->respond($response, 200);
                }
            } else {
                $response = [
                    'status' => false,
                    'message' => lang('App.selectBayar'),
                    'data' => [],
                ];
                return $this->respond($response, 200);
            }
        }
    }

    public function create1()
    {
        //Ambil Data Toko
        $toko = $this->toko->first();
        $ketJatuhTempo = $toko['jatuhtempo_keterangan'];
        if ($ketJatuhTempo == 0) {
            $rules = [
                'bayar' => [
                    'rules'  => 'required',
                    'errors' => []
                ],
                'jatuh_tempo' => [
                    'rules'  => 'required',
                    'errors' => []
                ],
                'id_kontak' => [
                    'rules'  => 'required',
                    'errors' => []
                ],
            ];
        } else {
            $rules = [
                'bayar' => [
                    'rules'  => 'required',
                    'errors' => []
                ],
                'jatuh_tempo' => [
                    'rules'  => 'required',
                    'errors' => []
                ],
                'keterangan' => [
                    'rules'  => 'required',
                    'errors' => []
                ],
                'id_kontak' => [
                    'rules'  => 'required',
                    'errors' => []
                ],
            ];
        }

        if ($this->request->getJSON()) {
            $json = $this->request->getJSON();
            $totalhpp = $json->hpp;
            $subtotal = $json->subtotal;
            $totaldiskon = $json->diskon;
            $diskonpersen = $json->diskon_persen;
            $pembulatan = $json->pembulatan;
            $total = $json->total;
            $bayar = $json->bayar;
            $kembali = $json->kembali;
            $idkontak = $json->id_kontak;
            $ppn = $json->ppn;
            $pajak = $json->pajak;
            $totalLaba = $subtotal - $totalhpp;
            $jatuhtempo = $json->jatuh_tempo;
            $keterangan = $json->keterangan;
            $metodeBayar = $json->metode_bayar;
            $catatan = $json->catatan;
        } else {
            $totalhpp = $this->request->getPost('hpp');
            $subtotal = $this->request->getPost('subtotal');
            $totaldiskon = $this->request->getPost('diskon');
            $diskonpersen = $this->request->getPost('diskon_persen');
            $pembulatan = $this->request->getPost('pembulatan');
            $total = $this->request->getPost('total');
            $bayar = $this->request->getPost('bayar');
            $kembali = $this->request->getPost('kembali');
            $idkontak = $this->request->getPost('id_kontak');
            $ppn = $this->request->getPost('ppn');
            $pajak = $this->request->getPost('pajak');
            $totalLaba = $subtotal - $totalhpp;
            $jatuhtempo = $this->request->getPost('jatuh_tempo');
            $keterangan = $this->request->getPost('keterangan');
            $metodeBayar = $this->request->getPost('metode_bayar');
            $catatan = $this->request->getPost('catatan');
        }

        if (!$this->validate($rules)) {
            $response = [
                'status' => false,
                'message' => lang('App.isRequired'),
                'data' => $this->validator->getErrors(),
            ];
            return $this->respond($response, 200);
        } else {
            if ($bayar < $total) {
                $tanggal = date('Y-m-d');
                $tanggaltempo = $jatuhtempo;
                //Hitung
                $hitung = $bayar - $total;
                $piutang = $total - $bayar;

                //Ambil request post data
                $input = $this->request->getVar('data');
                foreach ($input as $value) {
                    $id_barang[] = $value[0];
                    $harga_jual[] = $value[1];
                    $stok[] = $value[2];
                    $jumlah[] = $value[3];
                    $satuan[] = $value[4];
                    $harga_beli[] = $value[5];
                    $diskon[] = $value[6];
                    $diskon_persen[] = $value[7];
                    $hpp[] = $value[8];
                    $total_laba[] = $value[9];
                }
                $total_barang = count($id_barang);

                //Ambil Data Toko
                $toko = $this->toko->first();
                //Ambil kode jual toko
                $kdJual = $toko['kode_jual'];
                $kdJualTahun = $toko['kode_jual_tahun'];
                //Ambil timestamp
                $time = Time::now();
                if ($time->getHour() < 10) {
                    $getHour = '0' . $time->getHour();
                } else {
                    $getHour = $time->getHour();
                }
                if ($time->getMinute() < 10) {
                    $getMinute = '0' . $time->getMinute();
                } else {
                    $getMinute = $time->getMinute();
                }
                if ($time->getSecond() < 10) {
                    $getSecond = '0' . $time->getSecond();
                } else {
                    $getSecond = $time->getSecond();
                }
                $timestamp = $getHour . $getMinute . $getSecond;

                if ($kdJualTahun == '1') {
                    //Hitung transaksi tgl-bulan-tahun total tambah 1
                    $query = $this->model->select('DATE(created_at) as date_val, COUNT(*) as total')->groupBy('DATE(created_at)');
                    $hasil = $query->get()->getRowArray();
                    if (empty($hasil)) {
                        $last = 1;
                    } else {
                        $last = $hasil['total'] + 1;
                    }
                    $lastKode = sprintf('%02s', $last);
    
                    $kodeNota = $kdJual . random_string('numeric', 3) . date('dmy') . '-' . $lastKode;
                } else {
                    //Hitung transaksi bulan-tahun total tambah 1
                    $query = $this->model->select('YEAR(created_at) as year_val, MONTH(created_at) as month_val, COUNT(*) as total')->groupBy('YEAR(created_at), MONTH(created_at)');
                    $hasil = $query->get()->getRowArray();
                    if (empty($hasil)) {
                        $last = 1;
                    } else {
                        $last = $hasil['total'] + 1;
                    }
                    $lastKode = sprintf('%02s', $last);
    
                    $kodeNota = $kdJual . random_string('numeric', 3) . date('my') . '-' . $lastKode;
                }

                $dataNota = [
                    'faktur' => $kodeNota,
                    'id_kontak' => $idkontak,
                    'jumlah' => $total_barang,
                    'PPN' => $ppn,
                    'hpp' => $totalhpp,
                    'subtotal' => $subtotal,
                    'diskon' => $totaldiskon,
                    'diskon_persen' => $diskonpersen,
                    'pajak' => $pajak,
                    'pembulatan' => $pembulatan,
                    'total' => $total,
                    'bayar' => $bayar,
                    'kembali' => $kembali,
                    'total_laba' => $totalLaba,
                    'periode' => date('m-Y'),
                    'id_login' => session()->get('id'),
                    'id_toko' => 1,
                    'metode_bayar' => $metodeBayar,
                    'catatan' => $catatan,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => null
                ];
                $this->model->save($dataNota);
                $idPenjualan = $this->model->getInsertID();
                //Save Log
                $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Save Penjualan - Credit: ' . $kodeNota]);

                $arrNota = array();
                foreach ($input as $key => $value) {
                    $id_barang = $value[0];
                    $harga_jual = $value[1];
                    $stok = $value[2];
                    $qty = $value[3];
                    $satuan = $value[4];
                    $harga_beli = $value[5];
                    $diskon = $value[6];
                    $diskon_persen = $value[7];
                    $hpp = $value[8];
                    $total_laba = $value[9];
                    if ($toko['include_ppn'] == 1) {
                        $total = ((int)$harga_jual - (int)$diskon) * $qty;
                        $ppn = ($toko['PPN'] / 100);
                        $pajak = $total * $ppn;
                        $jumlah = ((int)$harga_jual - (int)$diskon) * $qty - $pajak;
                    } else {
                        $jumlah = ((int)$harga_jual - (int)$diskon) * $qty;
                        $ppn = ($toko['PPN'] / 100);
                        $pajak = $jumlah * $ppn;
                    }
                    $item = array(
                        'id_barang' => $id_barang,
                        'id_penjualan' => $idPenjualan,
                        'harga_beli' => $harga_beli,
                        'harga_jual' => $harga_jual,
                        'diskon' => $diskon,
                        'diskon_persen' => $diskon_persen,
                        'stok' => $stok,
                        'qty' => $qty,
                        'satuan' => $satuan,
                        'hpp' => $hpp,
                        'jumlah' => $jumlah,
                        'ppn' => $pajak,
                        'total_laba' => $total_laba,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => null
                    );
                    array_push($arrNota, $item);
                }
                $dataItem = $arrNota;
                $this->itemJual->insertBatch($dataItem);

                //Update stok barang dikurangi qty
                $arrStok = array();
                foreach ($input as $key => $value) {
                    $id_barang = $value[0];
                    $harga_jual = $value[1];
                    $stok = $value[2];
                    $qty = $value[3];
                    $satuan = $value[4];
                    $harga_beli = $value[5];
                    $diskon = $value[6];
                    $diskon_persen = $value[7];
                    $hpp = $value[8];
                    $total_laba = $value[9];
                    $stock = array(
                        'id_barang' => $id_barang,
                        'stok' => $stok - $qty,
                    );
                    array_push($arrStok, $stock);
                }
                $dataStok = $arrStok;
                $this->barang->updateBatch($dataStok, 'id_barang');

                //Update status barang (active = 0) jika stok 0 / habis
                $arrStokHabis = array();
                foreach ($input as $key => $value) {
                    $id_barang = $value[0];
                    $harga_jual = $value[1];
                    $stok = $value[2];
                    $qty = $value[3];
                    $satuan = $value[4];
                    $harga_beli = $value[5];
                    $diskon = $value[6];
                    $diskon_persen = $value[7];
                    $hpp = $value[8];
                    $total_laba = $value[9];
                    //Cari data barang/barangnya
                    $barang = $this->barang->where('id_barang', $id_barang)->first();
                    $stok = $barang['stok'];
                    if ($stok == 0) {
                        $stokHabis = array(
                            'id_barang' => $id_barang,
                            'active' => 0,
                        );
                    } else {
                        $stokHabis = array(
                            'id_barang' => $id_barang,
                            'active' => 1,
                        );
                    }
                    array_push($arrStokHabis, $stokHabis);
                }
                $dataStokHabis = $arrStokHabis;
                $this->barang->updateBatch($dataStokHabis, 'id_barang');

                //Data Pajak - Ambil besaran PPN Toko
                if ($toko['PPN'] > 0) :
                    //Ambil kode Pajak
                    $kdPajak = $toko['kode_pajak'];
                    if ($kdJualTahun == '1') {
                        $kodePajak = $kdPajak . date('dmy') . '-' . $timestamp;
                    } else {
                        $kodePajak = $kdPajak . $timestamp;
                    }
                    $dataPajak = [
                        'faktur' =>  $kodePajak,
                        'PPN' => $ppn,
                        'jenis' => 'Keluaran',
                        'nominal' => $pajak,
                        'pembulatan' => $pembulatan,
                        'keterangan' => 'Penjualan: ' . $kodeNota,
                        'id_toko' => 1,
                        'id_login' => session()->get('id'),
                        'id_penjualan' => $idPenjualan,
                    ];
                    //Save Pajak
                    $this->pajak->save($dataPajak);
                    //Save Log
                    $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Save Pajak: ' . $kodePajak]);
                endif;

                //Update penjualan kembali menghitung kurangnya berdasarkan $idPenjualan
                $penjualanKembali = [
                    'kembali' => $hitung
                ];
                $this->model->update($idPenjualan, $penjualanKembali);

                //Belum Bayar Masukkan ke Piutang
                $dataPiutang = [
                    'id_penjualan' =>  $idPenjualan,
                    'tanggal' => $tanggal,
                    'jatuh_tempo' => $jatuhtempo,
                    'jumlah_piutang' => $piutang,
                    'jumlah_bayar' => 0,
                    'sisa_piutang' => $piutang,
                    'status_piutang' => 0,
                    'keterangan' => $keterangan,
                    'id_login' => session()->get('id'),
                    'id_toko' => 1
                ];
                //Save Piutang
                $this->piutang->save($dataPiutang);
                $idPiutang =  $this->piutang->getInsertID();
                //Save Log
                $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Save Piutang, ID Faktur: ' . $idPenjualan]);

                //Jika mengisi nominal Bayar
                if ($bayar != 0) {
                    if ($toko['pembulatan_keatas'] == 1) {
                        $nominalKas = ($total - $pajak) - $piutang;
                    } else {
                        $nominalKas = ($total - $pajak) - $piutang - ($pembulatan);
                        //Simpan Pembulatan Kebawah karena Total dikurangi
                        //Data Cashflow
                        $dataKas = [
                            'faktur' => $kodeNota,
                            'jenis' => 'Pengeluaran',
                            'kategori' => 'Penjualan',
                            'tanggal' => date('Y-m-d', strtotime(Time::now())),
                            'waktu' => date('H:i:s', strtotime(Time::now())),
                            'pemasukan' => 0,
                            'pengeluaran' => abs($pembulatan),
                            'keterangan' => 'Pembulatan Kebawah: ' . $kodeNota,
                            'id_penjualan' => $idPenjualan,
                            'id_toko' => 1,
                            'id_login' => session()->get('id'),
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => null
                        ];
                        //Save Kas
                        $this->cashflow->save($dataKas);
                        //Save Log
                        $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Save Cashflow Pembulatan: ' . $kodeNota]);
                    }
                    $dataKas = [
                        'faktur' => $kodeNota,
                        'jenis' => 'Pemasukan',
                        'kategori' => 'Penjualan',
                        'tanggal' => date('Y-m-d', strtotime(Time::now())),
                        'waktu' => date('H:i:s', strtotime(Time::now())),
                        'pemasukan' => $nominalKas,
                        'pengeluaran' => 0,
                        'keterangan' => 'Penjualan: ' . $kodeNota . '. Piutang ID: ' . $idPiutang,
                        'id_penjualan' => $idPenjualan,
                        'id_toko' => 1,
                        'id_login' => session()->get('id'),
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => null
                    ];
                    //Save Kas
                    $this->cashflow->save($dataKas);
                    //Save Log
                    $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Save Cashflow: ' . $kodeNota]);
                }

                $response = [
                    'status' => true,
                    'message' => lang('App.saveSuccess') . ". " . "Piutang: " . $keterangan,
                    'data' => ['id_penjualan' => $idPenjualan],
                ];
                return $this->respond($response, 200);
            } else {
                $response = [
                    'status' => false,
                    'message' => lang('App.fullBayar'),
                    'data' => [],
                ];
                return $this->respond($response, 200);
            }
        }
    }

    public function create2()
    {
        $rules = [
            'bayar' => [
                'rules'  => 'required',
                'errors' => []
            ],
            'noref_nokartu' => [
                'rules'  => 'required',
                'errors' => []
            ],
            'id_kontak' => [
                'rules'  => 'required',
                'errors' => []
            ],
        ];

        if ($this->request->getJSON()) {
            $json = $this->request->getJSON();
            $totalhpp = $json->hpp;
            $subtotal = $json->subtotal;
            $totaldiskon = $json->diskon;
            $diskonpersen = $json->diskon_persen;
            $pembulatan = $json->pembulatan;
            $total = $json->total;
            $bayar = $json->bayar;
            $kembali = $json->kembali;
            $idkontak = $json->id_kontak;
            $ppn = $json->ppn;
            $pajak = $json->pajak;
            $totalLaba = $subtotal - $totalhpp;
            $noRefnoKartu = $json->noref_nokartu;
            $metodeBayar = $json->metode_bayar;
            $catatan = $json->catatan;
        } else {
            $totalhpp = $this->request->getPost('hpp');
            $subtotal = $this->request->getPost('subtotal');
            $totaldiskon = $this->request->getPost('diskon');
            $diskonpersen = $this->request->getPost('diskon_persen');
            $pembulatan = $this->request->getPost('pembulatan');
            $total = $this->request->getPost('total');
            $bayar = $this->request->getPost('bayar');
            $kembali = $this->request->getPost('kembali');
            $idkontak = $this->request->getPost('id_kontak');
            $ppn = $this->request->getPost('ppn');
            $pajak = $this->request->getPost('pajak');
            $totalLaba = $subtotal - $totalhpp;
            $noRefnoKartu = $this->request->getPost('noref_nokartu');
            $metodeBayar = $this->request->getPost('metode_bayar');
            $catatan = $this->request->getPost('catatan');
        }

        if (!$this->validate($rules)) {
            $response = [
                'status' => false,
                'message' => lang('App.isRequired'),
                'data' => $this->validator->getErrors(),
            ];
            return $this->respond($response, 200);
        } else {
            //Hitung
            $hitung = $bayar - $total;
            if ($bayar == $total) {
                //Ambil request post data
                $input = $this->request->getVar('data');
                foreach ($input as $value) {
                    $id_barang[] = $value[0];
                    $harga_jual[] = $value[1];
                    $stok[] = $value[2];
                    $jumlah[] = $value[3];
                    $satuan[] = $value[4];
                    $harga_beli[] = $value[5];
                    $diskon[] = $value[6];
                    $diskon_persen[] = $value[7];
                    $hpp[] = $value[8];
                    $total_laba[] = $value[9];
                }
                $total_barang = count($id_barang);

                //Ambil Data Toko
                $toko = $this->toko->first();
                //Ambil kode jual toko
                $kdJual = $toko['kode_jual'];
                $kdJualTahun = $toko['kode_jual_tahun'];
                //Ambil timestamp
                $time = Time::now();
                if ($time->getHour() < 10) {
                    $getHour = '0' . $time->getHour();
                } else {
                    $getHour = $time->getHour();
                }
                if ($time->getMinute() < 10) {
                    $getMinute = '0' . $time->getMinute();
                } else {
                    $getMinute = $time->getMinute();
                }
                if ($time->getSecond() < 10) {
                    $getSecond = '0' . $time->getSecond();
                } else {
                    $getSecond = $time->getSecond();
                }
                $timestamp = $getHour . $getMinute . $getSecond;

                if ($kdJualTahun == '1') {
                    //Hitung transaksi tgl-bulan-tahun total tambah 1
                    $query = $this->model->select('DATE(created_at) as date_val, COUNT(*) as total')->groupBy('DATE(created_at)');
                    $hasil = $query->get()->getRowArray();
                    if (empty($hasil)) {
                        $last = 1;
                    } else {
                        $last = $hasil['total'] + 1;
                    }
                    $lastKode = sprintf('%02s', $last);
    
                    $kodeNota = $kdJual . random_string('numeric', 3) . date('dmy') . '-' . $lastKode;
                } else {
                    //Hitung transaksi bulan-tahun total tambah 1
                    $query = $this->model->select('YEAR(created_at) as year_val, MONTH(created_at) as month_val, COUNT(*) as total')->groupBy('YEAR(created_at), MONTH(created_at)');
                    $hasil = $query->get()->getRowArray();
                    if (empty($hasil)) {
                        $last = 1;
                    } else {
                        $last = $hasil['total'] + 1;
                    }
                    $lastKode = sprintf('%02s', $last);
    
                    $kodeNota = $kdJual . random_string('numeric', 3) . date('my') . '-' . $lastKode;
                }
                
                $dataNota = [
                    'faktur' => $kodeNota,
                    'id_kontak' => $idkontak,
                    'jumlah' => $total_barang,
                    'PPN' => $ppn,
                    'hpp' => $totalhpp,
                    'subtotal' => $subtotal,
                    'diskon' => $totaldiskon,
                    'diskon_persen' => $diskonpersen,
                    'pajak' => $pajak,
                    'pembulatan' => $pembulatan,
                    'total' => $total,
                    'bayar' => $bayar,
                    'kembali' => $kembali,
                    'total_laba' => $totalLaba,
                    'periode' => date('m-Y'),
                    'id_login' => session()->get('id'),
                    'id_toko' => 1,
                    'metode_bayar' => $metodeBayar,
                    'catatan' => $catatan,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => null
                ];
                //Save Nota
                $this->model->save($dataNota);
                $idPenjualan = $this->model->getInsertID();
                //Save Log
                $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Save Penjualan - Bank: ' . $kodeNota]);

                $arrNota = array();
                foreach ($input as $key => $value) {
                    $id_barang = $value[0];
                    $harga_jual = $value[1];
                    $stok = $value[2];
                    $qty = $value[3];
                    $satuan = $value[4];
                    $harga_beli = $value[5];
                    $diskon = $value[6];
                    $diskon_persen = $value[7];
                    $hpp = $value[8];
                    $total_laba = $value[9];
                    if ($toko['include_ppn'] == 1) {
                        $total = ((int)$harga_jual - (int)$diskon) * $qty;
                        $ppn = ($toko['PPN'] / 100);
                        $pajak = $total * $ppn;
                        $jumlah = ((int)$harga_jual - (int)$diskon) * $qty - $pajak;
                    } else {
                        $jumlah = ((int)$harga_jual - (int)$diskon) * $qty;
                        $ppn = ($toko['PPN'] / 100);
                        $pajak = $jumlah * $ppn;
                    }
                    $item = array(
                        'id_barang' => $id_barang,
                        'id_penjualan' => $idPenjualan,
                        'harga_beli' => $harga_beli,
                        'harga_jual' => $harga_jual,
                        'diskon' => $diskon,
                        'diskon_persen' => $diskon_persen,
                        'stok' => $stok,
                        'qty' => $qty,
                        'satuan' => $satuan,
                        'hpp' => $hpp,
                        'jumlah' => $jumlah,
                        'ppn' => $pajak,
                        'total_laba' => $total_laba,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => null
                    );
                    array_push($arrNota, $item);
                }
                $dataItem = $arrNota;
                $this->itemJual->insertBatch($dataItem);

                //Update stok barang dikurangi qty
                $arrStok = array();
                foreach ($input as $key => $value) {
                    $id_barang = $value[0];
                    $harga_jual = $value[1];
                    $stok = $value[2];
                    $qty = $value[3];
                    $satuan = $value[4];
                    $harga_beli = $value[5];
                    $diskon = $value[6];
                    $diskon_persen = $value[7];
                    $hpp = $value[8];
                    $total_laba = $value[9];
                    $stock = array(
                        'id_barang' => $id_barang,
                        'stok' => $stok - $qty,
                    );
                    array_push($arrStok, $stock);
                }
                $dataStok = $arrStok;
                $this->barang->updateBatch($dataStok, 'id_barang');

                //Update status barang (active = 0) jika stok 0 / habis
                $arrStokHabis = array();
                foreach ($input as $key => $value) {
                    $id_barang = $value[0];
                    $harga_jual = $value[1];
                    $stok = $value[2];
                    $qty = $value[3];
                    $satuan = $value[4];
                    $harga_beli = $value[5];
                    $diskon = $value[6];
                    $diskon_persen = $value[7];
                    $hpp = $value[8];
                    $total_laba = $value[9];
                    //Cari data barang/barangnya
                    $barang = $this->barang->where('id_barang', $id_barang)->first();
                    $stok = $barang['stok'];
                    if ($stok == 0) {
                        $stokHabis = array(
                            'id_barang' => $id_barang,
                            'active' => 0,
                        );
                    } else {
                        $stokHabis = array(
                            'id_barang' => $id_barang,
                            'active' => 1,
                        );
                    }
                    array_push($arrStokHabis, $stokHabis);
                }
                $dataStokHabis = $arrStokHabis;
                $this->barang->updateBatch($dataStokHabis, 'id_barang');

                //Data Pajak - Ambil besaran PPN Toko
                if ($toko['PPN'] > 0) :
                    //Ambil kode Pajak
                    $kdPajak = $toko['kode_pajak'];
                    if ($kdJualTahun == '1') {
                        $kodePajak = $kdPajak . date('dmy') . '-' . $timestamp;
                    } else {
                        $kodePajak = $kdPajak . $timestamp;
                    }
                    $dataPajak = [
                        'faktur' =>  $kodePajak,
                        'PPN' => $ppn,
                        'jenis' => 'Keluaran',
                        'nominal' => $pajak,
                        'pembulatan' => $pembulatan,
                        'keterangan' => 'Penjualan: ' . $kodeNota,
                        'id_toko' => 1,
                        'id_login' => session()->get('id'),
                        'id_penjualan' => $idPenjualan,
                    ];
                    //Save Pajak
                    $this->pajak->save($dataPajak);
                    //Save Log
                    $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Save Pajak: ' . $kodePajak . ' Keterangan: Keluaran Penjualan']);
                endif;

                //Data Bank - Ambil kode Bank
                $kdBank = $toko['kode_bank'];
                $idBankAkun = $toko['id_bank_akun'];

                if ($toko['pembulatan_keatas'] == 1) {
                    $nominalBank = ($total - $pajak);
                } else {
                    $nominalBank = $total - $pajak - ($pembulatan);
                    //Simpan Pembulatan Kebawah karena Total dikurangi
                    // Data Bank
                    $dataBank = [
                        'faktur' => $kodeNota,
                        'jenis' => 'Pengeluaran',
                        'tanggal' => date('Y-m-d', strtotime(Time::now())),
                        'waktu' => date('H:i:s', strtotime(Time::now())),
                        'pemasukan' => 0,
                        'pengeluaran' => abs($pembulatan),
                        'keterangan' => 'Pembulatan Kebawah: ' . $kodeNota,
                        'id_penjualan' => $idPenjualan,
                        'noref_nokartu' => $noRefnoKartu,
                        'id_toko' => 1,
                        'id_login' => session()->get('id'),
                        'id_bank_akun' => $idBankAkun,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => null
                    ];
                    //Save Bank
                    $this->bank->save($dataBank);
                    //Save Log
                    $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Save Bank Pembulatan: ' . $kodeNota]);
                }
                $dataBank = [
                    'faktur' => $kodeNota,
                    'jenis' => 'Pemasukan',
                    'tanggal' => date('Y-m-d', strtotime(Time::now())),
                    'waktu' => date('H:i:s', strtotime(Time::now())),
                    'pemasukan' => $nominalBank,
                    'pengeluaran' => 0,
                    'keterangan' => 'Penjualan: ' . $kodeNota,
                    'id_penjualan' => $idPenjualan,
                    'noref_nokartu' => $noRefnoKartu,
                    'id_toko' => 1,
                    'id_login' => session()->get('id'),
                    'id_bank_akun' => $idBankAkun,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => null
                ];
                //Simpan Data Bank
                $this->bank->save($dataBank);
                //Save Log
                $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Save Bank: ' . $kodeNota . ' Keterangan: Pemasukan Penjualan']);

                $response = [
                    'status' => true,
                    'message' => lang('App.saveSuccess'),
                    'data' => ['id_penjualan' => $idPenjualan],
                ];
                return $this->respond($response, 200);
            } else {
                $response = [
                    'status' => false,
                    'message' => lang('App.notcorrectPayment') . ' +- ' . $hitung,
                    'data' => [],
                ];
                return $this->respond($response, 200);
            }
        }
    }

    public function update($id = NULL)
    {
        $rules = [
            'bayar' => [
                'rules'  => 'required',
                'errors' => []
            ],
            'id_kontak' => [
                'rules'  => 'required',
                'errors' => []
            ],
        ];

        if ($this->request->getJSON()) {
            $json = $this->request->getJSON();
            $totalhpp = $json->hpp;
            $subtotal = $json->subtotal;
            $totaldiskon = $json->diskon;
            $diskonpersen = $json->diskon_persen;
            $pembulatan = $json->pembulatan;
            $total = $json->total;
            $bayar = $json->bayar;
            $kembali = $json->kembali;
            $idkontak = $json->id_kontak;
            $ppn = $json->ppn;
            $pajak = $json->pajak;
            $totalLaba = $subtotal - $totalhpp;
            $jatuhtempo = $json->jatuh_tempo;
            $metodeBayar = $json->metode_bayar;
            $catatan = $json->catatan;
        } else {
            $totalhpp = $this->request->getPost('hpp');
            $subtotal = $this->request->getPost('subtotal');
            $totaldiskon = $this->request->getPost('diskon');
            $diskonpersen = $this->request->getPost('diskon_persen');
            $pembulatan = $this->request->getPost('pembulatan');
            $total = $this->request->getPost('total');
            $bayar = $this->request->getPost('bayar');
            $kembali = $this->request->getPost('kembali');
            $idkontak = $this->request->getPost('id_kontak');
            $ppn = $this->request->getPost('ppn');
            $pajak = $this->request->getPost('pajak');
            $totalLaba = $subtotal - $totalhpp;
            $jatuhtempo = $this->request->getPost('jatuh_tempo');
            $metodeBayar = $this->request->getPost('metode_bayar');
            $catatan = $this->request->getPost('catatan');
        }

        if (!$this->validate($rules)) {
            $response = [
                'status' => false,
                'message' => lang('App.isRequired'),
                'data' => $this->validator->getErrors(),
            ];
            return $this->respond($response, 200);
        } else {
            if ($bayar != 0) {
                $tanggal = date('Y-m-d');
                $tanggaltempo = $jatuhtempo;
                // Hitung
                $hitung = $bayar - $total;
                $piutang = $total - $bayar;

                // Ambil request post data
                $input = $this->request->getVar('data');
                foreach ($input as $value) {
                    $id_barang[] = $value[0];
                    $harga_jual[] = $value[1];
                    $stok[] = $value[2];
                    $jumlah[] = $value[3];
                    $satuan[] = $value[4];
                    $harga_beli[] = $value[5];
                    $diskon[] = $value[6];
                    $diskon_persen[] = $value[7];
                    $hpp[] = $value[8];
                    $total_laba[] = $value[9];
                }
                $total_barang = count($id_barang);

                // Ambil Data Penjualan
                $penjualan = $this->model->find($id);
                // Ambil Data Toko
                $toko = $this->toko->first();
                // Ambil kode jual toko
                $kdJual = $toko['kode_jual'];
                $kdJualTahun = $toko['kode_jual_tahun'];
                // Ambil timestamp
                $time = Time::now();
                if ($time->getHour() < 10) {
                    $getHour = '0' . $time->getHour();
                } else {
                    $getHour = $time->getHour();
                }
                if ($time->getMinute() < 10) {
                    $getMinute = '0' . $time->getMinute();
                } else {
                    $getMinute = $time->getMinute();
                }
                if ($time->getSecond() < 10) {
                    $getSecond = '0' . $time->getSecond();
                } else {
                    $getSecond = $time->getSecond();
                }
                $timestamp = $getHour . $getMinute . $getSecond;

                $dataNota = [
                    'id_kontak' => $idkontak,
                    'jumlah' => $total_barang,
                    'PPN' => $ppn,
                    'hpp' => $totalhpp,
                    'subtotal' => $subtotal,
                    'diskon' => $totaldiskon,
                    'diskon_persen' => $diskonpersen,
                    'pajak' => $pajak,
                    'pembulatan' => $pembulatan,
                    'total' => $total,
                    'bayar' => $bayar,
                    'kembali' => $kembali,
                    'total_laba' => $totalLaba,
                    'id_login' => session()->get('id'),
                    'metode_bayar' => $metodeBayar,
                    'catatan' => $catatan,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                $this->model->update($id, $dataNota);

                // Delete item penjualan lama
                $item = $this->itemJual->where('id_penjualan', $id)->findAll();
                foreach ($item as $row) {
                    $idItemJual = $row['id_itempenjualan'];
                    $this->itemJual->delete($idItemJual);
                }
                // Insert item penjualan baru
                $arrNota = array();
                foreach ($input as $key => $value) {
                    $id_barang = $value[0];
                    $harga_jual = $value[1];
                    $stok = $value[2];
                    $qty = $value[3];
                    $satuan = $value[4];
                    $harga_beli = $value[5];
                    $diskon = $value[6];
                    $diskon_persen = $value[7];
                    $hpp = $value[8];
                    $total_laba = $value[9];
                    if ($toko['include_ppn'] == 1) {
                        $total = ((int)$harga_jual - (int)$diskon) * $qty;
                        $ppn = ($toko['PPN'] / 100);
                        $pajak = $total * $ppn;
                        $jumlah = ((int)$harga_jual - (int)$diskon) * $qty - $pajak;
                    } else {
                        $jumlah = ((int)$harga_jual - (int)$diskon) * $qty;
                        $ppn = ($toko['PPN'] / 100);
                        $pajak = $jumlah * $ppn;
                    }
                    $item = array(
                        'id_barang' => $id_barang,
                        'id_penjualan' => $id,
                        'harga_beli' => $harga_beli,
                        'harga_jual' => $harga_jual,
                        'diskon' => $diskon,
                        'diskon_persen' => $diskon_persen,
                        'stok' => $stok,
                        'qty' => $qty,
                        'satuan' => $satuan,
                        'hpp' => $hpp,
                        'jumlah' => $jumlah,
                        'ppn' => $pajak,
                        'total_laba' => $total_laba,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => null
                    );
                    array_push($arrNota, $item);
                }
                $dataItem = $arrNota;
                $this->itemJual->insertBatch($dataItem);

                // Update stok barang dikurangi qty
                $arrStok = array();
                foreach ($input as $key => $value) {
                    $id_barang = $value[0];
                    $harga_jual = $value[1];
                    $stok = $value[2];
                    $qty = $value[3];
                    $satuan = $value[4];
                    $harga_beli = $value[5];
                    $diskon = $value[6];
                    $diskon_persen = $value[7];
                    $hpp = $value[8];
                    $total_laba = $value[9];
                    $stock = array(
                        'id_barang' => $id_barang,
                        'stok' => $stok - $qty,
                    );
                    array_push($arrStok, $stock);
                }
                $dataStok = $arrStok;
                $this->barang->updateBatch($dataStok, 'id_barang');

                // Update status barang (active = 0) jika stok 0 / habis
                $arrStokHabis = array();
                foreach ($input as $key => $value) {
                    $id_barang = $value[0];
                    $harga_jual = $value[1];
                    $stok = $value[2];
                    $qty = $value[3];
                    $satuan = $value[4];
                    $harga_beli = $value[5];
                    $diskon = $value[6];
                    $diskon_persen = $value[7];
                    $hpp = $value[8];
                    $total_laba = $value[9];
                    // Cari data barang/barangnya
                    $barang = $this->barang->where('id_barang', $id_barang)->first();
                    $stok = $barang['stok'];
                    if ($stok == 0) {
                        $stokHabis = array(
                            'id_barang' => $id_barang,
                            'active' => 0,
                        );
                    } else {
                        $stokHabis = array(
                            'id_barang' => $id_barang,
                            'active' => 1,
                        );
                    }
                    array_push($arrStokHabis, $stokHabis);
                }
                $dataStokHabis = $arrStokHabis;
                $this->barang->updateBatch($dataStokHabis, 'id_barang');

                // Data Pajak
                if ($toko['PPN'] > 0) {
                    $qPajak = $this->pajak->where('id_penjualan', $id)->first();
                    if (empty($qPajak)) {
                        // Ambil kode Pajak
                        $kdPajak = $toko['kode_pajak'];
                        if ($kdJualTahun == '1') {
                            $kodePajak = $kdPajak . date('dmy') . '-' . $timestamp;
                        } else {
                            $kodePajak = $kdPajak . $timestamp;
                        }
                        $dataPajak = [
                            'faktur' =>  $kodePajak,
                            'PPN' => $toko['PPN'],
                            'jenis' => 'Keluaran',
                            'nominal' => $pajak,
                            'pembulatan' => $pembulatan,
                            'keterangan' => 'Penjualan: ' . $penjualan['faktur'],
                            'id_toko' => 1,
                            'id_login' => session()->get('id'),
                            'id_penjualan' => $id,
                        ];
                        // Save Pajak
                        $this->pajak->save($dataPajak);

                        // Save Log
                        $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Save Pajak: ' . $kodePajak . ' Keterangan: Keluaran Penjualan']);
                    } else {
                        $idPajak = $qPajak['id_pajak'];
                        $dataPajak = [
                            'PPN' => $toko['PPN'],
                            'jenis' => 'Keluaran',
                            'nominal' => $pajak,
                            'pembulatan' => $pembulatan,
                            'id_login' => session()->get('id'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ];
                        // Save Pajak
                        $this->pajak->update($idPajak, $dataPajak);

                        // Save Log
                        $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Update Pajak: ' . $qPajak['id_pajak'] . ' Keterangan: Keluaran Penjualan']);
                    }
                } else {
                    $qPajak = $this->pajak->where('id_penjualan', $id)->first();
                    if (!empty($qPajak)) {
                        $idPajak = $qPajak['id_pajak'];
                        // Delete Pajak
                        $this->pajak->delete($idPajak);

                        // Save Log
                        $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Delete Pajak: ' . $qPajak['id_pajak'] . ' Keterangan: Update Penjualan']);
                    }
                }

                $qBank = $this->bank->where('id_penjualan', $id)->first();
                if (!empty($qBank)) {
                    $idBank = $qBank['id_bank'];
                    // Delete Pajak
                    $this->bank->delete($idBank);

                    // Save Log
                    $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Delete Bank: ' . $idBank . ' Keterangan: Update Penjualan']);
                }

                //Jika Lunas
                if ($bayar >= $total) {
                    $qCashflow = $this->cashflow->where('faktur', $penjualan['faktur'])->first();
                    if ($toko['pembulatan_keatas'] == 1) {
                        $nominalKas = ($total - $pajak);
                    } else {
                        $nominalKas = $total - $pajak - ($pembulatan);
                        if (empty($qCashflow)) {
                            //Simpan Pembulatan Kebawah karena Total dikurangi
                            //Data Cashflow
                            $dataKas = [
                                'faktur' => $penjualan['faktur'],
                                'jenis' => 'Pengeluaran',
                                'kategori' => 'Penjualan',
                                'tanggal' => date('Y-m-d', strtotime(Time::now())),
                                'waktu' => date('H:i:s', strtotime(Time::now())),
                                'pemasukan' => 0,
                                'pengeluaran' => abs($pembulatan),
                                'keterangan' => 'Pembulatan Kebawah: ' . $penjualan['faktur'],
                                'id_penjualan' => $id,
                                'id_toko' => 1,
                                'id_login' => session()->get('id'),
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => null
                            ];
                            //Save Kas
                            $this->cashflow->save($dataKas);
                            //Save Log
                            $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Save Cashflow Pembulatan: ' . $penjualan['faktur']]);
                        } else {
                            // Simpan Pembulatan Kebawah karena Total dikurangi
                            // Data Cashflow
                            $idKas = $qCashflow['id_cashflow'];
                            $dataKas = [
                                'pemasukan' => 0,
                                'pengeluaran' => abs($pembulatan),
                                'id_login' => session()->get('id'),
                                'updated_at' => date('Y-m-d H:i:s')
                            ];
                            // Save Kas
                            $this->cashflow->update($idKas, $dataKas);
                            // Save Log
                            $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Save Cashflow Pembulatan: ' . $penjualan['faktur']]);
                        }
                    }
                    if (empty($qCashflow)) {
                        $dataKas = [
                            'faktur' => $penjualan['faktur'],
                            'jenis' => 'Pemasukan',
                            'kategori' => 'Penjualan',
                            'tanggal' => date('Y-m-d', strtotime(Time::now())),
                            'waktu' => date('H:i:s', strtotime(Time::now())),
                            'pemasukan' => $nominalKas,
                            'pengeluaran' => 0,
                            'keterangan' => 'Penjualan: ' . $penjualan['faktur'],
                            'id_penjualan' => $id,
                            'id_toko' => 1,
                            'id_login' => session()->get('id'),
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => null
                        ];
                        //Save Cashflow
                        $this->cashflow->save($dataKas);

                        //Save Log
                        $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Save Cashflow: ' . $penjualan['faktur']]);
                    } else {
                        $idKas = $qCashflow['id_cashflow'];
                        $dataKas = [
                            'pemasukan' => $nominalKas,
                            'pengeluaran' => 0,
                            'id_login' => session()->get('id'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ];
                        // Save Cashflow
                        $this->cashflow->update($idKas, $dataKas);
                        // Save Log
                        $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Update Cashflow: ' . $penjualan['faktur']]);
                    }

                    // Cek Piutang
                    $qPiutang = $this->piutang->where('id_penjualan', $id)->first();
                    if (!empty($qPiutang)) {
                        $idPiutang = $qPiutang['id_piutang'];
                        // Delete Piutang
                        $this->piutang->delete($idPiutang);
                    }
                } else {
                    $qPiutang = $this->piutang->where('id_penjualan', $id)->first();
                    if (empty($qPiutang)) {
                        //Bayar kurang Masukkan ke Piutang
                        $dataPiutang = [
                            'id_penjualan' => $id,
                            'tanggal' => $tanggal,
                            'jatuh_tempo' => $tanggaltempo,
                            'jumlah_piutang' => $piutang,
                            'jumlah_bayar' => 0,
                            'sisa_piutang' => $piutang,
                            'status_piutang' => 0,
                            'keterangan' => '',
                            'id_login' => session()->get('id'),
                            'id_toko' => 1,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => null
                        ];
                        // Save Piutang
                        $this->piutang->save($dataPiutang);
                        $idPiutang =  $this->piutang->getInsertID();
                        // Save Log
                        $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Save Piutang: ' . $penjualan['faktur']]);
                    } else {
                        $idPiutang = $qPiutang['id_piutang'];
                        $dataPiutang = [
                            'jumlah_piutang' => $piutang,
                            'jumlah_bayar' => 0,
                            'sisa_piutang' => $piutang,
                            'status_piutang' => 0,
                            'keterangan' => '',
                            'id_login' => session()->get('id'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ];
                        // Save Piutang
                        $this->piutang->update($idPiutang, $dataPiutang);
                    }
                }

                // Save Log
                $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Update Penjualan: ' . $penjualan['faktur']]);

                $response = [
                    'status' => true,
                    'message' => lang('App.updSuccess'),
                    'data' => [],
                ];
                return $this->respond($response, 200);
            } else {
                $response = [
                    'status' => false,
                    'message' => lang('App.selectBayar'),
                    'data' => [],
                ];
                return $this->respond($response, 200);
            }
        }
    }

    public function update1($id = NULL)
    {
        $rules = [
            'bayar' => [
                'rules'  => 'required',
                'errors' => []
            ],
            'jatuh_tempo' => [
                'rules'  => 'required',
                'errors' => []
            ],
            'keterangan' => [
                'rules'  => 'required',
                'errors' => []
            ],
            'id_kontak' => [
                'rules'  => 'required',
                'errors' => []
            ],
        ];

        if ($this->request->getJSON()) {
            $json = $this->request->getJSON();
            $totalhpp = $json->hpp;
            $subtotal = $json->subtotal;
            $totaldiskon = $json->diskon;
            $diskonpersen = $json->diskon_persen;
            $pembulatan = $json->pembulatan;
            $total = $json->total;
            $bayar = $json->bayar;
            $kembali = $json->kembali;
            $idkontak = $json->id_kontak;
            $ppn = $json->ppn;
            $pajak = $json->pajak;
            $totalLaba = $subtotal - $totalhpp;
            $jatuhtempo = $json->jatuh_tempo;
            $keterangan = $json->keterangan;
            $metodeBayar = $json->metode_bayar;
            $catatan = $json->catatan;
            
        } else {
            $totalhpp = $this->request->getPost('hpp');
            $subtotal = $this->request->getPost('subtotal');
            $totaldiskon = $this->request->getPost('diskon');
            $diskonpersen = $this->request->getPost('diskon_persen');
            $pembulatan = $this->request->getPost('pembulatan');
            $total = $this->request->getPost('total');
            $bayar = $this->request->getPost('bayar');
            $kembali = $this->request->getPost('kembali');
            $idkontak = $this->request->getPost('id_kontak');
            $ppn = $this->request->getPost('ppn');
            $pajak = $this->request->getPost('pajak');
            $totalLaba = $subtotal - $totalhpp;
            $jatuhtempo = $this->request->getPost('jatuh_tempo');
            $keterangan = $this->request->getPost('keterangan');
            $metodeBayar = $this->request->getPost('metode_bayar');
            $catatan = $this->request->getPost('catatan');
        }

        if (!$this->validate($rules)) {
            $response = [
                'status' => false,
                'message' => lang('App.isRequired'),
                'data' => $this->validator->getErrors(),
            ];
            return $this->respond($response, 200);
        } else {
            if ($bayar < $total) {
                $tanggal = date('Y-m-d');
                $tanggaltempo = $jatuhtempo;
                // Hitung
                $hitung = $bayar - $total;
                $piutang = $total - $bayar;

                // Ambil request post data
                $input = $this->request->getVar('data');
                foreach ($input as $value) {
                    $id_barang[] = $value[0];
                    $harga_jual[] = $value[1];
                    $stok[] = $value[2];
                    $jumlah[] = $value[3];
                    $satuan[] = $value[4];
                    $harga_beli[] = $value[5];
                    $diskon[] = $value[6];
                    $diskon_persen[] = $value[7];
                    $hpp[] = $value[8];
                    $total_laba[] = $value[9];
                }
                $total_barang = count($id_barang);

                // Ambil Data Penjualan
                $penjualan = $this->model->find($id);
                // Ambil Data Toko
                $toko = $this->toko->first();
                // Ambil kode jual toko
                $kdJual = $toko['kode_jual'];
                $kdJualTahun = $toko['kode_jual_tahun'];
                // Ambil timestamp
                $time = Time::now();
                if ($time->getHour() < 10) {
                    $getHour = '0' . $time->getHour();
                } else {
                    $getHour = $time->getHour();
                }
                if ($time->getMinute() < 10) {
                    $getMinute = '0' . $time->getMinute();
                } else {
                    $getMinute = $time->getMinute();
                }
                if ($time->getSecond() < 10) {
                    $getSecond = '0' . $time->getSecond();
                } else {
                    $getSecond = $time->getSecond();
                }
                $timestamp = $getHour . $getMinute . $getSecond;

                $dataNota = [
                    'id_kontak' => $idkontak,
                    'jumlah' => $total_barang,
                    'PPN' => $ppn,
                    'hpp' => $totalhpp,
                    'subtotal' => $subtotal,
                    'diskon' => $totaldiskon,
                    'diskon_persen' => $diskonpersen,
                    'pajak' => $pajak,
                    'pembulatan' => $pembulatan,
                    'total' => $total,
                    'bayar' => $bayar,
                    'kembali' => $kembali,
                    'total_laba' => $totalLaba,
                    'id_login' => session()->get('id'),
                    'metode_bayar' => $metodeBayar,
                    'catatan' => $catatan,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                $this->model->update($id, $dataNota);

                // Delete item penjualan lama
                $item = $this->itemJual->where('id_penjualan', $id)->findAll();
                foreach ($item as $row) {
                    $idItemJual = $row['id_itempenjualan'];
                    $this->itemJual->delete($idItemJual);
                }
                // Insert item penjualan baru
                $arrNota = array();
                foreach ($input as $key => $value) {
                    $id_barang = $value[0];
                    $harga_jual = $value[1];
                    $stok = $value[2];
                    $qty = $value[3];
                    $satuan = $value[4];
                    $harga_beli = $value[5];
                    $diskon = $value[6];
                    $diskon_persen = $value[7];
                    $hpp = $value[8];
                    $total_laba = $value[9];
                    if ($toko['include_ppn'] == 1) {
                        $total = ((int)$harga_jual - (int)$diskon) * $qty;
                        $ppn = ($toko['PPN'] / 100);
                        $pajak = $total * $ppn;
                        $jumlah = ((int)$harga_jual - (int)$diskon) * $qty - $pajak;
                    } else {
                        $jumlah = ((int)$harga_jual - (int)$diskon) * $qty;
                        $ppn = ($toko['PPN'] / 100);
                        $pajak = $jumlah * $ppn;
                    }
                    $item = array(
                        'id_barang' => $id_barang,
                        'id_penjualan' => $id,
                        'harga_beli' => $harga_beli,
                        'harga_jual' => $harga_jual,
                        'diskon' => $diskon,
                        'diskon_persen' => $diskon_persen,
                        'stok' => $stok,
                        'qty' => $qty,
                        'satuan' => $satuan,
                        'hpp' => $hpp,
                        'jumlah' => $jumlah,
                        'ppn' => $pajak,
                        'total_laba' => $total_laba,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => null
                    );
                    array_push($arrNota, $item);
                }
                $dataItem = $arrNota;
                $this->itemJual->insertBatch($dataItem);

                // Update stok barang dikurangi qty
                $arrStok = array();
                foreach ($input as $key => $value) {
                    $id_barang = $value[0];
                    $harga_jual = $value[1];
                    $stok = $value[2];
                    $qty = $value[3];
                    $satuan = $value[4];
                    $harga_beli = $value[5];
                    $diskon = $value[6];
                    $diskon_persen = $value[7];
                    $hpp = $value[8];
                    $total_laba = $value[9];
                    $stock = array(
                        'id_barang' => $id_barang,
                        'stok' => $stok - $qty,
                    );
                    array_push($arrStok, $stock);
                }
                $dataStok = $arrStok;
                $this->barang->updateBatch($dataStok, 'id_barang');

                // Update status barang (active = 0) jika stok 0 / habis
                $arrStokHabis = array();
                foreach ($input as $key => $value) {
                    $id_barang = $value[0];
                    $harga_jual = $value[1];
                    $stok = $value[2];
                    $qty = $value[3];
                    $satuan = $value[4];
                    $harga_beli = $value[5];
                    $diskon = $value[6];
                    $diskon_persen = $value[7];
                    $hpp = $value[8];
                    $total_laba = $value[9];
                    // Cari data barang/barangnya
                    $barang = $this->barang->where('id_barang', $id_barang)->first();
                    $stok = $barang['stok'];
                    if ($stok == 0) {
                        $stokHabis = array(
                            'id_barang' => $id_barang,
                            'active' => 0,
                        );
                    } else {
                        $stokHabis = array(
                            'id_barang' => $id_barang,
                            'active' => 1,
                        );
                    }
                    array_push($arrStokHabis, $stokHabis);
                }
                $dataStokHabis = $arrStokHabis;
                $this->barang->updateBatch($dataStokHabis, 'id_barang');

                // Data Pajak
                if ($toko['PPN'] > 0) {
                    $qPajak = $this->pajak->where('id_penjualan', $id)->first();
                    if (empty($qPajak)) {
                        // Ambil kode Pajak
                        $kdPajak = $toko['kode_pajak'];
                        if ($kdJualTahun == '1') {
                            $kodePajak = $kdPajak . date('dmy') . '-' . $timestamp;
                        } else {
                            $kodePajak = $kdPajak . $timestamp;
                        }
                        $dataPajak = [
                            'faktur' =>  $kodePajak,
                            'PPN' => $toko['PPN'],
                            'jenis' => 'Keluaran',
                            'nominal' => $pajak,
                            'pembulatan' => $pembulatan,
                            'keterangan' => 'Penjualan: ' . $penjualan['faktur'],
                            'id_toko' => 1,
                            'id_login' => session()->get('id'),
                            'id_penjualan' => $id,
                        ];
                        // Save Pajak
                        $this->pajak->save($dataPajak);

                        // Save Log
                        $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Save Pajak: ' . $kodePajak . ' Keterangan: Keluaran Penjualan']);
                    } else {
                        $idPajak = $qPajak['id_pajak'];
                        $dataPajak = [
                            'PPN' => $toko['PPN'],
                            'jenis' => 'Keluaran',
                            'nominal' => $pajak,
                            'pembulatan' => $pembulatan,
                            'id_login' => session()->get('id'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ];
                        // Save Pajak
                        $this->pajak->update($idPajak, $dataPajak);

                        // Save Log
                        $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Update Pajak: ' . $qPajak['id_pajak'] . ' Keterangan: Keluaran Penjualan']);
                    }
                } else {
                    $qPajak = $this->pajak->where('id_penjualan', $id)->first();
                    if (!empty($qPajak)) {
                        $idPajak = $qPajak['id_pajak'];
                        // Delete Pajak
                        $this->pajak->delete($idPajak);

                        // Save Log
                        $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Delete Pajak: ' . $qPajak['id_pajak'] . ' Keterangan: Update Penjualan']);
                    }
                }

                //Update penjualan kembali menghitung kurangnya berdasarkan $idPenjualan
                $penjualanKembali = [
                    'kembali' => $hitung
                ];
                $this->model->update($id, $penjualanKembali);

                $qPiutang = $this->piutang->where('id_penjualan', $id)->first();
                if (empty($qPiutang)) {
                    //Bayar kurang Masukkan ke Piutang
                    $dataPiutang = [
                        'id_penjualan' => $id,
                        'tanggal' => $tanggal,
                        'jatuh_tempo' => $tanggaltempo,
                        'jumlah_piutang' => $piutang,
                        'jumlah_bayar' => 0,
                        'sisa_piutang' => $piutang,
                        'status_piutang' => 0,
                        'keterangan' => '',
                        'id_login' => session()->get('id'),
                        'id_toko' => 1,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => null
                    ];
                    // Save Piutang
                    $this->piutang->save($dataPiutang);
                    $idPiutang =  $this->piutang->getInsertID();
                    // Save Log
                    $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Save Piutang: ' . $penjualan['faktur']]);
                } else {
                    $idPiutang = $qPiutang['id_piutang'];
                    $dataPiutang = [
                        'jumlah_piutang' => $piutang,
                        'jumlah_bayar' => 0,
                        'sisa_piutang' => $piutang,
                        'status_piutang' => 0,
                        'keterangan' => '',
                        'id_login' => session()->get('id'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                    // Save Piutang
                    $this->piutang->update($idPiutang, $dataPiutang);
                }

                $qCashflow = $this->cashflow->where('faktur', $penjualan['faktur'])->first();
                if (!empty($qCashflow)) {
                    $idKas = $qCashflow['id_cashflow'];
                    // Delete Pajak
                    $this->cashflow->delete($idKas);

                    // Save Log
                    $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Delete Cashflow: ' . $idKas . ' Keterangan: Update Penjualan']);
                }

                $qBank = $this->bank->where('id_penjualan', $id)->first();
                if (!empty($qBank)) {
                    $idBank = $qBank['id_bank'];
                    // Delete Pajak
                    $this->bank->delete($idBank);

                    // Save Log
                    $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Delete Bank: ' . $idBank . ' Keterangan: Update Penjualan']);
                }

                //Jika mengisi nominal Bayar
                if ($bayar != 0) {
                    if ($toko['pembulatan_keatas'] == 1) {
                        $nominalKas = ($total - $pajak) - $piutang;
                    } else {
                        $nominalKas = ($total - $pajak) - $piutang - ($pembulatan);
                        //Simpan Pembulatan Kebawah karena Total dikurangi
                        //Data Cashflow
                        $dataKas = [
                            'faktur' => $penjualan['faktur'],
                            'jenis' => 'Pengeluaran',
                            'kategori' => 'Penjualan',
                            'tanggal' => date('Y-m-d', strtotime(Time::now())),
                            'waktu' => date('H:i:s', strtotime(Time::now())),
                            'pemasukan' => 0,
                            'pengeluaran' => abs($pembulatan),
                            'keterangan' => 'Pembulatan Kebawah: ' . $penjualan['faktur'],
                            'id_penjualan' => $id,
                            'id_toko' => 1,
                            'id_login' => session()->get('id'),
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => null
                        ];
                        // Save Kas
                        $this->cashflow->save($dataKas);
                        // Save Log
                        $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Save Cashflow Pembulatan: ' . $penjualan['faktur']]);
                    }
                    $dataKas = [
                        'faktur' => $penjualan['faktur'],
                        'jenis' => 'Pemasukan',
                        'kategori' => 'Penjualan',
                        'tanggal' => date('Y-m-d', strtotime(Time::now())),
                        'waktu' => date('H:i:s', strtotime(Time::now())),
                        'pemasukan' => $nominalKas,
                        'pengeluaran' => 0,
                        'keterangan' => 'Penjualan: ' . $penjualan['faktur'] . '. Piutang ID: ' . $idPiutang,
                        'id_penjualan' => $id,
                        'id_toko' => 1,
                        'id_login' => session()->get('id'),
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => null
                    ];
                    // Save Kas
                    $this->cashflow->save($dataKas);
                    // Save Log
                    $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Save Cashflow: ' . $penjualan['faktur']]);
                }

                // Save Log
                $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Update Penjualan: ' . $penjualan['faktur']]);

                $response = [
                    'status' => true,
                    'message' => lang('App.saveSuccess') . ". " . "Piutang: " . $keterangan,
                    'data' => [],
                ];
                return $this->respond($response, 200);
            } else {
                $response = [
                    'status' => false,
                    'message' => lang('App.fullBayar'),
                    'data' => [],
                ];
                return $this->respond($response, 200);
            }
        }
    }

    public function update2($id = NULL)
    {
        $rules = [
            'bayar' => [
                'rules'  => 'required',
                'errors' => []
            ],
            'noref_nokartu' => [
                'rules'  => 'required',
                'errors' => []
            ],
            'id_kontak' => [
                'rules'  => 'required',
                'errors' => []
            ],
        ];

        if ($this->request->getJSON()) {
            $json = $this->request->getJSON();
            $totalhpp = $json->hpp;
            $subtotal = $json->subtotal;
            $totaldiskon = $json->diskon;
            $diskonpersen = $json->diskon_persen;
            $pembulatan = $json->pembulatan;
            $total = $json->total;
            $bayar = $json->bayar;
            $kembali = $json->kembali;
            $idkontak = $json->id_kontak;
            $ppn = $json->ppn;
            $pajak = $json->pajak;
            $totalLaba = $subtotal - $totalhpp;
            $jatuhtempo = $json->jatuh_tempo;
            $noRefnoKartu = $json->noref_nokartu;
            $metodeBayar = $json->metode_bayar;
            $catatan = $json->catatan;
        } else {
            $totalhpp = $this->request->getPost('hpp');
            $subtotal = $this->request->getPost('subtotal');
            $totaldiskon = $this->request->getPost('diskon');
            $diskonpersen = $this->request->getPost('diskon_persen');
            $pembulatan = $this->request->getPost('pembulatan');
            $total = $this->request->getPost('total');
            $bayar = $this->request->getPost('bayar');
            $kembali = $this->request->getPost('kembali');
            $idkontak = $this->request->getPost('id_kontak');
            $ppn = $this->request->getPost('ppn');
            $pajak = $this->request->getPost('pajak');
            $totalLaba = $subtotal - $totalhpp;
            $jatuhtempo = $this->request->getPost('jatuh_tempo');
            $noRefnoKartu = $this->request->getPost('noref_nokartu');
            $metodeBayar = $this->request->getPost('metode_bayar');
            $catatan = $this->request->getPost('catatan');
        }

        if (!$this->validate($rules)) {
            $response = [
                'status' => false,
                'message' => lang('App.isRequired'),
                'data' => $this->validator->getErrors(),
            ];
            return $this->respond($response, 200);
        } else {
            // Hitung
            $hitung = $bayar - $total;
            if ($bayar == $total) {
                // Ambil request post data
                $input = $this->request->getVar('data');
                foreach ($input as $value) {
                    $id_barang[] = $value[0];
                    $harga_jual[] = $value[1];
                    $stok[] = $value[2];
                    $jumlah[] = $value[3];
                    $satuan[] = $value[4];
                    $harga_beli[] = $value[5];
                    $diskon[] = $value[6];
                    $diskon_persen[] = $value[7];
                    $hpp[] = $value[8];
                    $total_laba[] = $value[9];
                }
                $total_barang = count($id_barang);

                // Ambil Data Penjualan
                $penjualan = $this->model->find($id);
                // Ambil Data Toko
                $toko = $this->toko->first();
                // Ambil kode jual toko
                $kdJual = $toko['kode_jual'];
                $kdJualTahun = $toko['kode_jual_tahun'];
                // Ambil timestamp
                $time = Time::now();
                if ($time->getHour() < 10) {
                    $getHour = '0' . $time->getHour();
                } else {
                    $getHour = $time->getHour();
                }
                if ($time->getMinute() < 10) {
                    $getMinute = '0' . $time->getMinute();
                } else {
                    $getMinute = $time->getMinute();
                }
                if ($time->getSecond() < 10) {
                    $getSecond = '0' . $time->getSecond();
                } else {
                    $getSecond = $time->getSecond();
                }
                $timestamp = $getHour . $getMinute . $getSecond;

                $dataNota = [
                    'id_kontak' => $idkontak,
                    'jumlah' => $total_barang,
                    'PPN' => $ppn,
                    'hpp' => $totalhpp,
                    'subtotal' => $subtotal,
                    'diskon' => $totaldiskon,
                    'diskon_persen' => $diskonpersen,
                    'pajak' => $pajak,
                    'pembulatan' => $pembulatan,
                    'total' => $total,
                    'bayar' => $bayar,
                    'kembali' => $kembali,
                    'total_laba' => $totalLaba,
                    'id_login' => session()->get('id'),
                    'metode_bayar' => $metodeBayar,
                    'catatan' => $catatan,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                $this->model->update($id, $dataNota);

                // Delete item penjualan lama
                $item = $this->itemJual->where('id_penjualan', $id)->findAll();
                foreach ($item as $row) {
                    $idItemJual = $row['id_itempenjualan'];
                    $this->itemJual->delete($idItemJual);
                }
                // Insert item penjualan baru
                $arrNota = array();
                foreach ($input as $key => $value) {
                    $id_barang = $value[0];
                    $harga_jual = $value[1];
                    $stok = $value[2];
                    $qty = $value[3];
                    $satuan = $value[4];
                    $harga_beli = $value[5];
                    $diskon = $value[6];
                    $diskon_persen = $value[7];
                    $hpp = $value[8];
                    $total_laba = $value[9];
                    if ($toko['include_ppn'] == 1) {
                        $total = ((int)$harga_jual - (int)$diskon) * $qty;
                        $ppn = ($toko['PPN'] / 100);
                        $pajak = $total * $ppn;
                        $jumlah = ((int)$harga_jual - (int)$diskon) * $qty - $pajak;
                    } else {
                        $jumlah = ((int)$harga_jual - (int)$diskon) * $qty;
                        $ppn = ($toko['PPN'] / 100);
                        $pajak = $jumlah * $ppn;
                    }
                    $item = array(
                        'id_barang' => $id_barang,
                        'id_penjualan' => $id,
                        'harga_beli' => $harga_beli,
                        'harga_jual' => $harga_jual,
                        'diskon' => $diskon,
                        'diskon_persen' => $diskon_persen,
                        'stok' => $stok,
                        'qty' => $qty,
                        'satuan' => $satuan,
                        'hpp' => $hpp,
                        'jumlah' => $jumlah,
                        'ppn' => $pajak,
                        'total_laba' => $total_laba,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => null
                    );
                    array_push($arrNota, $item);
                }
                $dataItem = $arrNota;
                $this->itemJual->insertBatch($dataItem);

                // Update stok barang dikurangi qty
                $arrStok = array();
                foreach ($input as $key => $value) {
                    $id_barang = $value[0];
                    $harga_jual = $value[1];
                    $stok = $value[2];
                    $qty = $value[3];
                    $satuan = $value[4];
                    $harga_beli = $value[5];
                    $diskon = $value[6];
                    $diskon_persen = $value[7];
                    $hpp = $value[8];
                    $total_laba = $value[9];
                    $stock = array(
                        'id_barang' => $id_barang,
                        'stok' => $stok - $qty,
                    );
                    array_push($arrStok, $stock);
                }
                $dataStok = $arrStok;
                $this->barang->updateBatch($dataStok, 'id_barang');

                // Update status barang (active = 0) jika stok 0 / habis
                $arrStokHabis = array();
                foreach ($input as $key => $value) {
                    $id_barang = $value[0];
                    $harga_jual = $value[1];
                    $stok = $value[2];
                    $qty = $value[3];
                    $satuan = $value[4];
                    $harga_beli = $value[5];
                    $diskon = $value[6];
                    $diskon_persen = $value[7];
                    $hpp = $value[8];
                    $total_laba = $value[9];
                    // Cari data barang/barangnya
                    $barang = $this->barang->where('id_barang', $id_barang)->first();
                    $stok = $barang['stok'];
                    if ($stok == 0) {
                        $stokHabis = array(
                            'id_barang' => $id_barang,
                            'active' => 0,
                        );
                    } else {
                        $stokHabis = array(
                            'id_barang' => $id_barang,
                            'active' => 1,
                        );
                    }
                    array_push($arrStokHabis, $stokHabis);
                }
                $dataStokHabis = $arrStokHabis;
                $this->barang->updateBatch($dataStokHabis, 'id_barang');

                // Data Pajak
                if ($toko['PPN'] > 0) {
                    $qPajak = $this->pajak->where('id_penjualan', $id)->first();
                    // Ambil kode Pajak
                    $kdPajak = $toko['kode_pajak'];
                    if ($kdJualTahun == '1') {
                        $kodePajak = $kdPajak . date('dmy') . '-' . $timestamp;
                    } else {
                        $kodePajak = $kdPajak . $timestamp;
                    }
                    if (empty($qPajak)) {
                        $dataPajak = [
                            'faktur' =>  $kodePajak,
                            'PPN' => $toko['PPN'],
                            'jenis' => 'Keluaran',
                            'nominal' => $pajak,
                            'pembulatan' => $pembulatan,
                            'keterangan' => 'Penjualan: ' . $penjualan['faktur'],
                            'id_toko' => 1,
                            'id_login' => session()->get('id'),
                            'id_penjualan' => $id,
                        ];
                        // Save Pajak
                        $this->pajak->save($dataPajak);

                        // Save Log
                        $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Save Pajak: ' . $kodePajak . ' Keterangan: Keluaran Penjualan']);
                    } else {
                        $idPajak = $qPajak['id_pajak'];
                        $dataPajak = [
                            'PPN' => $toko['PPN'],
                            'jenis' => 'Keluaran',
                            'nominal' => $pajak,
                            'pembulatan' => $pembulatan,
                            'id_login' => session()->get('id'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ];
                        // Save Pajak
                        $this->pajak->update($idPajak, $dataPajak);

                        // Save Log
                        $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Update Pajak: ' . $idPajak . ' Keterangan: Keluaran Penjualan']);
                    }
                } else {
                    $qPajak = $this->pajak->where('id_penjualan', $id)->first();
                    if (!empty($qPajak)) {
                        $idPajak = $qPajak['id_pajak'];
                        // Delete Pajak
                        $this->pajak->delete($idPajak);

                        // Save Log
                        $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Delete Pajak: ' . $idPajak . ' Keterangan: Update Penjualan']);
                    }
                }

                // Periksa data Cashflow
                $qCashflow = $this->cashflow->where('faktur', $penjualan['faktur'])->first();
                if (!empty($qCashflow)) {
                    $idKas = $qCashflow['id_cashflow'];
                    // Delete Pajak
                    $this->cashflow->delete($idKas);
                }

                // Cek Piutang
                $qPiutang = $this->piutang->where('id_penjualan', $id)->first();
                if (!empty($qPiutang)) {
                    $idPiutang = $qPiutang['id_piutang'];
                    // Delete Piutang
                    $this->piutang->delete($idPiutang);

                    // Save Log
                    $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Delete Piutang: ' . $idPiutang . ' Keterangan: Update Penjualan']);
                }

                // Periksa data Bank
                $qBank = $this->bank->where('faktur', $penjualan['faktur'])->first();
                // Data Bank - Ambil kode Bank
                $kdBank = $toko['kode_bank'];
                $idBankAkun = $toko['id_bank_akun'];

                if ($toko['pembulatan_keatas'] == 1) {
                    $nominalBank = ($total - $pajak);
                } else {
                    $nominalBank = $total - $pajak - ($pembulatan);
                    // Simpan Pembulatan Kebawah karena Total dikurangi
                    if (empty($qBank)) {
                        // Data Bank
                        $dataBank = [
                            'faktur' => $penjualan['faktur'],
                            'jenis' => 'Pengeluaran',
                            'tanggal' => date('Y-m-d', strtotime(Time::now())),
                            'waktu' => date('H:i:s', strtotime(Time::now())),
                            'pemasukan' => 0,
                            'pengeluaran' => abs($pembulatan),
                            'keterangan' => 'Pembulatan Kebawah: ' . $penjualan['faktur'],
                            'id_penjualan' => $id,
                            'id_toko' => 1,
                            'id_login' => session()->get('id'),
                            'id_bank_akun' => $idBankAkun,
                            'noref_nokartu' => $noRefnoKartu,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => null
                        ];
                        // Save Bank
                        $this->bank->save($dataBank);
                        // Save Log
                        $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Save Bank Pembulatan: ' . $penjualan['faktur']]);
                    } else {
                        // Data Bank
                        $idBank = $idBank = $qBank['id_bank'];
                        $dataBank = [
                            'tanggal' => date('Y-m-d', strtotime(Time::now())),
                            'waktu' => date('H:i:s', strtotime(Time::now())),
                            'pemasukan' => 0,
                            'pengeluaran' => abs($pembulatan),
                            'id_login' => session()->get('id'),
                            'id_bank_akun' => $idBankAkun,
                            'noref_nokartu' => $noRefnoKartu,
                            'updated_at' => date('Y-m-d H:i:s')
                        ];
                        // Save Bank
                        $this->bank->update($idBank, $dataBank);
                        // Save Log
                        $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Update Bank Pembulatan: ' . $penjualan['faktur']]);
                    }
                }
                if (empty($qBank)) {
                    $dataBank = [
                        'faktur' => $penjualan['faktur'],
                        'jenis' => 'Pemasukan',
                        'tanggal' => date('Y-m-d', strtotime(Time::now())),
                        'waktu' => date('H:i:s', strtotime(Time::now())),
                        'pemasukan' => $nominalBank,
                        'pengeluaran' => 0,
                        'keterangan' => 'Penjualan: ' . $penjualan['faktur'],
                        'id_penjualan' => $id,
                        'id_toko' => 1,
                        'id_login' => session()->get('id'),
                        'id_bank_akun' => $idBankAkun,
                        'noref_nokartu' => $noRefnoKartu,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => null
                    ];
                    // Simpan Data Bank
                    $this->bank->save($dataBank);
                    // Save Log
                    $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Save Bank: ' . $penjualan['faktur'] . ' Keterangan: Update Penjualan']);
                } else {
                    $idBank = $qBank['id_bank'];
                    $dataBank = [
                        'tanggal' => date('Y-m-d', strtotime(Time::now())),
                        'waktu' => date('H:i:s', strtotime(Time::now())),
                        'pemasukan' => $nominalBank,
                        'pengeluaran' => 0,
                        'id_login' => session()->get('id'),
                        'id_bank_akun' => $idBankAkun,
                        'noref_nokartu' => $noRefnoKartu,
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                    // Simpan Data Bank
                    $this->bank->update($idBank, $dataBank);
                    // Save Log
                    $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Update Bank: ' . $penjualan['faktur'] . ' Keterangan: Update Penjualan']);
                }

                $response = [
                    'status' => true,
                    'message' => lang('App.updSuccess'),
                    'data' => [],
                ];
                return $this->respond($response, 200);
            } else {
                $response = [
                    'status' => false,
                    'message' => lang('App.notcorrectPayment') . ' +- ' . $hitung,
                    'data' => [],
                ];
                return $this->respond($response, 200);
            }
        }
    }

    public function delete($id = null)
    {
        $delete = $this->model->find($id);
        $faktur = $delete['faktur'];

        //Cek data Piutang dulu karena akan terkena Foreign key cek 'restrict'
        $cekPiutang = $this->piutang->where(['id_penjualan' => $id])->findAll();
        if ($cekPiutang) :
            $response = [
                'status' => true,
                'message' => lang('App.delFailedPiutang'),
                'data' => [],
            ];
            return $this->respond($response, 200);
        endif;

        if (!$cekPiutang) {
            //Cari data item penjualan
            $item = $this->itemJual->where('id_penjualan', $id)->findAll();
            foreach ($item as $row) {
                $idItemJual = $row['id_itempenjualan'];
                $idBarang = $row['id_barang'];
                $qty = $row['qty'];

                //Cari data barang/barangnya
                $barang = $this->barang->where('id_barang', $idBarang)->first();
                $stok = $barang['stok'];
                $dataStok = [
                    'stok' => $stok + $qty,
                ];
                //Update stok barang/barangnya
                $this->barang->update($idBarang, $dataStok);

                //Hapus item penjualan
                $this->itemJual->delete($idItemJual);
            }

            //Cari data Cashflow
            $cash = $this->cashflow->where('faktur', $faktur)->findAll();
            if ($cash) :
                foreach ($cash as $row) {
                    $idCashflow = $row['id_cashflow'];
                    //Hapus Cashflow
                    $this->cashflow->delete($idCashflow);
                    //Save Log
                    $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Delete Cashflow: ' . $idCashflow]);
                }
            endif;

            //Hapus penjualan
            $this->model->delete($id);
            //Save Log
            $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Delete Penjualan: ' . $faktur]);

            $response = [
                'status' => true,
                'message' => lang('App.delSuccess'),
                'data' => [],
            ];
            return $this->respond($response, 200);
        } else {
            $response = [
                'status' => false,
                'message' => lang('App.delFailed'),
                'data' => [],
            ];
            return $this->respond($response, 200);
        }
    }

    public function cetakNota($id = null)
    {
        return $this->respond(["status" => true, "message" => lang("App.getSuccess"), "data" => $this->itemJual->findNota($id)], 200);
    }

    //Function Ribuan
    function Ribuan($angka)
    {
        $hasil_rupiah = number_format($angka, 0, ',', '.');
        return $hasil_rupiah;
    }

    public function cetakUSB()
    {
        if ($this->request->getJSON()) {
            $json = $this->request->getJSON();
            $id = $json->id_penjualan;
        } else {
            $id = $this->request->getPost('id_penjualan');
        }

        $toko = $this->toko->first();
        $penjualan = $this->model->getPenjualanById($id);
        $item = $this->itemJual->findNotaCetak($id);

        $tanggal = dayshortdate_indo(date('Y-m-d', strtotime($penjualan['created_at']))) . ' ' . date('H:i', strtotime($penjualan['created_at']));
        $user = session()->get('nama');
        $appname = env('appName');
        $companyname = env('appCompany');

        // Data Toko
        $namaToko = $toko['nama_toko'];
        $alamatToko = $toko['alamat_toko'];
        $telpToko = $toko['telp'];
        $nibToko = $toko['NIB'];
        $paperSize = $toko['paper_size'];
        $footerNota = $toko['footer_nota'];

        // Data Nota
        $faktur = $penjualan['faktur'];
        $subtotal = $this->Ribuan($penjualan['subtotal']);
        $ppn = $penjualan['PPN'];
        $pajak = $this->Ribuan($penjualan['pajak']);
        $diskon = $this->Ribuan($penjualan['diskon']);
        $diskonPersen = $penjualan['diskon_persen'];
        $total = $this->Ribuan($penjualan['total']);
        $bayar = $this->Ribuan($penjualan['bayar']);
        $kembali = $this->Ribuan($penjualan['kembali']);
        $items = $penjualan['jumlah'];
        $kontak = $penjualan['nama_kontak'];
        $member = $penjualan['grup'];
        $pembulatan = $penjualan['pembulatan'];

        // Logo
        $logo = $this->setting->info['img_logo_resize'];
        $img = EscposImage::load("$logo", false, ['native']);

        try {
            /**
             * Install the printer dengan USB printing support (Generic / Text Only driver),
             * Buka Windows Control Panel > Devices and Printers > Printernya > Klik Kanan Printer properties 
             * Klik Tab Sharing > Share Name = Receipt Printer
             * Klik OK
             */
            // Share name dari USB printer sesuai nama di file .env printerShareName
            $printer = env('printerShareName');
            $connector = new WindowsPrintConnector($printer);

            // Mulai Printer
            $printer = new Printer($connector);
            $printer->feed();
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->bitImage($img);
            $printer->text("\n");
            $printer->setFont(Printer::FONT_A);
            $printer->text("$namaToko\n");
            $printer->setFont(Printer::FONT_B);
            if ($nibToko != 0) :
                $printer->text("NIB: $nibToko\n");
            endif;
            $printer->text("$alamatToko\n");
            $printer->text("Telp/WA: $telpToko\n");
            $printer->textRaw(str_repeat('-', 40) . PHP_EOL);
            $printer->setFont(false);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("No: $faktur\n");
            $printer->text("Hr/Tgl: $tanggal\n");
            $printer->text("Kasir: $user\n");
            $printer->text("Customer: $kontak ($member)\n");
            $printer->textRaw(str_repeat('-', 40) . PHP_EOL);
            foreach ($item as $item) {
                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer->text("$item->nama_barang\n");
                $printer->text(str_pad("$item->qty ($item->satuan) x @ {$this->Ribuan($item->harga_jual)}", 20));
                $printer->text(str_pad("{$this->Ribuan($item->jumlah)}", 10, ' ', STR_PAD_LEFT));
                $printer->text("\n");
            }
            $printer->textRaw(str_repeat('-', 40) . PHP_EOL);
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text("Subtotal ($items item): $subtotal\n");
            $printer->text("PPN $ppn%: $pajak\n");
            $printer->text("Diskon $diskonPersen%: $diskon\n");
            if ($toko['pembulatan'] == 1) :
                $printer->text("Pembulatan: $pembulatan\n");
            endif;
            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->text("Total: $total\n");
            $printer->selectPrintMode();
            $printer->text("Bayar: $bayar\n");
            if ($kembali >= 0) {
                $printer->text("Kembali: $kembali\n");
            } else {
                $printer->text("Kurang: $kembali\n");
            }
            $printer->text("\n");
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setFont(Printer::FONT_B);
            $printer->text("$footerNota. Dicetak menggunakan Aplikasi $appname by $companyname\n");
            $printer->feed(2);
            /* Cut printer */
            //$printer->cut();
            /* Tutup printer */
            $printer->close();

            $response = [
                'status' => true,
                'message' => 'Print Success',
                'data' => [],
            ];
            return $this->respond($response, 200);
        } catch (Exception $e) {
            //echo "Couldn't print to this printer: " . $e->getMessage() . "\n";
            $response = [
                'status' => false,
                'message' => "Couldn't print to this printer: " . $e->getMessage() . "\n",
                'data' => [],
            ];
            return $this->respond($response, 200);
        }
    }

    public function cetakBluetooth()
    {
        if ($this->request->getJSON()) {
            $json = $this->request->getJSON();
            $id = $json->id_penjualan;
        } else {
            $id = $this->request->getPost('id_penjualan');
        }

        $toko = $this->toko->first();
        $penjualan = $this->model->getPenjualanById($id);
        $item = $this->itemJual->findNotaCetak($id);

        $tanggal = dayshortdate_indo(date('Y-m-d', strtotime($penjualan['created_at']))) . ' ' . date('H:i', strtotime($penjualan['created_at']));
        $user = session()->get('nama');
        $appname = env('appName');
        $companyname = env('appCompany');

        // Data Toko
        $namaToko = $toko['nama_toko'];
        $alamatToko = $toko['alamat_toko'];
        $telpToko = $toko['telp'];
        $nibToko = $toko['NIB'];
        $paperSize = $toko['paper_size'];
        $footerNota = $toko['footer_nota'];

        // Data Nota
        $faktur = $penjualan['faktur'];
        $subtotal = $this->Ribuan($penjualan['subtotal']);
        $ppn = $penjualan['PPN'];
        $pajak = $this->Ribuan($penjualan['pajak']);
        $diskon = $this->Ribuan($penjualan['diskon']);
        $diskonPersen = $penjualan['diskon_persen'];
        $total = $this->Ribuan($penjualan['total']);
        $bayar = $this->Ribuan($penjualan['bayar']);
        $kembali = $this->Ribuan($penjualan['kembali']);
        $items = $penjualan['jumlah'];
        $kontak = $penjualan['nama_kontak'];
        $member = $penjualan['grup'];
        $pembulatan = $penjualan['pembulatan'];

        // Logo
        $logo = $this->setting->info['img_logo_resize'];
        $img = EscposImage::load("$logo", false, ['native']);

        try {
            $profile = CapabilityProfile::load("POS-5890");

            /* Fill in your own connector here */
            $connector = new RawbtPrintConnector();

            // Mulai Printer
            $printer = new Printer($connector, $profile);
            $printer->feed();
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->bitImage($img);
            $printer->text("\n");
            $printer->setFont(Printer::FONT_A);
            $printer->text("$namaToko\n");
            $printer->setFont(Printer::FONT_B);
            if ($nibToko != 0) :
                $printer->text("NIB: $nibToko\n");
            endif;
            $printer->text("$alamatToko\n");
            $printer->text("Telp/WA: $telpToko\n");
            $printer->textRaw(str_repeat('-', 40) . PHP_EOL);
            $printer->setFont(false);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("No: $faktur\n");
            $printer->text("Hr/Tgl: $tanggal\n");
            $printer->text("Kasir: $user\n");
            $printer->text("Customer: $kontak ($member)\n");
            $printer->textRaw(str_repeat('-', 40) . PHP_EOL);
            foreach ($item as $item) {
                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer->text("$item->nama_barang\n");
                $printer->text(str_pad("$item->qty ($item->satuan) x @ {$this->Ribuan($item->harga_jual)}", 20));
                $printer->text(str_pad("{$this->Ribuan($item->jumlah)}", 10, ' ', STR_PAD_LEFT));
                $printer->text("\n");
            }
            $printer->textRaw(str_repeat('-', 40) . PHP_EOL);
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text("Subtotal ($items item): $subtotal\n");
            $printer->text("PPN $ppn%: $pajak\n");
            $printer->text("Diskon $diskonPersen%: $diskon\n");
            if ($toko['pembulatan'] == 1) :
                $printer->text("Pembulatan: $pembulatan\n");
            endif;
            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->text("Total: $total\n");
            $printer->selectPrintMode();
            $printer->text("Bayar: $bayar\n");
            if ($kembali >= 0) {
                $printer->text("Kembali: $kembali\n");
            } else {
                $printer->text("Kurang: $kembali\n");
            }
            $printer->text("\n");
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setFont(Printer::FONT_B);
            $printer->text("$footerNota. Dicetak menggunakan Aplikasi $appname by $companyname\n");
            $printer->feed(2);
            /* Cut printer */
            //$printer->cut();
            /* Tutup printer */
            $printer->close();

            $response = [
                'status' => true,
                'message' => 'Print Success',
                'data' => [],
            ];
            return $this->respond($response, 200);
        } catch (Exception $e) {
            //echo "Couldn't print to this printer: " . $e->getMessage() . "\n";
            $response = [
                'status' => false,
                'message' => "Couldn't print to this printer: " . $e->getMessage() . "\n",
                'data' => [],
            ];
            return $this->respond($response, 200);
        }
    }
}
