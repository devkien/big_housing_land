<?php

class Model
{
    protected static function db()
    {
        return Database::connect();
    }
}
