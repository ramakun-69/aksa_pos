<?php

namespace App\Modules\Bank\Controllers\Api;

use App\Controllers\BaseControllerApi;
use App\Modules\Bank\Models\BankAkunModel;
use App\Modules\Toko\Models\TokoModel;
use App\Modules\Log\Models\LogModel;

class BankAkun extends BaseControllerApi
{
    protected $format       = 'json';
    protected $modelName    = BankAkunModel::class;
    protected $toko;
    protected $log;

    public function __construct()
    {
        $this->toko = new TokoModel();
        $this->log = new LogModel();
    }

    public function index()
    {
        $data = $this->model->findAll();
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
        return $this->respond(['status' => true, 'message' => lang('App.getSuccess'), 'data' => $this->model->find($id)], 200);
    }

    public function create()
    {
        $rules = [
            'nama_bank' => [
                'rules'  => 'required',
                'errors' => []
            ],
            'bank_nama' => [
                'rules'  => 'required',
                'errors' => []
            ],
            'no_rekening' => [
                'rules'  => 'required',
                'errors' => []
            ],
        ];

        if ($this->request->getJSON()) {
            $json = $this->request->getJSON();
            $data = [
                'nama_bank' => $json->nama_bank,
                'bank_nama' => $json->bank_nama,
                'no_rekening' => $json->no_rekening,
                'utama' => 0
            ];
        } else {
            $data = [
                'nama_bank' => $this->request->getPost('nama_bank'),
                'bank_nama' => $this->request->getPost('bank_nama'),
                'no_rekening' => $this->request->getPost('no_rekening'),
                'utama' => 0
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
            $id = $this->model->getInsertID();

            //Save Log
            $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Save Akun Bank: ' .  $id]);

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
            'nama_bank' => [
                'rules'  => 'required',
                'errors' => []
            ],
            'bank_nama' => [
                'rules'  => 'required',
                'errors' => []
            ],
            'no_rekening' => [
                'rules'  => 'required',
                'errors' => []
            ],
        ];

        if ($this->request->getJSON()) {
            $json = $this->request->getJSON();
            $data = [
                'nama_bank' => $json->nama_bank,
                'bank_nama' => $json->bank_nama,
                'no_rekening' => $json->no_rekening,
            ];
        } else {
            $data = $this->request->getRawInput();
        }

        if (!$this->validate($rules)) {
            $response = [
                'status' => false,
                'message' => lang('App.reqFailed'),
                'data' => $this->validator->getErrors(),
            ];
            return $this->respond($response, 200);
        } else {
            $this->model->update($id, $data);

            //Save Log
            $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Update Akun Bank: ' .  $id]);

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
            $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Delete Akun Bank: ' .  $id]);

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

    public function setUtama($id = NULL)
    {
        if ($this->request->getJSON()) {
            $json = $this->request->getJSON();
            $idToko = $json->id_toko;
            $data = [
                'utama' => $json->utama,
            ];
        } else {
            $input = $this->request->getRawInput();
            $idToko = $input['id_toko'];
            $data = [
                'utama' => $input['utama'],
            ];
        }

        if ($data > 0) {
            $query = $this->model->where('utama', 1)->first();
            $qid = $query['id_bank_akun'];
            $qutama = $query['utama'];
            if ($qutama == 1) :
                $this->model->update($qid, ['utama' => 0]);
            endif;
            
            $this->model->update($id, $data);
            $this->toko->update($idToko, ['id_bank_akun' => $id]);

            //Save Log
            $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Set Akun Bank Active: ' .  $id]);

            $response = [
                'status' => true,
                'message' => lang('App.updSuccess'),
                'data' => []
            ];
            return $this->respond($response, 200);
        } else {
            $response = [
                'status' => false,
                'message' => lang('App.updFailed'),
                'data' => []
            ];
            return $this->respond($response, 200);
        }
    }
}
