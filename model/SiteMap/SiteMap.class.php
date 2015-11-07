<?php 

class SitemapFacadeOgicom{

	private $objects = array();
	
	public function __construct($ogicomHandler) {
        $this->objects[0] = new OgicomHelper($ogicomHandler);
        $this->objects[1] = new OgicomProduct($ogicomHandler);
    }

	public function getProductsData() {
        $result=$this->objects[0]->getProductsDateAndLink();
        return $result;
    }

    public function getCategoryData() {
       $result=$this->objects[0]->getCategoryDateAndLink();
       return $result;
    }

    public function getProductImage($id) {
        $result=$this->objects[1]->getWholeImages($id);
        return $result;
    }

    public function getImageTitle($image) {
        $result=$this->objects[1]->getImagesTitles($image);
        return $result;
    }
     
}
