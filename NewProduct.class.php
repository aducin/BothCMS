<?php

class NewProduct extends Product
{
	protected function getWhereProduct() {
		return " WHERE ps_product_lang.id_product= :id_number";
	}

	protected function getWhereProduct2() {
		return " WHERE ps_category_product.id_product= :id";
	}

	protected function getWhereProduct3() {
		return " WHERE id_category NOT IN (1,2,10,14, 15, 19, 20, 22, 23, 24, 25, 26, 27, 32, 33)";
	}

	public function countReduction($price, $reduction) {
		$new0=number_format($price, 2,'.','');
		$new=floatval($reduction);
		$newQueryResult2=$new0-$new0*$new;
		$newQueryResult3=$new*100;
		$newQueryResult4=$newQueryResult3.'%</b>';
		return$newQueryResult2.'zł<br>W tym rabat: <b>'.$newQueryResult4;
	}
}
