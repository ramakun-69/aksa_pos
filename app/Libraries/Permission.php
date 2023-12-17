<?php

namespace App\Libraries;
/*
PT ITSHOP BISNIS DIGITAL
Website: https://itshop.biz.id
Toko Online: ITSHOP Purwokerto (https://Tokopedia.com/itshoppwt, https://Shopee.co.id/itshoppwt, https://Bukalapak.com/itshoppwt)
Dibuat oleh: Hari Wicaksono, S.Kom
05-2023
*/

use App\Modules\Group\Models\GroupModel;

class Permission
{
    var $permission = array();
    public function __construct()
    {
        $group_data = array();
        $user_id = session()->get('id');
        $groups = new GroupModel();
        $group_data = $groups->getGroupById($user_id);
        if ($group_data == NULL) {
            return redirect('restricted', 'refresh');
        } 
        $this->permission = unserialize($group_data['permission']);
    }

    public function init()
    {
        return $this->permission;
    }
}
