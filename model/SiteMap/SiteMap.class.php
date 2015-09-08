<?php 

class SitemapFacadeOgicom{

	private $objects = array();
	
	public function __construct($ogicomHandler) {
        $this->objects[0] = new OgicomHelper($ogicomHandler);
        $this->objects[1] = new OgicomProduct($ogicomHandler);
    }

	//public function __construct($DBHandler){
	//	$this->pdo=$DBHandler;
	//}

	public function method1() {
        $result=$this->objects[0]->getProductsDateAndLink();
        return $result;
    }

    public function method2() {
       $result=$this->objects[0]->getCategoryDateAndLink();
       return $result;
    }

    public function method3($id) {
        $result=$this->objects[1]->getWholeImages($id);
        return $result;
    }

    public function method4($image) {
        $result=$this->objects[1]->getImagesTitles($image);
        return $result;
    }
     
}
