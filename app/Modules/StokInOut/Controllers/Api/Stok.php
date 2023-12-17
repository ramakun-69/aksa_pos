<?php

namespace App\Modules\StokInOut\Controllers\Api;
/*
PT ITSHOP BISNIS DIGITAL
Website: https://itshop.biz.id
Toko Online: ITSHOP Purwokerto (https://Tokopedia.com/itshoppwt, https://Shopee.co.id/itshoppwt, https://Bukalapak.com/itshoppwt)
Dibuat oleh: Hari Wicaksono, S.Kom
02-2023
*/

use App\Controllers\BaseControllerApi;
use App\Modules\Barang\Models\BarangModel;
use App\Modules\StokInOut\Models\StokModel;
use App\Modules\Toko\Models\TokoModel;
use App\Modules\Cashflow\Models\CashflowModel;
use App\Modules\Log\Models\LogModel;
use CodeIgniter\I18n\Time;

class Stok extends BaseControllerApi
{
    protected $format       = 'json';
    protected $modelName    = StokModel::class;
    protected $toko;
    protected $barang;
    protected $cash;
    protected $log;

    public function __construct()
    {
        //memanggil Model
        $this->toko = new TokoModel();
        $this->barang = new BarangModel();
        $this->cash = new CashflowModel();
        $this->log = new LogModel();
    }

    public function index()
    {
        $input = $this->request->getVar();
        $start = $input['tgl_start'] ?? "";
        $end = $input['tgl_end'] ?? "";
        if ($start == "" && $end == "") {
            $data = $this->model->getStok();
        } else {
            $data = $this->model->getStok($start, $end);
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
        return $this->respond(['status' => true, 'message' => lang('App.getSuccess'), 'data' => $this->model->showPajak($id)], 200);
    }

    public function create()
    {
        $rules = [
            'id_barang' => [
                'rules'  => 'required',
                'errors' => []
            ],
            'jenis' => [
                'rules'  => 'required',
                'errors' => []
            ],
            'jumlah' => [
                'rules'  => 'required|numeric',
                'errors' => []
            ],
        ];

        if ($this->request->getJSON()) {
            $json = $this->request->getJSON();
            $idBarang = $json->id_barang;
            $kodeBarang = $json->kode_barang;
            $jenis = $json->jenis;
            $jumlah = $json->jumlah;

            $qbarang = $this->barang->find($idBarang);
            $hrgBeli = $qbarang['harga_beli'];
            $stok = $qbarang['stok'];
            $nilai = $hrgBeli*$jumlah;

            $data = [
                'id_barang' => $idBarang,
                'jenis' => $jenis,
                'jumlah' => $jumlah,
                'nilai' => $nilai,
                'keterangan' => $json->keterangan,
                'id_toko' => 1,
                'id_login' => session()->get('id'),
            ];
        } else {
            $idBarang = $this->request->getPost('id_barang');
            $kodeBarang = $this->request->getPost('kode_barang');
            $jenis = $this->request->getPost('jenis');
            $jumlah = $this->request->getPost('jumlah');

            $qbarang = $this->barang->find($idBarang);
            $hrgBeli = $qbarang['harga_beli'];
            $stok = $qbarang['stok'];
            $nilai = $hrgBeli*$jumlah;

            $data = [
                'id_barang' => $idBarang,
                'jenis' => $jenis,
                'jumlah' => $jumlah,
                'nilai' => $nilai,
                'keterangan' => $this->request->getPost('keterangan'),
                'id_toko' => 1,
                'id_login' => session()->get('id'),
            ];
        }

        if (!$this->validate($rules)) {
            $response = [
                'status' => false,
                'message' => lang('App.isRequired'),
                'data' => $this->validator->getErrors(),
            ];
            return $this->respond($response, 200);
        } else {
            $this->model->save($data);
            $idStok = $this->model->getInsertID();

            //Save Log
            $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Save Stok kode_barang: ' . $kodeBarang]);

            //Update Stok Barang
            if ($jenis == 'in') {
                $stock = array(
                    'stok' => $stok + $jumlah,
                );
                $this->barang->update($idBarang, $stock);
            } else {
                $stock = array(
                    'stok' => $stok - $jumlah,
                );
                $this->barang->update($idBarang, $stock);
            }

            //Save Log
            $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Update Barang Stok kode_barang: ' . $kodeBarang]);

            $response = [
                'status' => true,
                'message' => lang('App.saveSuccess'),
                'data' => [],
            ];
            return $this->respond($response, 200);
        }
    }

    public function update($id = NULL)
    {
        $rules = [
            'keterangan' => [
                'rules'  => 'required',
                'errors' => []
            ],
        ];

        if ($this->request->getJSON()) {
            $json = $this->request->getJSON();
            $data = [
                'keterangan' => $json->keterangan,
            ];
        } else {
            $data = $this->request->getRawInput();
        }

        if (!$this->validate($rules)) {
            $response = [
                'status' => false,
                'message' => lang('App.isRequired'),
                'data' => $this->validator->getErrors(),
            ];
            return $this->respond($response, 200);
        } else {
            $this->model->update($id, $data);

            //Save Log
            $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Update Stok Opname: ' . $id]);

            $response = [
                'status' => true,
                'message' => lang('App.updSuccess'),
                'data' => [],
            ];
            return $this->respond($response, 200);
        }
    }

    public function delete($id = null)
    {
        $delete = $this->model->find($id);
        if ($delete) {
            $idBarang = $delete['id_barang'];
            $jenis = $delete['jenis'];
            $jumlah = $delete['jumlah'];

            //Query Barang
            $qbarang = $this->barang->find($idBarang);
            $stok = $qbarang['stok'];

            //Update Stok Barang karena Data Stok In/Out akan dihapus
            if ($jenis == 'in') {
                $stock = array(
                    'stok' => $stok - $jumlah,
                );
                $this->barang->update($idBarang, $stock);
            } else {
                $stock = array(
                    'stok' => $stok + $jumlah,
                );
                $this->barang->update($idBarang, $stock);
            }

            //Save Log
            $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Update Barang Stok id_barang: ' . $idBarang]);

            //Delete Data Stok In/Out
            $this->model->delete($id);

            //Save Log
            $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Delete Stok Opname: ' . $id]);

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
}
