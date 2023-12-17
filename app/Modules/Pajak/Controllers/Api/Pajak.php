<?php

namespace App\Modules\Pajak\Controllers\Api;

use App\Controllers\BaseControllerApi;
use App\Modules\Pajak\Models\PajakModel;
use App\Modules\Toko\Models\TokoModel;
use App\Modules\Log\Models\LogModel;
use CodeIgniter\I18n\Time;

class Pajak extends BaseControllerApi
{
    protected $format       = 'json';
    protected $modelName    = PajakModel::class;
    protected $toko;
    protected $log;

    public function __construct()
    {
        //memanggil Model
        $this->toko = new TokoModel();
        $this->log = new LogModel();
    }

    public function index()
    {
        $data = $this->model->getPajak();
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

    public function saldo()
    {
        $sumKeluaran = $this->model->select('sum(nominal) as total')->where("jenis", "Keluaran")->get()->getRow()->total;
        $sumDisetorkan = $this->model->select('sum(nominal) as total')->where("jenis", "Disetorkan")->get()->getRow()->total;
        $data = (int)$sumKeluaran-(int)$sumDisetorkan;
        if (!empty($data)) {
            $response = [
                "status" => true,
                "message" => lang('App.getSuccess'),
                "data" => [
                    'keluaran' => (int)$sumKeluaran,
                    'disetorkan' => (int)$sumDisetorkan,
                    'saldo' => $data,
                ],
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

    public function create()
    {
        $rules = [
            'jenis' => [
                'rules'  => 'required',
                'errors' => []
            ],
            'nominal' => [
                'rules'  => 'required',
                'errors' => []
            ],
        ];

        //Ambil kode pajak
        $toko = $this->toko->first();
        $ppn = $toko['PPN'];
        $kdJualTahun = $toko['kode_jual_tahun'];
        $kdPajak = $toko['kode_pajak'];
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
            $kodePajak = $kdPajak . date('dmy') . '-' . $timestamp;
        } else {
            $kodePajak = $kdPajak . $timestamp;
        }

        if ($this->request->getJSON()) {
            $json = $this->request->getJSON();
            $jenis = $json->jenis;
            $nominal = $json->nominal;
            $data = [
                'faktur' => $kodePajak,
                'jenis' => $jenis,
                'nominal' => $nominal,
                'keterangan' => $json->keterangan,
                'id_toko' => 1,
                'id_login' => session()->get('id'),
            ];
        } else {
            $jenis = $this->request->getPost('jenis');
            $nominal = $this->request->getPost('nominal');
            $data = [
                'faktur' => $kodePajak,
                'PPN' => $ppn,
                'jenis' => $jenis,
                'nominal' => $nominal,
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
            //Save Pajak
            $this->model->save($data);
            //Save Log
            $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Save Pajak: ' . $kodePajak]);

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
            //Update Pajak
            $this->model->update($id, $data);
            //Save Log
            $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Update Pajak: ' . $id]);

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
        $faktur = $delete['faktur'];

        if ($delete) {
            //Delete Pajak
            $this->model->delete($id);
            //Save Log
            $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Delete Pajak: ' . $faktur]);

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
