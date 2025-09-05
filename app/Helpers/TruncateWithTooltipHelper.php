<?php

namespace App\Helpers;

class TruncateWithTooltipHelper
{
    public static function wrapText($text, $limit = 10)
    {
        if (!$text) return '';
        $text = trim($text);
        if (strlen($text) <= $limit) {
            return e($text);
        }
        $truncated = e(substr($text, 0, $limit));
        $full = e($text);
        return '<span title="' . $full . '">' . $truncated . ' <i class="bi bi-info-circle-fill"></i></span>';
    }

}
