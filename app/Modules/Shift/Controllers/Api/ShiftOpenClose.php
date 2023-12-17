<?php

namespace App\Modules\Shift\Controllers\Api;
/*
PT ITSHOP BISNIS DIGITAL
Website: https://itshop.biz.id
Toko Online: ITSHOP Purwokerto (https://Tokopedia.com/itshoppwt, https://Shopee.co.id/itshoppwt, https://Bukalapak.com/itshoppwt)
Dibuat oleh: Hari Wicaksono, S.Kom
04-2023
*/

use App\Controllers\BaseControllerApi;
use App\Modules\Shift\Models\ShiftOpenCloseModel;
use App\Modules\Log\Models\LogModel;
use App\Modules\Shift\Models\LaporanOpenCloseModel;
use App\Modules\Shift\Models\ShiftOpenCloseDetailModel;
use CodeIgniter\I18n\Time;

class ShiftOpenClose extends BaseControllerApi
{
    protected $format       = 'json';
    protected $modelName    = ShiftOpenCloseModel::class;
    protected $log;
    protected $detail;
    protected $laporan;

    public function __construct()
    {
        //memanggil Model
        $this->log = new LogModel();
        $this->detail = new ShiftOpenCloseDetailModel();
        $this->laporan = new LaporanOpenCloseModel();
    }

    public function index()
    {
        $input = $this->request->getVar();
        $start = $input['tgl_start'] ?? "";
        $end = $input['tgl_end'] ?? "";
        if ($start == "" && $end == "") {
            $data = $this->model->getOpenCloseCashier();
        } else {
            $data = $this->model->getOpenCloseCashier($start, $end);
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
        return $this->respond(['status' => true, 'message' => lang('App.getSuccess'), 'data' => $this->model->show($id)], 200);
    }

    public function detail($id = null)
    {
        return $this->respond(['status' => true, 'message' => lang('App.getSuccess'), 'data' => $this->detail->where('id_shift_openclose', $id)->first()], 200);
    }

    public function getStatus()
    {
        $checkIsOpen = $this->model->where(['type' => 'open', 'id_login' => session()->get('id'), 'tanggal' => date('Y-m-d')])->findAll();
        $checkIsClose = $this->model->where(['type' => 'close', 'id_login' => session()->get('id'), 'tanggal' => date('Y-m-d')])->findAll();
        $data = "";
        if (!empty($checkIsOpen) && empty($checkIsClose) || count($checkIsOpen) > 1 && count($checkIsClose) == 1) {
            $data = 'Open';
        } else if (!empty($checkIsOpen) && !empty($checkIsClose) || count($checkIsOpen) > 1 && count($checkIsClose) > 1) {
            $data = 'Close';
        } else {
            $data = 'Absen';
        }
        return $this->respond(['status' => true, 'message' => lang('App.getSuccess'), 'data' => $data], 200);
    }

    public function create()
    {
        $rules = [
            'id_shift' => [
                'rules'  => 'required',
                'errors' => []
            ],
            'type' => [
                'rules'  => 'required',
                'errors' => []
            ],
            'tanggal' => [
                'rules'  => 'required',
                'errors' => []
            ],
            'waktu' => [
                'rules'  => 'required',
                'errors' => []
            ],
            'kertas100' => [
                'rules'  => 'required|numeric',
                'errors' => []
            ],
            'kertas50' => [
                'rules'  => 'required|numeric',
                'errors' => []
            ],
            'kertas20' => [
                'rules'  => 'required|numeric',
                'errors' => []
            ],
            'kertas10' => [
                'rules'  => 'required|numeric',
                'errors' => []
            ],
            'kertas5' => [
                'rules'  => 'required|numeric',
                'errors' => []
            ],
            'kertas2' => [
                'rules'  => 'required|numeric',
                'errors' => []
            ],
            'kertas1' => [
                'rules'  => 'required|numeric',
                'errors' => []
            ],
            'koin1000' => [
                'rules'  => 'required|numeric',
                'errors' => []
            ],
            'koin500' => [
                'rules'  => 'required|numeric',
                'errors' => []
            ],
            'koin200' => [
                'rules'  => 'required|numeric',
                'errors' => []
            ],
            'koin100' => [
                'rules'  => 'required|numeric',
                'errors' => []
            ],
        ];

        if ($this->request->getJSON()) {
            $json = $this->request->getJSON();
            $idShift = $json->id_shift;
            $type = $json->type;
            $tanggal = $json->tanggal;
            $waktu = $json->waktu;
            $waktuClose = $json->waktuClose;
            $data = [
                'id_shift' => $idShift,
                'type' => $type,
                'tanggal' => $tanggal,
                'waktu' => $waktu,
                'id_login' => session()->get('id'),
                'supervisor' => 3,
                'jumlah_uang_kertas' => $json->jumlah_uang_kertas,
                'jumlah_uang_koin' => $json->jumlah_uang_koin,
                'id_toko' => 1,
            ];
            // Jumlah Uang Kertas dan Koin
            $kertas100 = $json->kertas100;
            $kertas50 = $json->kertas50;
            $kertas20 = $json->kertas20;
            $kertas10 = $json->kertas10;
            $kertas5 = $json->kertas5;
            $kertas2 = $json->kertas2;
            $kertas1 = $json->kertas1;
            $koin1000 = $json->koin1000;
            $koin500 = $json->koin500;
            $koin200 = $json->koin200;
            $koin100 = $json->koin100;
            $redirect = $json->redirect;
        } else {
            $idShift = $this->request->getPost('id_shift');
            $type = $this->request->getPost('type');
            $tanggal = $this->request->getPost('tanggal');
            $waktu = $this->request->getPost('waktu');
            $waktuClose = $this->request->getPost('waktuClose');
            $data = [
                'id_shift' => $idShift,
                'type' => $type,
                'tanggal' => $tanggal,
                'waktu' => $waktu,
                'id_login' => session()->get('id'),
                'supervisor' => 3,
                'jumlah_uang_kertas' => $this->request->getPost('jumlah_uang_kertas'),
                'jumlah_uang_koin' => $this->request->getPost('jumlah_uang_koin'),
                'id_toko' => 1,
            ];
            // Jumlah Uang Kertas dan Koin
            $kertas100 = $this->request->getPost('kertas100');
            $kertas50 = $this->request->getPost('kertas50');
            $kertas20 = $this->request->getPost('kertas20');
            $kertas10 = $this->request->getPost('kertas10');
            $kertas5 = $this->request->getPost('kertas5');
            $kertas2 = $this->request->getPost('kertas2');
            $kertas1 = $this->request->getPost('kertas1');
            $koin1000 = $this->request->getPost('koin1000');
            $koin500 = $this->request->getPost('koin500');
            $koin200 = $this->request->getPost('koin200');
            $koin100 = $this->request->getPost('koin100');
            $redirect = $this->request->getPost('redirect');
        }

        $checkIsOpen = $this->model->where(['type' => 'open', 'id_login' => session()->get('id'), 'tanggal' => $tanggal, 'id_shift' => $idShift])->first();
        $checkIsClose = $this->model->where(['type' => 'close', 'id_login' => session()->get('id'), 'tanggal' => $tanggal, 'id_shift' => $idShift])->first();

        if (!$this->validate($rules)) {
            $response = [
                'status' => false,
                'message' => lang('App.isRequired'),
                'data' => $this->validator->getErrors(),
            ];
            return $this->respond($response, 200);
        } else {
            // Periksa jika type Open sudah Open Cashier
            if ($type == 'open' && $checkIsOpen) {
                $response = [
                    'status' => false,
                    'message' => 'Sudah Open Cashier tanggal ' . date('d-m-Y', strtotime($tanggal)),
                    'data' => [],
                ];
                return $this->respond($response, 200);
                // Periksa jika type Close sudah Close Cashier
            } else if ($type == 'close' && $checkIsClose) {
                $response = [
                    'status' => false,
                    'message' => 'Sudah Close Cashier tanggal ' . date('d-m-Y', strtotime($tanggal)),
                    'data' => [],
                ];
                return $this->respond($response, 200);
                // Periksa jika type Close belum Open Cashier
            } else if ($type == 'close' && empty($checkIsOpen)) {
                $response = [
                    'status' => false,
                    'message' => 'Anda Belum Open Cashier tanggal ' . date('d-m-Y', strtotime($tanggal)),
                    'data' => [],
                ];
                return $this->respond($response, 200);
            } else if ($type == 'close' && date('H:i', strtotime($waktu)) < date('H:i', strtotime($waktuClose))) {
                $response = [
                    'status' => false,
                    'message' => 'Belum Jam Akhir Shift. Jam Close: ' . date('H:i', strtotime($waktu)) . ', Waktu Saat ini: ' . date('H:i:s') . ', Waktu Close: ' . $waktuClose,
                    'data' => [],
                ];
                return $this->respond($response, 200);
            } else {
                $this->model->save($data);
                $lastId = $this->model->getInsertID();

                // Save Log
                $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Save ' . ucfirst($type) . ' Cashier: ' . $lastId]);

                // Save Detail
                $detail = [
                    'id_shift_openclose' => $lastId,
                    'kertas100' => $kertas100,
                    'kertas50' => $kertas50,
                    'kertas20' => $kertas20,
                    'kertas10' => $kertas10,
                    'kertas5' => $kertas5,
                    'kertas2' => $kertas2,
                    'kertas1' => $kertas1,
                    'koin1000' => $koin1000,
                    'koin500' => $koin500,
                    'koin200' => $koin200,
                    'koin100' => $koin100,
                ];
                $this->detail->save($detail);

                //Save Log
                $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Save Detail ' . ucfirst($type) . ' Cashier: ' . $lastId]);

                if ($type == 'open' && $redirect == true) {
                    $result = ['url' => base_url('sales')];
                } else {
                    $result = [];
                }

                $response = [
                    'status' => true,
                    'message' => lang('App.saveSuccess'),
                    'data' => $result,
                ];
                return $this->respond($response, 200);
            }
        }
    }

    public function update($id = NULL)
    {
        $rules = [
            'tanggal' => [
                'rules'  => 'required',
                'errors' => []
            ],
            'waktu' => [
                'rules'  => 'required',
                'errors' => []
            ],
        ];

        if ($this->request->getJSON()) {
            $json = $this->request->getJSON();
            $tanggal = $json->tanggal;
            $waktu = $json->waktu;
            $data = [
                'tanggal' => $tanggal,
                'waktu' => $waktu,
                'jumlah_uang_kertas' => $json->jumlah_uang_kertas,
                'jumlah_uang_koin' => $json->jumlah_uang_koin,
            ];
            // Jumlah Uang Kertas dan Koin
            $idDetail = $json->id_shift_openclose_detail;
            $kertas100 = $json->kertas100;
            $kertas50 = $json->kertas50;
            $kertas20 = $json->kertas20;
            $kertas10 = $json->kertas10;
            $kertas5 = $json->kertas5;
            $kertas2 = $json->kertas2;
            $kertas1 = $json->kertas1;
            $koin1000 = $json->koin1000;
            $koin500 = $json->koin500;
            $koin200 = $json->koin200;
            $koin100 = $json->koin100;
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
            $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Update Open Close Cashier: ' . $id]);

            // Update Detail
            $detail = [
                'kertas100' => $kertas100,
                'kertas50' => $kertas50,
                'kertas20' => $kertas20,
                'kertas10' => $kertas10,
                'kertas5' => $kertas5,
                'kertas2' => $kertas2,
                'kertas1' => $kertas1,
                'koin1000' => $koin1000,
                'koin500' => $koin500,
                'koin200' => $koin200,
                'koin100' => $koin100,
            ];
            $this->detail->update($idDetail, $detail);

            //Save Log
            $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Update Detail Open Close Cashier: ' . $id]);

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
            //Delete Data
            $this->model->delete($id);

            //Save Log
            $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Delete Open Close Cashier: ' . $id]);

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

    public function getOpen()
    {
        $input = $this->request->getVar();
        $date = $input['date'];
        $user = $input['user'];
        return $this->respond(['status' => true, 'message' => lang('App.getSuccess'), 'data' => $this->model->where(['type' => 'open', 'id_login' => $user, 'tanggal' => $date])->first()], 200);
    }

    public function laporanOpenClose()
    {
        $input = $this->request->getVar();
        $date = $input['date'];
        $user = $input['user'];
        return $this->respond([
            'status' => true,
            'message' => lang('App.getSuccess'),
            'data' => [
                'trx_selesai' => $this->laporan->getCountTrxSelesai($user, $date),
                'trx_belum_selesai' => $this->laporan->getCountTrxBelumSelesai($user, $date),
                'total_cash' => $this->laporan->totalCash($user, $date),
                'total_credit' => $this->laporan->totalCredit($user, $date),
                'total_bank' => $this->laporan->totalBank($user, $date),
                ]
        ], 200);
    }
}
