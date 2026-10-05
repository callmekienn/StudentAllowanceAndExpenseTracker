<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['tt']['start'])) {
    $_SESSION['tt']['start'] = '2026-01-01';
}

if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('getCategoryKeys')) {
    function getCategoryKeys() {
        return ['food', 'transpo', 'schools', 'savings', 'others'];
    }
}

if (!function_exists('getCategoryLabel')) {
    function getCategoryLabel($key) {
        $labels = [
            'food' => 'Food',
            'transpo' => 'Transportation',
            'schools' => 'School Supplies',
            'savings' => 'Savings',
            'others' => 'Others'
        ];
        return $labels[$key] ?? ucfirst($key);
    }
}

if (!function_exists('getCategoryClass')) {
    function getCategoryClass($key) {
        return 'dot-' . $key;
    }
}

if (!function_exists('formatPeso')) {
    function formatPeso($amount) {
        return '₱' . number_format((float)$amount, 2);
    }
}

if (!function_exists('setFlash')) {
    function setFlash($message) {
        $_SESSION['flash'] = $message;
    }
}

if (!function_exists('redirect')) {
    function redirect($url) {
        header("Location: $url");
        exit;
    }
}

if (!function_exists('addTransaction')) {
    function addTransaction($data) {
        $errors = [];
        if (empty($data['amount']) || (float)$data['amount'] <= 0) {
            $errors[] = 'Please enter the correct amount.';
        }
        return $errors;
    }
}


?>