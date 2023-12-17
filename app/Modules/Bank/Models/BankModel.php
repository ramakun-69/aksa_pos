<?php

namespace App\Modules\Bank\Models;

use CodeIgniter\Model;

class BankModel extends Model
{
    protected $DBGroup              = 'default';
    protected $table                = 'bank';
    protected $primaryKey           = 'id_bank';
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

    public function getBank($start = false, $end = false, $where = false)
    {
        $this->select("{$this->table}.*, b.nama_bank, b.bank_nama, b.no_rekening, l.nama");
        $this->join("bank_akun b", "b.id_bank_akun = {$this->table}.id_bank_akun", 'left');
        $this->join("login l", "l.id_login = {$this->table}.id_login");
        if ($start != "" && $end != "") :
            $this->where("DATE({$this->table}.tanggal) BETWEEN '$start' AND '$end'", null, false);
        endif;
        if ($where != "") :
            $this->where("{$this->table}.id_bank_akun", $where);
            $multiple = explode(",", $where);
            if (count($multiple) > 1) {
                $this->where("{$this->table}.id_bank_akun", $multiple[0]);
                $this->orWhere("{$this->table}.id_bank_akun", $multiple[1]);
            }
        endif;
        $this->orderBy("{$this->table}.created_at", "DESC");
        $query = $this->findAll();
        return $query;
    }

    public function showBank($id)
    {
        $this->select("{$this->table}.*, b.nama_bank, b.bank_nama, b.no_rekening, l.nama");
        $this->join("bank_akun b", "b.id_bank_akun = {$this->table}.id_bank_akun", 'left');
        $this->join("login l", "l.id_login = {$this->table}.id_login");
        $this->where("{$this->table}.id_bank", $id);
        $query = $this->first();
        return $query;
    }

    public function getSaldo($start = false, $end = false, $where = false)
    {
        $this->select("(sum(pemasukan)-sum(pengeluaran)) as total");
        $this->where("{$this->table}.id_bank_akun", $where);
        if ($start != "" && $end != "") :
            $this->where("DATE({$this->table}.tanggal) BETWEEN '$start' AND '$end'", null, false);
        endif;
        return $this->get()->getRow()->total;
    }
}
