<?php

class LinuxPlOrder extends Order
{
	protected function getWhereSubquery() {
		return " WHERE id_order = :id_number";
	}

	protected function getWhereLessSubquery() {
		return " WHERE ps_orders.id_order = :id_number";
	}
}


