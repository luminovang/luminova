<?php 
namespace App\Controllers\Config;

class ServiceTest 
{ 
    private $name;

    public function __construct(string $name){
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }
}