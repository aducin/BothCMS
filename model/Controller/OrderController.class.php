<?php

class OrderController extends Controller
{
	public function getOrderDetails($orderNumber){
		$order= new OgicomOrder($this->secondPDO);
		$details = $order->getQueryDetails($orderNumber);
		foreach ($details as $sDetail){
			$detail2[]=array('id'=>$sDetail['product_id'], 'name'=>$sDetail['name'], 'price'=>number_format($sDetail['product_price'], 2,'.',''), 'reduction'=>number_format($sDetail['reduction_amount'], 2,'.',''), 'reducedPrice'=>($sDetail['product_price']-$sDetail['reduction_amount'])*0.85, 'quantity'=>$sDetail['product_quantity'], 'reducedTotalPrice'=>($sDetail['product_price']-$sDetail['reduction_amount'])*$sDetail['product_quantity']*0.85);
			}
		return $detail2;	
	}

	public function orderCheck($orderNumber){
		$order= new OgicomOrder($this->secondPDO);
		$orderSearch = $order->checkIfVoucherDue($orderNumber);
		$totalProducts= array('total'=>$orderSearch['total_products'], 'idCustomer'=>$orderSearch['id_customer']);
		return $totalProducts;
	}

	public function sendNotification($baseName, $notification) {
		if($baseName=='ogicom'){
			$order= new OgicomOrder($this->secondPDO);
		}elseif($baseName=='linuxPl'){
			$order= new LinuxPlOrder($this->pdo);
		}
		$notificationresult = $order->sendNotification($notification);
		return $notificationresult;
	}
}