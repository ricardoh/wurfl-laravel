<?php namespace RWH\WURFL\Facade;

use Illuminate\Support\Facades\Facade;

class WURFL extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'wurfl';
    }
}
