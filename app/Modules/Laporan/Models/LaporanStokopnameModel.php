<?php

namespace App\Modules\Laporan\Models;

use CodeIgniter\Model;

class LaporanStokopnameModel extends Model
{
    protected $DBGroup              = 'default';
    protected $table                = 'stok_opname';
    protected $primaryKey           = 'id_stok_opname';
    protected $useAutoIncrement     = true;
    protected $insertID             = 0;
    protected $returnType           = 'array';
    protected $useSoftDeletes       = false;
    protected $protectFields        = false;
    protected $allowedFields        = [];

    // Dates
    protected $useTimestamps        = true;
    protected $dateFormat           = 'datetime';
    protected $createdField         = 'created_at';
    protected $updatedField         = 'updated_at';
    protected $deletedField         = '';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks       = true;
    protected $beforeInsert         = [];
    protected $afterInsert          = [];
    protected $beforeUpdate         = [];
    protected $afterUpdate          = [];
    protected $beforeFind           = [];
    protected $afterFind            = [];
    protected $beforeDelete         = [];
    protected $afterDelete          = [];

    public function getStokOpname($start, $end)
    {
        $this->select("{$this->table}.*, b.kode_barang, b.nama_barang, b.barcode, l.nama as nama_user");
        $this->join("barang b", "b.id_barang = {$this->table}.id_barang");
        $this->join("login l", "l.id_login = {$this->table}.id_login");
        $this->where("DATE({$this->table}.created_at) BETWEEN '$start' AND '$end'", null, false);
        $query = $this->findAll();
        return $query;
    }

    public function showStokOpname($id)
    {
        $this->select("{$this->table}.*, b.kode_barang, b.nama_barang, b.barcode, l.nama as nama_user");
        $this->join("barang b", "b.id_barang = {$this->table}.id_barang");
        $this->join("login l", "l.id_login = {$this->table}.id_login");
        $this->where("{$this->table}.id_stok_opname", $id);
        $query = $this->first();
        return $query;
    }

}
