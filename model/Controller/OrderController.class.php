<?php

class OrderController extends Controller
{
	private $LinuxPlOrder;
	private $OgicomOrder;
	private $existingClient;

	public function setLinuxPlOrder(){
		$this->LinuxPlOrder=new LinuxPlOrder($this->pdo);
	}

	public function setOgicomOrder(){
		$this->OgicomOrder=new OgicomOrder($this->secondPDO);
	}

	public function setExistingClient($client){
		$this->existingClient=$client;
	}

	public function getOrderDetails($orderNumber){
		$details = $this->OgicomOrder->getQueryDetails($orderNumber);
		foreach ($details as $sDetail){
			$detail2[]=array('id'=>$sDetail['product_id'], 'name'=>$sDetail['name'], 'price'=>number_format($sDetail['product_price'], 2,'.',''), 'reduction'=>number_format($sDetail['reduction_amount'], 2,'.',''), 'reducedPrice'=>($sDetail['product_price']-$sDetail['reduction_amount'])*0.85, 'quantity'=>$sDetail['product_quantity'], 'reducedTotalPrice'=>($sDetail['product_price']-$sDetail['reduction_amount'])*$sDetail['product_quantity']*0.85);
			}
		return $detail2;	
	}

	public function orderCheck($orderNumber){
		$orderSearch = $this->OgicomOrder->checkIfVoucherDue($orderNumber);
		$totalProducts= array('total'=>$orderSearch['total_products'], 'idCustomer'=>$orderSearch['id_customer']);
		return $totalProducts;
	}

	public function sendNotification($baseName, $notification) {
		if($baseName=='ogicom'){
			$order= $this->OgicomOrder;
		}elseif($baseName=='linuxPl'){
			$order= $this->LinuxPlOrder;
		}
		$notificationresult = $order->sendNotification($notification);
		return $notificationresult;
	}

	public function getOrderInformations($option, $orderNumber){
		if($option==1){
			$query = $this->LinuxPlOrder->getQuery($orderNumber);
			$product= new OgicomProduct($this->secondPDO);
		}else{
			$query = $this->OgicomOrder->getQuery($orderNumber);
			$product= new LinuxPlProduct($this->pdo);
			$product2= new OgicomProduct($this->secondPDO);
		}
		foreach ($query as $query2){
			$otherQuery = $product->getProductQuery($query2['product_id']);
			$otherQuery2 = $otherQuery->fetch();
			if($option==1){
				$imageNumber= $product->image($query2['product_id']);
			}elseif($option==2){
				$imageNumber= $product2->image($query2['product_id']);
			}
			if(($otherQuery2['name']==$query2['name'])AND($otherQuery2['quantity']==$query2['quantity'])){
				$queryResult="Zgodność ilości i nazw produktu nr ".$otherQuery2['id_product'];
			}elseif(($otherQuery2['name']==$query2['name'])AND($otherQuery2['quantity']!=$query2['quantity'])){
				$queryResult="Ilość produktu ".$otherQuery2['id_product']." w drugim panelu to: ".$otherQuery2['quantity'];
			}elseif(($otherQuery2['name']!=$query2['name'])AND($otherQuery2['quantity']==$query2['quantity'])){
				$queryResult="Nazwa produktu ".$otherQuery2['id_product']." w drugiej bazie to: ".$otherQuery2['name'];
			}elseif(($otherQuery2['name']!=$query2['name'])AND($otherQuery2['quantity']!=$query2['quantity'])){
				$queryResult="Podwójna niezgodność - (SB): ".$otherQuery2['name'].", a ilość to: ".$oldQuery2['quantity'];
			}
			$result[]=array('id'=>$query2['product_id'], 'name'=>$query2['name'], 'onStock'=>$query2['product_quantity'], 'quantity'=>$query2['quantity'], 'nameResult'=>$queryResult, 'imgNumber'=>$imageNumber);
		}
		if (!isset($result[0]['id'])){
			$result=0;
		}
		return $result;
	}

	public function checkOrderDetail($orderNumber){
		$nameDetails = $this->OgicomOrder->getQueryLessDetails($orderNumber);
		$nameDetails['reducedTotalProduct']=$nameDetails['total_products']*0.85;
		$nameDetails['orderNumber']=$orderNumber;
		$nameDetails['reducedTotal']=$nameDetails['total_paid']*0.85;
		$detailsCount=$this->OgicomOrder->getCount($orderNumber);
		$nameDetails['count'] = $detailsCount['COUNT(product_name)'];
		return $nameDetails;
	}

	public function checkUndeliveredData($orderNumber){
		$confOrderData = $this->OgicomOrder->getQueryLessDetails($orderNumber);
		$confOrderData['ordNumb']=$orderNumber;
		return $confOrderData;
	}

	public function getCustomerData($customerNumber){
		$customerData= $this->OgicomOrder->getOrderCustomerData($customerNumber);
		if($this->existingClient->checkIfClientExists($customerData['email'])==6){
			$customerData['new'] = $this->existingClient->checkIfClientExists($customerData['email']);
		}
		$customerData['voucherLast']= $this->OgicomOrder->getLastVoucherNumber($customerNumber);
		$customerData['idCustomer']=$customerNumber;
		return $customerData;
	}

	public function getVoucherHistory($customerNumber){
		$ordNumb=0;
		$voucherHistory= $this->OgicomOrder->getVoucherNumber($customerNumber);
		foreach ($voucherHistory as $custOrder){
			$ordNumb++;
			$custOrders[]= array('id'=>$custOrder['id_order'], 'reference'=>$custOrder['reference'], 'total'=>$custOrder['total_products'], 'shipping'=>$custOrder['total_shipping'], 'date'=>$custOrder['date_add'], 'orderNumber'=>((($ordNumb-1) % 6) + 1 ));
		}
		return $custOrders;
	}

	public function getUndeliveredData($orderNumber){
		$confOrderDetail = $this->OgicomOrder->getQueryDetails($orderNumber);
		foreach ($confOrderDetail as $detail){
			$confOrderDetails[]=array('id'=>$detail['product_id'], 'name'=>$detail['name'], 'price'=>number_format($detail['product_price'], 2,'.',''), 'reduction'=>number_format($detail['reduction_amount'], 2,'.',''), 'quantity'=>$detail['product_quantity']);
		}
		return $confOrderDetails;
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

	public function mergeIntoOld($productNumber){
		$product= new OgicomProduct($this->secondPDO);
		$Query = $this->LinuxPlOrder->selectOrderQuantity($productNumber);
		foreach ($Query as $Query2){
			$oldQuantity=$product->getQuantity($Query2['product_id']);
			$quantityUpdate=$product->updateQuantity($Query2['quantity'], $Query2['product_id']);
			$finalQuantity=$product->getQuantity($Query2['product_id']);
			$mods[]=array('quantity'=>$Query2['quantity'], 'product_id'=>$Query2['product_id'], 'previousQuantity'=>$oldQuantity, 'finalQuantity'=>$finalQuantity, 'result'=>(string)($finalQuantity-$oldQuantity));
		}
		return $mods;
	}

	public function mergeIntoNew($productNumber){
		$product= new LinuxPlProduct($this->pdo);
		$Query = $this->OgicomOrder->selectOrderQuantity($productNumber);
		foreach ($Query as $Query2){
			$oldQuantity=$product->getQuantity($Query2['product_id']);
			$quantityUpdate=$product->updateQuantity($Query2['quantity'], $Query2['product_id']);
			$finalQuantity=$product->getQuantity($Query2['product_id']);
			$mods[]=array('quantity'=>$Query2['quantity'], 'product_id'=>$Query2['product_id'], 'previousQuantity'=>$oldQuantity, 'finalQuantity'=>$finalQuantity, 'result'=>(string)($finalQuantity-$oldQuantity));
		}
		return $mods;
	}

		public function mergeIntoNewHelper($productNumber){
		$mergeDetails=array('idOrder'=>$productNumber.' w nowym panelu', 'base'=>'Ilość w SP', 'current'=>'Obecna ilość (NP)', 'changed'=>'Ilość po modyfikacji (NP)');
		return $mergeDetails;
	}

	public function mergeIntoOldHelper($productNumber){
		$mergeDetails=array('idOrder'=>$productNumber.' w nowym panelu', 'base'=>'Ilość w SP', 'current'=>'Obecna ilość (NP)', 'changed'=>'Ilość po modyfikacji (NP)');
		return $mergeDetails;
	}
}