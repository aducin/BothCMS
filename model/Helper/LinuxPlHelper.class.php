<?php

class LinuxPlHelper extends Helper{

	protected function getWhereSubquery() {
		return " WHERE id_category NOT IN (1,2,10,11,12,14, 15, 19, 20, 21,22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 38, 39, 40, 42,43,46,47,52,53)";
	}

	function __destruct(){
	}
}
