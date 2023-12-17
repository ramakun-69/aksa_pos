<?php

namespace App\Modules\Bank\Models;

use CodeIgniter\Model;

class BankAkunModel extends Model
{
    protected $DBGroup              = 'default';
    protected $table                = 'bank_akun';
    protected $primaryKey           = 'id_bank_akun';
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

    public function getBank()
    {
        $this->select("{$this->table}.*, l.nama");
        $this->join("login l", "l.id_login = {$this->table}.id_login");
        $query = $this->findAll();
        return $query;
    }

    public function showBank($id)
    {
        $this->select("{$this->table}.*, l.nama");
        $this->join("login l", "l.id_login = {$this->table}.id_login");
        $this->where("{$this->table}.id_bank", $id);
        $query = $this->first();
        return $query;
    }
}
