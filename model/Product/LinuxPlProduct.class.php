<?php

class LinuxPlProduct extends Product
{
	protected function getWhereProductSubquery() {
		return " WHERE ps_product_lang.id_product= :id_number";
	}

	protected function getWhereCategorySubquery() {
		return " WHERE ps_category_product.id_product= :id";
	}

	protected function getWhereEveryCategorySubquery() {
		return " WHERE id_category NOT IN (1,2,10,14, 15, 19, 20, 22, 23, 24, 25, 26, 27, 32, 33)";
	}

	public function countReduction($price, $reduction) {
		$new0=number_format($price, 2,'.','');
		$new=floatval($reduction);
		$newQueryResult2=$new0-$new0*$new;
		$newQueryResult22=number_format($newQueryResult2, 2,'.','');
		$newQueryResult3=$new*100;
		$newQueryResult4=$newQueryResult3."%";
		return$newQueryResult22.'zł.'."\r\n".'W tym rabat: '.$newQueryResult4;
	}
	public function countRealPrice($price, $reduction) {
		$new0=number_format($price, 2,'.','');
		$new=floatval($reduction);
		$newQueryResult2=$new0-$new0*$new;
		$newQueryResult22=number_format($newQueryResult2, 2,'.','');
		$newQueryResult3=$new*100;
		$newQueryResult4=$newQueryResult3."%";
		$result=array('reduction'=>'Rabat: '.$newQueryResult4, 'realPrice'=>'Cena rzeczywista: '.$newQueryResult22.'zł');
		return$result;
	}

	public function getEveryName(){
	$sql='SELECT name, id_product FROM ps_product_lang
	ORDER BY name';
	$c= $this->pdo->prepare($sql);
	$c->bindValue(':id', $productId);
	$c->execute();
	return$c;
	}
	
}
