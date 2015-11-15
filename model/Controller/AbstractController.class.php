<?php 

abstract class Controller
{
	protected $pdo;
	protected $secondPDO;

	public function __construct($firstDBHandler, $secondDBHandler){
		$this->pdo=$firstDBHandler;
		$this->secondPDO=$secondDBHandler;
	}

	public function getHelpers(){
		$helper= new OgicomHelper($this->secondPDO);
		$result= $helper->selectWholeManufacturer();
		foreach ($result as $row){
			$authors[]= array('id'=> $row['id_manufacturer'], 'name'=> $row['name']);
		}
		$result= $helper->getCategoryData();
		foreach ($result as $row){
			$categories[]= array('id'=>$row['id_category'], 'name'=>$row['meta_title']);
		}
		$result= $helper->getModyfiedData();
		$product= new OgicomProduct($this->secondPDO);
		foreach ($result as $mod){
			$productReduction=$product->getReductionData($mod['id_number']);
			$mods[]= array('id'=>$mod['id_number'], 'nazwa'=>$mod['name'], 'data'=>$mod['date'], 'cena'=>number_format($mod['price'], 2,'.','').'zł', 'reduction'=>$productReduction);
		}
		return array($authors, $categories, $mods);
	}

	public function getOgicomImage($idNumber){
		$product= new OgicomProduct($this->secondPDO);
		$imageNumber= $product->image($idNumber);
		return $imageNumber;
	}
}