<?php

class LinuxPlOrder extends Order
{
	protected function getWhereSubquery() {
		return " WHERE id_order = :id_number";
	}
}
