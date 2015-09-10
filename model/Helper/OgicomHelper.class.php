<?php

class OgicomHelper extends Helper
{

	protected function getWhereSubquery() {
		return " WHERE id_lang=3 AND id_category NOT IN (1,6)"; 
	}

	function getModyfiedData(){
		$sql='SELECT ps_modyfy.id_number, ps_product_lang.name, ps_modyfy.date, ps_product.price
		FROM ps_modyfy INNER JOIN ps_product_lang ON ps_modyfy.id_number=ps_product_lang.id_product
		INNER JOIN ps_product ON ps_modyfy.id_number=ps_product.id_product
		WHERE ps_product_lang.id_lang=3
		ORDER BY id_number';
		$s=$this->pdo->prepare($sql);
		$s->execute();
		return $s;
	}

	function deleteModyfied($id){
		$sql='DELETE FROM ps_modyfy
		WHERE id_number = :id';
		$s=$this->pdo->prepare($sql);
		$s->bindValue(':id', $id);
		$s->execute();
	}

	public function getProductsDateAndLink(){
		$sql='SELECT ps_product.id_product, date_upd, ps_product_lang.link_rewrite, ps_category_lang.link_rewrite as link FROM ps_product
		INNER JOIN ps_product_lang ON ps_product.id_product=ps_product_lang.id_product
		INNER JOIN ps_category_lang ON ps_product.id_category_default=ps_category_lang.id_category
		WHERE ps_product_lang.id_lang=3 AND ps_category_lang.id_lang=3 
		ORDER BY id_product LIMIT 100';
		$result=$this->pdo->prepare($sql);
		$result->execute();
		return $result;
	}

	public function getCategoryDateAndLink(){
		$sql='SELECT ps_category.id_category, date_upd, link_rewrite FROM ps_category 
		INNER JOIN ps_category_lang ON 
		ps_category.id_category=ps_category_lang.id_category WHERE ps_category.id_category NOT IN (1) AND id_lang=3 GROUP BY ps_category.id_category';
		$result=$this->pdo->prepare($sql);
		$result->execute();
		return $result;
	}

	function __destruct(){
	}
}
