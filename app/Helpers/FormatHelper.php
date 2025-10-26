<?php

namespace App\Helpers;

class FormatHelper
{

    public static function rupiah($angka, $prefix = 'Rp. ')
    {
        if (!is_numeric($angka)) {
            return $prefix . '0';
        }

        return $prefix . number_format($angka, 0, ',', '.');
    }


    public static function persen($decimal, $decimals = 0)
    {
        if (!is_numeric($decimal)) {
            return '0%';
        }

        return number_format($decimal * 100, $decimals) . '%';
    }


    public static function cleanRupiah($rupiah)
    {
        if (is_numeric($rupiah)) {
            return $rupiah;
        }

        return (int) preg_replace('/[^0-9]/', '', $rupiah);
    }


    public static function cleanPersen($persen)
    {
        if (is_numeric($persen)) {
            return $persen / 100;
        }

        $clean = (float) preg_replace('/[^0-9.]/', '', $persen);
        return $clean / 100;
    }

    
    public static function validateStrongPassword($password)
    {
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/', $password);
    }
}
