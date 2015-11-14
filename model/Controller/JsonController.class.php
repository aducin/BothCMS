<?php

class JsonController extends Controller
{
	private $product1;
	private $product2;

	public function __construct($firstDBHandler, $secondDBHandler){
		$this->pdo=$firstDBHandler;
		$this->secondPDO=$secondDBHandler;
		$this->product1=new LinuxPlProduct($this->pdo);
		$this->product2=new OgicomProduct($this->secondPDO);
	}

	public function searchNames($id){
		$Name=$this->product1->getName($id);
		$Quantity1=$this->product1->getQuantity($id);
		$Quantity2=$this->product2->getQuantity($id);
		$data='{ "nameLinux":"'.$Name.'", "quantityLinux":"'.$Quantity1.'", "quantityOgicom":"'.$Quantity2.'" }';
		return $data;
	}

	public function updateJsonQuantities($id, $quantity){
		$intQuantity = intval($quantity);
		$previousQuantity=$this->product1->getQuantity($id);
		$this->product1->updateQuantity($id, $intQuantity);
		$this->product2->updateQuantity($id, $intQuantity);
		$newQuantity=$this->product1->getQuantity($id);
		$data='{"obj1":{ "propertyA":"'.$id.'", "propertyB":"'.$newQuantity.'", "propertyC":"'.$previousQuantity.'"} }';
		return $data;
	}

	public function autoComplete($searchQuery){
		if(preg_match('/^[a-zA-z0-9]$/D',$searchQuery[0])){
			$data='tooShort';
		}else{
			$params[]=array('text'=>$searchQuery[0],'category'=>$searchQuery[1],'author'=>$searchQuery[2]);
			foreach ($params as $result){
				$prequery[]=" AND name LIKE '%".$searchQuery[0]."%'";
				if ($result['category'] == "notSelected"){ 
					unset($result['category']);
				}else{
					$prequery[]= " id_category =".$searchQuery[1];
				}
				if ($result['author'] =="notSelected") {
					unset($result['author']);
				}else{
					$prequery[]= " id_manufacturer =".$searchQuery[2];
				}
			}
			$implodeSelect=" WHERE id_lang=3".implode(" AND",$prequery)." GROUP BY ps_product_lang.id_product ORDER BY ps_product_lang.id_product";
			//$json=new Json($this->pdo, $this->secondPDO);
			$names= $this->product2->getTypedProductsQuery($implodeSelect);
			$total = $names->rowCount();
			if($total>0){
				foreach ($names as $result){
					$quantity=$this->product2->getQuantity($result['id_product']);
					$data[]=array('id'=>$result['id_product'], 'name'=>$result['name'], 'quantity'=>$quantity);
				}
			}else{
				$data = 'null';
			}
		}
		return $data;
	}
}