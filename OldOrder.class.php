<?php

class OldOrder extends Order
{
	protected function getWhereStatement() {
		return " WHERE ps_product_lang.id_lang=3 AND ps_order_detail.id_order = :id_number";
	}

	public function getCount($orderId, $pdo){
		$sql= 'SELECT COUNT(product_name) FROM ps_order_detail 
		WHERE id_order= :id';
		$s=$pdo->prepare($sql);
		$s->bindValue(':id', $orderId);
		$s->execute();
		return $s;
	}
}
