<?php

namespace App\Helpers;

class NumberFormatterHelper
{
    public static function formatQty($amount,$currency): string
    {

        if($amount && $amount==0 || $amount=='0'|| $amount==NULL){
            return 0;
        }
        return match ($currency) {
            '₹' => self::formatINR($amount),
            'रु' => self::formatNPR($amount),
            '$' => self::formatUSD($amount),
            // default =>  round($amount, 2),
            default => sprintf("%.2f", (float)$amount),
        };
    }
    public static function formatCurrency($amount,$currency): string
    {
        // $currency = session('user_currency')['symbol'] ?? '₹'; // Safely get currency symbol
        if($amount==0 || $amount=='0'){
            return $currency .' '.$amount;
        }
        return match ($currency) {
            '₹' => '₹ ' .self::formatINR($amount),
            'रु' => 'रु' .self::formatNPR($amount),
            '$' => '$ ' .self::formatUSD($amount),
            // default =>  round($amount, 2),
            default => $currency . ' ' . sprintf("%.2f", (float)$amount),
        };
    }
    

    public static function formatINR($amount): string
    {
        $amount = (float)$amount;
        $amountFormatted = number_format($amount, 2, '.', '');

        [$intPart, $decimalPart] = explode('.', $amountFormatted);

        $lastThree = substr($intPart, -3);
        $restUnits = substr($intPart, 0, -3);

        if ($restUnits != '') {
            $restUnits = preg_replace("/\B(?=(\d{2})+(?!\d))/", ",", $restUnits);
        }

        $formatted = ($restUnits != '') ? $restUnits . ',' . $lastThree : $lastThree;

        return $formatted . '.' . $decimalPart;
    }


    public static function formatNPR($amount): string
    {
        return self::formatINR($amount);
    }


    public static function formatUSD($amount): string
    {
        return  number_format((float) $amount, 2, '.', ',');
    }
}
