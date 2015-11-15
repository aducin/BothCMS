<?php 

abstract class Controller
{
	protected $pdo;
	protected $secondPDO;
	private $existingClient;

	public function __construct($firstDBHandler, $secondDBHandler){
		$this->pdo=$firstDBHandler;
		$this->secondPDO=$secondDBHandler;
	}

	public function setExistingClient($client){
		$this->existingClient=$client;
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

	public function getUndeliveredData($orderNumber){
		$order= new OgicomOrder($this->secondPDO);
		$confOrderDetail = $order->getQueryDetails($orderNumber);
		foreach ($confOrderDetail as $detail){
			$confOrderDetails[]=array('id'=>$detail['product_id'], 'name'=>$detail['name'], 'price'=>number_format($detail['product_price'], 2,'.',''), 'reduction'=>number_format($detail['reduction_amount'], 2,'.',''), 'quantity'=>$detail['product_quantity']);
		}
		return $confOrderDetails;
	}

	public function getVoucherHistory($customerNumber){
		$ordNumb=0;
		$order= new OgicomOrder($this->secondPDO);
		$voucherHistory= $order->getVoucherNumber($customerNumber);
		foreach ($voucherHistory as $custOrder){
			$ordNumb++;
			$custOrders[]= array('id'=>$custOrder['id_order'], 'reference'=>$custOrder['reference'], 'total'=>$custOrder['total_products'], 'shipping'=>$custOrder['total_shipping'], 'date'=>$custOrder['date_add'], 'orderNumber'=>((($ordNumb-1) % 6) + 1 ));
		}
		return $custOrders;
	}

	public function mergeIntoNewHelper($productNumber){
		$mergeDetails=array('idOrder'=>$productNumber.' w nowym panelu', 'base'=>'Ilość w SP', 'current'=>'Obecna ilość (NP)', 'changed'=>'Ilość po modyfikacji (NP)');
		return $mergeDetails;
	}

	public function mergeIntoNew($productNumber){
		$order= new OgicomOrder($this->secondPDO);
		$product= new LinuxPlProduct($this->pdo);
		$Query = $order->selectOrderQuantity($productNumber);
		foreach ($Query as $Query2){
			$oldQuantity=$product->getQuantity($Query2['product_id']);
			$quantityUpdate=$product->updateQuantity($Query2['quantity'], $Query2['product_id']);
			$finalQuantity=$product->getQuantity($Query2['product_id']);
			$mods[]=array('quantity'=>$Query2['quantity'], 'product_id'=>$Query2['product_id'], 'previousQuantity'=>$oldQuantity, 'finalQuantity'=>$finalQuantity, 'result'=>(string)($finalQuantity-$oldQuantity));
		}
		return $mods;
	}

	public function mergeIntoOldHelper($productNumber){
		$mergeDetails=array('idOrder'=>$productNumber.' w nowym panelu', 'base'=>'Ilość w SP', 'current'=>'Obecna ilość (NP)', 'changed'=>'Ilość po modyfikacji (NP)');
		return $mergeDetails;
	}

	public function mergeIntoOld($productNumber){
		$order= new LinuxPlOrder($this->pdo);
		$product= new OgicomProduct($this->secondPDO);
		$Query = $order->selectOrderQuantity($productNumber);
		foreach ($Query as $Query2){
			$oldQuantity=$product->getQuantity($Query2['product_id']);
			$quantityUpdate=$product->updateQuantity($Query2['quantity'], $Query2['product_id']);
			$finalQuantity=$product->getQuantity($Query2['product_id']);
			$mods[]=array('quantity'=>$Query2['quantity'], 'product_id'=>$Query2['product_id'], 'previousQuantity'=>$oldQuantity, 'finalQuantity'=>$finalQuantity, 'result'=>(string)($finalQuantity-$oldQuantity));
		}
		return $mods;
	}

	public function MergeSingleQuantity($dbNumber, $id, $quantity){
		if($dbNumber==2){
			$product= new LinuxPlProduct($this->pdo);
		}elseif($dbNumber==1){
			$product= new OgicomProduct($this->secondPDO);
		}
		$Query = $product->updateQuantity($quantity, $id);
		$outputOrderOrProduct1 = $product->confirmation($id);
		return $outputOrderOrProduct1;
		unset($product);
	}

	public function getCustomerData($customerNumber){
		$order= new OgicomOrder($this->secondPDO);
		$customerData= $order->getOrderCustomerData($customerNumber);
		if($this->existingClient->checkIfClientExists($customerData['email'])==6){
			$customerData['new'] = $this->existingClient->checkIfClientExists($customerData['email']);
		}
		$customerData['voucherLast']= $order->getLastVoucherNumber($customerNumber);
		$customerData['idCustomer']=$customerNumber;
		return $customerData;
	}
}