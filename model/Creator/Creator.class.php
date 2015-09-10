<?php

interface Creator{

    public function createProduct($item);
}

class ProductCreator implements Creator{

	private $LinuxPl;
	private $Ogicom;

	public function __construct($firstDBHandler, $secondDBHandler){
		$this->LinuxPl=$firstDBHandler;
		$this->Ogicom=$secondDBHandler;
	}

    public function createProduct($item) {
        switch($item) {
            case 'LinuxPl':
                return new LinuxPlProduct($this->LinuxPl);
                break;
            case 'Ogicom':
                return new OgicomProduct($this->Ogicom);
                break;
        }
    }
}