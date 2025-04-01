<?php
if (!function_exists('e')) {
    function e($value) {
        if ($value === null) return '';
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}
?>
