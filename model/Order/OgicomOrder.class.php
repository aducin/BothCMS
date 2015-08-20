<?php

class OgicomOrder extends Order
{
	protected function getWhereSubquery() {
		return " WHERE ps_product_lang.id_lang=3 AND ps_order_detail.id_order = :id_number";
	}

	protected function getWhereLessSubquery() {
		return " WHERE ps_orders.id_order = :id_number";
	}

	public function getCount($orderId){
		$sql= 'SELECT COUNT(product_name) FROM ps_order_detail 
		WHERE id_order= :id';
		$s=$this->pdo->prepare($sql);
		$s->bindValue(':id', $orderId);
		$s->execute();
		$result = $s->fetch();
		return($result);
	}
	public function checkIfVoucherDue($orderId){
		$sql= 'SELECT id_customer, total_products FROM ps_orders 
		WHERE ps_orders.id_order = :id';
		$result=$this->pdo->prepare($sql);
		$result->bindValue(':id', $orderId);
		$result->execute();
		$resultFetch = $result->fetch();
		return($resultFetch);
	}
	public function getOrderCustomerData($customerId){
		$sql= 'SELECT firstname, lastname, email FROM ps_customer
		WHERE ps_customer.id_customer = :id';
		$result=$this->pdo->prepare($sql);
		$result->bindValue(':id', $customerId);
		$result->execute();
		$resultFetch = $result->fetch();
		return($resultFetch);
	}
	public function getVoucherNumber($customerId){
		$sql= 'SELECT ps_orders.id_order, ps_orders.reference, ps_orders.total_products, ps_orders.total_shipping, ps_orders.date_add 
		FROM ps_orders WHERE id_customer = :id AND ps_orders.total_products>=50';
		$result=$this->pdo->prepare($sql);
		$result->bindValue(':id', $customerId);
		$result->execute();
		return($result);
	}
	public function getLastVoucherNumber($customerId){
		$sql= 'SELECT ps_orders.id_order, ps_orders.reference  
		FROM ps_orders WHERE id_customer = :id AND ps_orders.total_products>=50 GROUP BY ps_orders.reference  ORDER BY ps_orders.date_add DESC';
		$r=$this->pdo->prepare($sql);
		$r->bindValue(':id', $customerId);
		$r->execute();
		$result=$r->fetch();
		return($result['reference']);
	}
}
