<?php namespace RWH\WURFL;

class WURFL
{
    private $wurfl;

    public function __construct($library)
    {
        $this->wurfl = $library;
    }

    public function capability($capability)
    {
        return $this->wurfl->getCapability($capability);
    }

    public function getLibrary()
    {
        return $this->wurfl;
    }

    public function __call($capability, $dummy)
    {
        return $this->capability($capability);
    }
}
