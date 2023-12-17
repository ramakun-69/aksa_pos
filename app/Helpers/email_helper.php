<?php
/*
PT ITSHOP BISNIS DIGITAL
Website: https://itshop.biz.id
Toko Online: ITSHOP Purwokerto (https://Tokopedia.com/itshoppwt, https://Shopee.co.id/itshoppwt, https://Bukalapak.com/itshoppwt)
Dibuat oleh: Hari Wicaksono, S.Kom
06-2022
*/

function sendEmail($subject, $to, $view)
{
    $email = \Config\Services::email();
    $email->setTo($to);
    $email->setSubject($subject);
    $email->setMessage($view);
    if ($email->send(false)) {
        return true;
    }
    return false;
}