<?php

abstract class Order
{
	public function __construct($DBHandler){
		$this->pdo=$DBHandler;
	}

	protected $idOrder;

	private function getSelectSubquery($orderId) {
		$this->idOrder=$orderId;
		return 'SELECT ps_order_detail.product_id, ps_order_detail.id_order, ps_product_lang.name, ps_order_detail.product_quantity, ps_stock_available.quantity FROM ps_order_detail
		INNER JOIN ps_product_lang ON ps_order_detail.product_id=ps_product_lang.id_product
		INNER JOIN ps_stock_available ON ps_order_detail.product_id=ps_stock_available.id_product';
	}

	private function getQueryDetailsSubquery($orderId) {
		$this->idOrder=$orderId;
		return 'SELECT ps_order_detail.product_id, ps_order_detail.id_order, ps_order_detail.product_price, ps_order_detail.reduction_amount, ps_order_detail.total_price_tax_incl, ps_order_detail.product_quantity, ps_product_lang.name, ps_orders.total_products, ps_orders.total_paid, ps_orders.reference, ps_orders.payment,ps_orders.id_customer, ps_customer.email, ps_customer.firstname, ps_customer.lastname
		FROM ps_order_detail 
		INNER JOIN ps_product_lang ON ps_order_detail.product_id=ps_product_lang.id_product
		INNER JOIN ps_orders ON ps_order_detail.id_order=ps_orders.id_order
		INNER JOIN ps_customer ON ps_orders.id_customer=ps_customer.id_customer';
	}

	private function getQueryLessDetailsSubquery($orderId) {
		$this->idOrder=$orderId;
		return 'SELECT ps_orders.total_products, ps_orders.total_paid, ps_orders.reference, ps_orders.payment,ps_orders.id_customer, ps_customer.email, ps_customer.firstname, ps_customer.lastname
		FROM ps_orders
		INNER JOIN ps_customer ON ps_orders.id_customer=ps_customer.id_customer';
	}

	private function sendNotificationSubquery($orderId) {
		$this->idOrder=$orderId;
		return 'SELECT ps_orders.id_order, ps_orders.reference, ps_orders.id_customer, ps_customer.email, ps_customer.firstname, ps_customer.lastname
		FROM ps_orders
		INNER JOIN ps_customer ON ps_orders.id_customer=ps_customer.id_customer
		WHERE ps_orders.id_order=:id_number';
	}

	abstract protected function getWhereSubquery();

	public function getQuery($orderId) {
		$sql=$this->getSelectSubquery($orderId) . $this->getWhereSubquery();
		$c=$this->pdo->prepare($sql);
		$c->bindValue(':id_number', $orderId);
		$c->execute();
		return $c;
	}

	public function getQueryDetails($orderId) {
		$sql=$this->getQueryDetailsSubquery($orderId) . $this->getWhereSubquery();
		$c=$this->pdo->prepare($sql);
		$c->bindValue(':id_number', $orderId);
		$c->execute();
		return $c;
	}
	
	public function getQueryLessDetails($orderId) {
		$sql=$this->getQueryLessDetailsSubquery($orderId) . $this->getWhereLessSubquery();
		$c=$this->pdo->prepare($sql);
		$c->bindValue(':id_number', $orderId);
		$c->execute();
		$return=$c->fetch();
		return $return;
	}

	public function sendNotification($orderId){
		$sql=$this->sendNotificationSubquery($orderId);
		$c=$this->pdo->prepare($sql);
		$c->bindValue(':id_number', $orderId);
		$c->execute();
		$return=$c->fetch();
		return $return;
	}

	public function selectOrderQuantity($id_number){
		$sql='SELECT ps_stock_available.quantity, ps_order_detail.product_id, ps_order_detail.id_order FROM ps_stock_available
		INNER JOIN ps_order_detail ON ps_order_detail.product_id=ps_stock_available.id_product
		WHERE ps_order_detail.id_order = :id_number';
		$s=$this->pdo->prepare($sql);
		$s->bindValue(':id_number', $id_number);
		$s->execute();
		return $s;
	}
}
