<!DOCTYPE html>
<html lang="pl">
<head>
	<meta charset="utf-8">
	<title>Potwierdzenie wykonania operacji</title>
</head>
<body>
	<fieldset>
		<legend>Operacja zakończona powodzeniem</legend>
		<?php 
		if(isset($quantityOld)&&!isset($quantityNew)){
			echo'Wykonanie aktualizacji produktu ID: <b>'.$idOld.'</b> w starej bazie powiodło się!'.'<br>';
			echo'Obecna ilość produktu w starej bazie wynosi: <b>'.$quantityOld,'</b>';
		} 
		else if(isset($quantityNew)&&!isset($quantityOld)){
			echo'Wykonanie aktualizacji produktu ID: <b>'.$idOld.'</b> w nowej bazie powiodło się!'.'<br>';
			echo'Obecna ilość produktu w nowej bazie wynosi: <b>'.$quantityNew,'</b>';
			if(isset($_GET['neworder'])OR isset($_GET['oldorder'])){?>
			<form action="" method="get">
				<div>
					<input type="hidden" name="action" value="search">
					<input type="hidden" name="author" value="">
					<input type="hidden" name="category" value="">
					<input type="hidden" name="idnr" value="">
					<input type="submit" value="Powrót do zamówienia">
					<input type="hidden" name="oldorder" value="<?php
					if (isset($_GET['oldorder'])){
						htmlout($_GET['oldorder']);} ?>">
						<input type="hidden" name="neworder" value="<?php 
						if (isset($_GET['neworder'])){
							htmlout($_GET['neworder']);} ?>"> 
						</div><?php
					} 
				}
				else if(isset($quantityOld)&&isset($quantityNew)){
					echo'Wykonanie aktualizacji produktu ID: <b>'.$idOld.'</b> w obu bazach powiodło się!'.'<br>';
					echo'Obecna ilość produktu w starej bazie wynosi: <b>'.$quantityOld.'</b>'.'<br>';
					echo'Obecna ilość produktu w nowej bazie wynosi: <b>'.$quantityNew.'</b>';
				}
				if(isset($error1)){
					?><fieldset>
					UWAGA!<br>
					<?php htmlout($error1);?>
					</fieldset><?php
				}

