<?php

namespace App\Libraries;
/*
PT ITSHOP BISNIS DIGITAL
Website: https://itshop.biz.id
Toko Online: ITSHOP Purwokerto (https://Tokopedia.com/itshoppwt, https://Shopee.co.id/itshoppwt, https://Bukalapak.com/itshoppwt)
Dibuat oleh: Hari Wicaksono, S.Kom
06-2022
*/

class Settings
{
	var $info = array();
	public function __construct()
	{
		$DB = \Config\Database::connect();
		$site = $DB->table('settings');

		foreach ($site->get()->getResult() as $set) {
			$key = $set->variable_setting;
			$value = $set->value_setting;
			$this->info[$key] = $value;
		}
	}
}
