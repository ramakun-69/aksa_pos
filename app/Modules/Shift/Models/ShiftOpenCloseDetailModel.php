<?php

namespace App\Modules\Shift\Models;

use CodeIgniter\Model;

class ShiftOpenCloseDetailModel extends Model
{
    protected $DBGroup              = 'default';
    protected $table                = 'shift_openclose_detail';
    protected $primaryKey           = 'id_shift_openclose_detail';
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

    public function getOpenCloseCashier($start = false, $end = false)
    {
        $this->select("{$this->table}.*, s.nama_shift, s.jam_mulai, s.jam_selesai, l.email, l.username");
        $this->join("shift s", "s.id_shift = {$this->table}.id_shift");
        $this->join("login l", "l.id_login = {$this->table}.id_login");
        if ($start != "" && $end != "") :
            $this->where("DATE({$this->table}.tanggal) BETWEEN '$start' AND '$end'", null, false);
        endif;
        $query = $this->findAll();
        return $query;
    }
}
