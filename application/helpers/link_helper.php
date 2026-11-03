<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Encrypted (AES-256-GCM) URL-safe tokens that hide the registration number in payment links.

if (!function_exists('pay_link_key')) {
    function pay_link_key() {
        $CI =& get_instance();
        $CI->config->load('link', FALSE, TRUE);
        $secret = (string) $CI->config->item('link_key');
        if ($secret === '') {
            show_error('Payment link key is not configured (application/config/link.php).');
        }
        return hash('sha256', $secret, TRUE);
    }
}

if (!function_exists('make_pay_token')) {
    function make_pay_token($registration) {
        $iv  = random_bytes(12);
        $tag = '';
        $enc = openssl_encrypt($registration, 'aes-256-gcm', pay_link_key(), OPENSSL_RAW_DATA, $iv, $tag);
        return rtrim(strtr(base64_encode($iv . $tag . $enc), '+/', '-_'), '=');
    }
}

if (!function_exists('read_pay_token')) {
    // Returns the registration number, or FALSE when the token is invalid or tampered with.
    function read_pay_token($token) {
        $raw = base64_decode(strtr($token, '-_', '+/'), TRUE);
        if ($raw === FALSE || strlen($raw) < 29) {
            return FALSE;
        }
        $plain = openssl_decrypt(substr($raw, 28), 'aes-256-gcm', pay_link_key(), OPENSSL_RAW_DATA, substr($raw, 0, 12), substr($raw, 12, 16));
        return ($plain === FALSE || $plain === '') ? FALSE : $plain;
    }
}

if (!function_exists('pay_link')) {
    function pay_link($registration) {
        return site_url('welcome/paymentHistory/' . make_pay_token(strtoupper(trim($registration))));
    }
}
