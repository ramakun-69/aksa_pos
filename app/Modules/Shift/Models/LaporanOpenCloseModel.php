<?php

namespace App\Modules\Shift\Models;

/*
PT ITSHOP BISNIS DIGITAL
Website: https://itshop.biz.id
Toko Online: ITSHOP Purwokerto (https://Tokopedia.com/itshoppwt, https://Shopee.co.id/itshoppwt, https://Bukalapak.com/itshoppwt)
Dibuat oleh: Hari Wicaksono, S.Kom
04-2022
*/

use CodeIgniter\Model;

class LaporanOpenCloseModel extends Model
{
    protected $table                = 'penjualan';

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    // hitung total data pada transaction
    public function getCountTrxSelesai($user, $date)
    {
        return $this->db->table("penjualan")->where(['id_login' => $user, 'DATE(created_at) =' => $date])->where('metode_bayar !=', 'credit')->countAllResults();
    }

    public function getCountTrxBelumSelesai($user, $date)
    {
        return $this->db->table("penjualan")->where(['id_login' => $user, 'DATE(created_at) =' => $date])->where('metode_bayar', 'credit')->countAllResults();
    }

    public function totalCash($user, $date)
    {
        //$this->select('sum(total) as total');
        //$this->where(['id_login' => $user, 'DATE(created_at) =' => $date, 'metode_bayar' => 'cash']);
        //return $this->get()->getRow()->total;
        $query = $this->db->table('cashflow')
            ->select('sum(pemasukan) as total')
            ->like('jenis', 'Pemasukan')
            ->where(['id_login' => $user, 'DATE(created_at) =' => $date]);
        return $query->get()->getRow()->total;
    }

    public function totalCredit($user, $date)
    {
        //$this->select('sum(total) as total');
        //$this->where(['id_login' => $user, 'DATE(created_at) =' => $date, 'metode_bayar' => 'credit']);
        //return $this->get()->getRow()->total;
        $query = $this->db->table('piutang')
            ->select('sum(sisa_piutang) as total')
            ->where(['id_login' => $user, 'DATE(created_at) =' => $date]);
        return $query->get()->getRow()->total;
    }

    public function totalBank($user, $date)
    {
        //$this->select('sum(total) as total');
        //$this->where(['id_login' => $user, 'DATE(created_at) =' => $date, 'metode_bayar' => 'bank']);
        //return $this->get()->getRow()->total;
        $query = $this->db->table('bank')
            ->select('sum(pemasukan) as total')
            ->like('jenis', 'Pemasukan')
            ->where(['id_login' => $user, 'DATE(created_at) =' => $date]);
        return $query->get()->getRow()->total;
    }
}
