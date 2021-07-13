<?php
final class SomeClass
{
    /**
     * @var int
     */
    private $count;
    
    public function __construct()
    {
        $this->count = 123;
    }

    public function getCount():int
    {
        return $this->count;
    }
}