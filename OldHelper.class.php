<?php

class OldHelper extends Helper
{

	protected function selectWhere() {
		return " WHERE id_lang=3 AND id_category NOT IN (1,6)"; }

		function selectModyfy(){
			$sql='SELECT ps_modyfy.id_number, ps_product_lang.name, ps_modyfy.date, ps_product.price
			FROM ps_modyfy INNER JOIN ps_product_lang ON ps_modyfy.id_number=ps_product_lang.id_product
			INNER JOIN ps_product ON ps_modyfy.id_number=ps_product.id_product
			WHERE ps_product_lang.id_lang=3
			ORDER BY id_number';
			$s=$this->pdo->prepare($sql);
			$s->execute();
			return $s;
		}

		function deleteMod($id){
			$sql='DELETE FROM ps_modyfy
			WHERE id_number = :id';
			$s=$this->pdo->prepare($sql);
			$s->bindValue(':id', $id);
			$s->execute();
		}

		function __destruct(){
		}
	}
