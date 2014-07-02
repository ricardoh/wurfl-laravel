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
        $value = null;

        try {
            $value = $this->wurfl->getCapability($capability);
        } catch (\InvalidArgumentException $e) {
            try {
                $value = $this->wurfl->getVirtualCapability($capability);
            } catch (\InvalidArgumentException $e) {
                $value = null;
            }
        }

        return $value;
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
