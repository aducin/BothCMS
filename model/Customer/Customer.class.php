<?php

class LinuxPlCustomer{

	public function __construct($DBHandler){
		$this->pdo=$DBHandler;
	}

	public function checkIfClientExists($email){
		$sql='SELECT ps_customer.email, ps_customer.id_customer, ps_customer_group.id_group
			FROM ps_customer
			INNER JOIN ps_customer_group ON ps_customer.id_customer=ps_customer_group.id_customer
			WHERE ps_customer.email=:email AND ps_customer_group.id_group=6';
			$result=$this->pdo->prepare($sql);
			$result->bindValue(':email', $email);
			$result->execute();
			$result1 = $result->fetch();
			return($result1['id_group']);
	}

}
