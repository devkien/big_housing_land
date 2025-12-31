<?php

// Small helper utilities for views/controllers

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field()
{
    $token = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
    return "<input type=\"hidden\" name=\"_csrf\" value=\"{$token}\">";
}

function verify_csrf($token)
{
    if (empty($_SESSION['csrf_token'])) return false;
    $ok = hash_equals($_SESSION['csrf_token'], (string)$token);
    // Optionally keep token for multiple forms; do not unset here to allow multiple submissions in same session
    return $ok;
}

function old($key, $default = '')
{
    return isset($_POST[$key]) ? htmlspecialchars($_POST[$key], ENT_QUOTES, 'UTF-8') : $default;
}
