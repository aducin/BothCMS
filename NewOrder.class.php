<?php

class NewOrder extends Order
{
	protected function getWhereStatement() {
		return " WHERE id_order = :id_number";
	}
}
