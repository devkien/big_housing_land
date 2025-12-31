<?php

class Session
{
    public static function get($key)
    {
        return $_SESSION[$key] ?? null;
    }

    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }
}
