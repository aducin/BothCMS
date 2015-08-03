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
						echo'Obecna ilość produktu w nowej bazie wynosi: <b>'.$quantityNew.'</b>';}
						else if(isset($_GET['changeTabOldAdd']) OR (isset($_GET['changeTabNewAdd'])))
						{
							if(isset($_GET['changeTabOldAdd'])){
								$finish='Kompletna edycja w SP';}
								else{
									$finish='Kompletna edycja w NP';
								}
								echo'Dodanie nowej wartości TAG produktu ID: <b>'.$_POST['id'].'</b>'.' powiodło się!'.'<br>';
								echo'Obecna nazwa nowego TAG: "'.$_POST['newtagText'].'"'.'<br>';
								echo'Obecna wartość ID pola TAG: '.$checkedTagId;?>
								<form action="" method="get">
									<div>
										<input type="hidden" name="id" value="<?php htmlout($_POST['id']);?>">
										<input type="hidden" name="action" value="<?php htmlout($finish); ?>">
										<input type="submit" value="Powrót do produktu">
										</div><?php
									}
									else if(isset($_GET['changeTabOldCut']) OR (isset($_GET['changeTabNewCut'])))
									{
										if(isset($_GET['changeTabOldCut'])){
											$finish='Kompletna edycja w SP';}
											else{
												$finish='Kompletna edycja w NP';
											}
											echo'Usunięcie wartości TAG produktu ID: <b>'.$_POST['id'].'</b>'.' powiodło się!'.'<br>';
											echo'Usunięto TAG: "'.$_POST['textCut'].'"'.'<br>';
											echo'Numer ID pola TAG wynosił: '.$checkedTagId;?>
											<form action="" method="get">
												<div>
													<input type="hidden" name="id" value="<?php htmlout($_POST['id']);?>">
													<input type="hidden" name="action" value="Kompletna edycja w NP">
													<input type="submit" value="Powrót do produktu">
												</div><?php } ?>
											</fieldset>
										</body>
										</html>
