<?php

class Json
{
	private $pdo;
	private $secondPDO;

	public function __construct($firstDBHandler, $secondDBHandler){
		$this->pdo=$firstDBHandler;
		$this->secondPDO=$secondDBHandler;
	}

	protected function getTypedProductSubquery(){
		$sql="SELECT ps_product_lang.name, ps_product_lang.id_product, ps_product_lang.link_rewrite FROM ps_product_lang
		INNER JOIN ps_product ON ps_product_lang.id_product=ps_product.id_product
		INNER JOIN ps_category_product ON ps_product_lang.id_product=ps_category_product.id_product";
		return $sql;
	}

	public function getTypedProductsQuery($where) {
		$sql=$this->getTypedProductSubquery().$where;
		$r= $this->secondPDO->prepare($sql);
		$r->execute();
		return $r;
	}
}