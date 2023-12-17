<?php

namespace App\Modules\StokOpname\Controllers\Api;
/*
PT ITSHOP BISNIS DIGITAL
Website: https://itshop.biz.id
Toko Online: ITSHOP Purwokerto (https://Tokopedia.com/itshoppwt, https://Shopee.co.id/itshoppwt, https://Bukalapak.com/itshoppwt)
Dibuat oleh: Hari Wicaksono, S.Kom
02-2023
*/

use App\Controllers\BaseControllerApi;
use App\Modules\Barang\Models\BarangModel;
use App\Modules\StokOpname\Models\StokOpnameModel;
use App\Modules\Toko\Models\TokoModel;
use App\Modules\Cashflow\Models\CashflowModel;
use App\Modules\Log\Models\LogModel;
use CodeIgniter\I18n\Time;

class StokOpname extends BaseControllerApi
{
    protected $format       = 'json';
    protected $modelName    = StokOpnameModel::class;
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
            $data = $this->model->getStokOpname();
        } else {
            $data = $this->model->getStokOpname($start, $end);
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
            'stok' => [
                'rules'  => 'required',
                'errors' => []
            ],
            'stok_nyata' => [
                'rules'  => 'required',
                'errors' => []
            ],
        ];

        if ($this->request->getJSON()) {
            $json = $this->request->getJSON();
            $idBarang = $json->id_barang;
            $kodeBarang = $json->kode_barang;
            $stok = $json->stok;
            $stokNyata = $json->stok_nyata;
            $selisih = $stokNyata-$stok;

            $qbarang = $this->barang->find($idBarang);
            $hrgBeli = $qbarang['harga_beli'];
            $nilai = $hrgBeli*$selisih;

            $data = [
                'id_barang' => $idBarang,
                'stok' => $stok,
                'stok_nyata' => $stokNyata,
                'selisih' => $selisih,
                'nilai' => $nilai,
                'keterangan' => $json->keterangan,
                'id_toko' => 1,
                'id_login' => session()->get('id'),
            ];
        } else {
            $idBarang = $this->request->getPost('id_barang');
            $kodeBarang = $this->request->getPost('kode_barang');
            $stok = $this->request->getPost('stok');
            $stokNyata = $this->request->getPost('stok_nyata');
            $selisih = $stokNyata-$stok;

            $qbarang = $this->barang->find($idBarang);
            $hrgBeli = $qbarang['harga_beli'];
            $nilai = $hrgBeli*$selisih;

            $data = [
                'id_barang' => $idBarang,
                'stok' => $stok,
                'stok_nyata' => $stokNyata,
                'selisih' => $selisih,
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
            $idStokOpname = $this->model->getInsertID();

            //Save Log
            $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Save Stok Opname kode_barang: ' . $kodeBarang]);

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
