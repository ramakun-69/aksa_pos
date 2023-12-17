<?php 
namespace App\Modules\Media\Controllers\Api;
/*
PT ITSHOP BISNIS DIGITAL
Website: https://itshop.biz.id
Toko Online: ITSHOP Purwokerto (https://Tokopedia.com/itshoppwt, https://Shopee.co.id/itshoppwt, https://Bukalapak.com/itshoppwt)
Dibuat oleh: Hari Wicaksono, S.Kom
02-2023
*/

use App\Controllers\BaseControllerApi;
use App\Modules\Media\Models\MediaModel;

class Media extends BaseControllerApi
{
    protected $format       = 'json';
    protected $modelName    = MediaModel::class;

    public function getMedia($id = null)
    {
        $data = $this->model->where(['id_barang' => $id, 'active' => 1])->first();
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

	public function create()
    {
        $gambar = $this->request->getFile('image');
        $id_barang = $this->request->getPost('id_barang');
        // buat nama file random
        $fileName = $gambar->getRandomName();
        if ($gambar !== "") {
            $path = "images/";
            // pindahkan ke lokasi $path
            $moved = $gambar->move($path, $fileName);
            if ($moved) {
                $simpan = $this->model->save([
                    'media_path' => $path . $fileName,
                    'id_barang' => $id_barang,
                    'active' => 1
                ]);
                if ($simpan) {
                    // respond true
                    return $this->respond(["status" => true, "message" => lang('App.imgSuccess'), "data" => $this->model->getInsertID()], 200);
                } else {
                    // respond false
                    return $this->respond(["status" => false, "message" => lang('App.imgFailed'), "data" => []], 200);
                }
            }
        } else {
            $response = [
                'status' => false,
                'message' => lang('App.uploadFailed'),
                'data' => []
            ];
            return $this->respond($response, 200);
        }
    }

    public function delete($id = null)
    {
        $hapus = $this->model->find($id);
        if ($hapus) {
            $this->model->delete($id);
            // hapus file
            unlink($hapus['media_path']);
            
            $response = [
                'status' => true,
                'message' => lang('App.imgDeleted'),
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