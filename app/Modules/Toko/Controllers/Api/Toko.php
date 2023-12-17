<?php

namespace App\Modules\Toko\Controllers\Api;

use App\Controllers\BaseControllerApi;
use App\Modules\Toko\Models\TokoModel;
use App\Modules\Log\Models\LogModel;

class Toko extends BaseControllerApi
{
    protected $format       = 'json';
    protected $modelName    = TokoModel::class;
    protected $log;

    public function __construct()
    {
        $this->log = new LogModel();
    }

    public function index()
    {
        $data = $this->model->first();
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

    public function update($id = NULL)
    {
        //$id = '1';
        $rules = [
            'nama_toko' => [
                'rules'  => 'required',
                'errors' => []
            ],
            'alamat_toko' => [
                'rules'  => 'required',
                'errors' => []
            ],
            'telp' => [
                'rules'  => 'required',
                'errors' => []
            ],
            'nama_pemilik' => [
                'rules'  => 'required',
                'errors' => []
            ],
            'nib' => [
                'rules'  => 'required|numeric',
                'errors' => []
            ],
            'ppn' => [
                'rules'  => 'required|numeric',
                'errors' => []
            ],
        ];

        if ($this->request->getJSON()) {
            $json = $this->request->getJSON();
            $data = [
                'nama_toko' => $json->nama_toko,
                'alamat_toko' => $json->alamat_toko,
                'telp' => $json->telp,
                'email' => $json->email,
                'nama_pemilik' => $json->nama_pemilik,
                'NIB' => $json->nib,
                'PPN' => $json->ppn,
                'include_ppn' => $json->include_ppn,
                'kode_barang' => $json->kode_barang,
                'kode_jual' => $json->kode_jual,
                'kode_beli' => $json->kode_beli,
                'kode_kas' => $json->kode_kas,
                'kode_bank' => $json->kode_bank,
                'kode_pajak' => $json->kode_pajak,
                'kode_biaya' => $json->kode_biaya,
                'paper_size' => $json->paper_size,
                'footer_nota' => $json->footer_nota,
                'jatuhtempo_hari' => $json->jatuhtempo_hari,
                'jatuhtempo_tanggal' => $json->jatuhtempo_tanggal,
                'pembulatan' => $json->pembulatan,
                'pembulatan_keatas' => $json->pembulatan_keatas,
                'pembulatan_max' => $json->pembulatan_max,
                'diskon_member' => $json->diskon_member
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
            $this->log->save(['keterangan' => session('nama') . ' (' . session('email') . ') ' . strtolower(lang('App.do')) . ' Update Toko/Warung: ' . $id]);
            $response = [
                'status' => true,
                'message' => lang('App.updSuccess'),
                'data' => [],
            ];
            return $this->respond($response, 200);
        }
    }

    public function setAktifPrinterUsb($id = NULL)
    {

        if ($this->request->getJSON()) {
            $json = $this->request->getJSON();
            $data = [
                'printer_usb' => $json->printer_usb
            ];
        } else {
            $input = $this->request->getRawInput();
            $data = [
                'printer_usb' => $input['printer_usb']
            ];
        }

        if ($data > 0) {
            $this->model->update($id, $data);
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

    public function setAktifPrinterBT($id = NULL)
    {

        if ($this->request->getJSON()) {
            $json = $this->request->getJSON();
            $data = [
                'printer_bluetooth' => $json->printer_bluetooth
            ];
        } else {
            $input = $this->request->getRawInput();
            $data = [
                'printer_bluetooth' => $input['printer_bluetooth']
            ];
        }

        if ($data > 0) {
            $this->model->update($id, $data);
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

    public function setAktifKodeJualTahun($id = NULL)
    {
        if ($this->request->getJSON()) {
            $json = $this->request->getJSON();
            $data = [
                'kode_jual_tahun' => $json->kode_jual_tahun
            ];
        } else {
            $input = $this->request->getRawInput();
            $data = [
                'kode_jual_tahun' => $input['kode_jual_tahun']
            ];
        }

        if ($data > 0) {
            $this->model->update($id, $data);
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

    public function setAktifScanKeranjang($id = NULL)
    {
        if ($this->request->getJSON()) {
            $json = $this->request->getJSON();
            $data = [
                'scan_keranjang' => $json->scan_keranjang
            ];
        } else {
            $input = $this->request->getRawInput();
            $data = [
                'scan_keranjang' => $input['scan_keranjang']
            ];
        }

        if ($data > 0) {
            $this->model->update($id, $data);
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

    public function setAktifTglJatuhTempo($id = NULL)
    {
        if ($this->request->getJSON()) {
            $json = $this->request->getJSON();
            $data = [
                'jatuhtempo_hari_tanggal' => $json->jatuhtempo_hari_tanggal
            ];
        } else {
            $input = $this->request->getRawInput();
            $data = [
                'jatuhtempo_hari_tanggal' => $input['jatuhtempo_hari_tanggal']
            ];
        }

        if ($data > 0) {
            $this->model->update($id, $data);
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

    public function setAktifKetJatuhTempo($id = NULL)
    {
        if ($this->request->getJSON()) {
            $json = $this->request->getJSON();
            $data = [
                'jatuhtempo_keterangan' => $json->jatuhtempo_keterangan
            ];
        } else {
            $input = $this->request->getRawInput();
            $data = [
                'jatuhtempo_keterangan' => $input['jatuhtempo_keterangan']
            ];
        }

        if ($data > 0) {
            $this->model->update($id, $data);
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
