<!DOCTYPE html>
<html lang="pl">
<head>
	<meta charset="utf-8">
	<title>Kompletna edycja produktu</title>
	<style type="text/css">
	textarea {
		display: block;
		width: 50%;
	}
	#legend{
		font-size: 32px; background-color: #b5fdd9; color: white;border: 2px ridge blue;
	}
	#text{
		margin-left: 0px;
	}
	div#producerC{
		background-color: #bb9998; width:616px;padding-left: 10px;
		border: 2px ridge purple;font-style: italic;
	}
	#priceC{
		background-color: #cc9998;width:616px;padding-left:10px;
		border: 2px ridge purple;font-style: italic;
	}
	#priceCO{
		background-color: #ce9278;width:616px;padding-left:10px;
		border: 2px ridge purple;font-style: italic;
	}
	#condP{
		background-color: #a59998;width:616px;padding-left: 10px;
		border: 2px ridge purple;font-style: italic;
	}
	#indexP{
		background-color: #a59668;width:616px;padding-bottom: 10px;padding-left: 10px;
		border: 2px ridge purple;font-style: italic;
	}
	#quantity{
		background-color: #a59338;width:616px;padding-left: 10px;
		border: 2px ridge purple;font-style: italic;}
	#fieldC{
		background-color: #dddd98;width:700px;
		border: 2px ridge purple;
	}
	#fieldT{
		background-color: #ffff98;width:700px;
		border: 2px ridge purple;
	}
	#tagText{
		background-color: #fffff5;width:10.2%;
		border: 2px ridge purple;font-style: italic;}
	#quantityText{
	margin-bottom: 11px;margin-left:24.2%;
	}
	#conditionText{
	margin-bottom: 11px;margin-left:25.5%;
	}
	#authorText{
	margin-bottom: 11px;margin-left:45.3%;
	}
	#priceText{
		margin-bottom: 11px;margin-left:55px;
	}
	#priceTextO{
		margin-bottom: 11px;margin-left:51px;
	}
	#indexText{
		margin-left:9.6%;}
	#newTag{
		background-color: #ffbc98;padding-left: 14px;padding-right: 14px;
		border: 2px ridge purple;}
	#AddTag{
		width:700px;border: 2px ridge purple;padding-bottom: 22px;background-color: #ffff98;}
	#submitButton{
		font-size: 15px; width:712px;background-color: #ddee98;border: 2px ridge purple;text-align: center;padding-left: 14px;padding-right: 14px; padding-bottom: 28px;
	}
	#submitButton2{
background-color: silver;border: 2px ridge black;font-style: italic; font-size: 15px;}
	#checkbox{
		text-align:left;border: 2px ridge black;margin-left:95px;margin-right:105px;
		background-color: #ddee76;
	}
	#select{
		width:100%;
	}
	#textarea{
		border: 2px ridge black;
	}
	</style>
	<script src="/Ad9bisCMS/assets/jquery.js" ></script>
	<script src="/Ad9bisCMS/assets/jscript.js"></script>
	<script type="text/javascript" language="javascript">
	function wyswietlAlert(){
		if(document.form.quantity.value==""){
			alert("Proszę podać ilość produktu do zapisania!");
			return false;}
			else if(document.form.text.value==""){
				alert("Proszę podać nową nazwę produktu!");
				return false;}
			}
			</script>
		</head>
		<body>
	<legend><h1>Edycja produktu ID: <?php htmlout($QueryResult[0]);
	echo' ';
	htmlout($baza); ?><h1></legend>
	<form name="form" action="<?php htmlout($editForm); ?>" method="post" onsubmit="wyswietlAlert()">
		<div>
			<label for="text">Aktualna nazwa produktu:</label>
			<textarea id="textarea" name="text" rows="1" cols="30"><?php htmlout($QueryResult[1]); ?></textarea>
			<label for="description_short">Krótki opis produktu:<label>
			<textarea id="textarea" name="description_short" rows="1" cols="30"><?php htmlout($QueryResult[3]); ?></textarea>
			<label for="description">Pełny opis produktu:<label>
			<textarea id="textarea" name="description" rows="5" cols="70"><?php htmlout($QueryResult[2]); ?></textarea>
			<label for="link">Aktualny link do produktu:</label>
			<textarea id="textarea" name="link" rows="1" cols="30"><?php htmlout($QueryResult[4]); ?></textarea>
			<label for="meta_title">Aktualny Meta-tytuł:</label>
			<textarea id="textarea" name="meta_title" rows="1" cols="30"><?php htmlout($QueryResult[6]); ?></textarea>
			<label for="meta_description">Aktualny Meta-opis:</label>
			<textarea id="textarea" name="meta_description" rows="2" cols="30"><?php htmlout($QueryResult[5]); ?></textarea>
			<div id="quantity"><label for="quantity">Aktualna ilość produktu:</label>
			<input type="text" id="quantityText" name="quantity" size="11" value="<?php htmlout($QueryResult[7]); ?>"</></div>
			<div id="indexP"><label for="indexP">Aktywność produktu na sklepie:</label>
			<?php $indexTable = array (
				'1'=>
				array('indexed'=>'0','stan'=>'Nieaktywny'),
				'2'=>
				array('indexed'=>'1','stan'=>'Aktywny')
				);?>
			<select name="active" id="indexText">
				<?php foreach ($indexTable as $ind): ?>
					<option value="<?php htmlout($ind['indexed']); ?>"<?php
							if ($ind['indexed'] == $QueryResult[10])
							{
								echo ' selected';
							}
							?>><?php htmlout($ind['stan']); ?></option>
						<?php endforeach; ?></select><br></div>
			<div id="condP"><label for="condition">Aktualny stan produktu:</label>
			<?php $tablica = array (
				'1'=>
				array('condition'=>'new','stan'=>'Nowy'),
				'2'=>
				array('condition'=>'used','stan'=>'Używany'),
				'3'=>
				array('condition'=>'refurbished','stan'=>'Odnowiony')
				);?>
			<select name="condition" id="conditionText">
				<?php foreach ($tablica as $cond): ?>
					<option value="<?php htmlout($cond['condition']); ?>"<?php
							if ($cond['condition'] == $QueryResult[8])
							{
								echo ' selected';
							}
							?>><?php htmlout($cond['stan']); ?></option>
						<?php endforeach; ?>
					</select><br></div><div id="producerC">
					<label for="author">Producent:</label>
					<select name="author" id="authorText">
						<option value="">Wybierz producenta</option>
						<?php foreach ($authors as $authori): ?>
						<option value="<?php htmlout($authori['id']); ?>"<?php
						if ($authori['name'] == $Query6)
						{
							echo ' selected';
						}
						?>><?php htmlout($authori['name']); ?></option>
					<?php endforeach; ?>
					</select><br>
				</div>
			<?php if(isset($_GET['fullEditionO'])){
				$button= 'Aktualizuj produkt w starej bazie';
				$completeButton="Uaktualnij produkt (SB)";
			$tagName='changeTabOld'; $buttonCat='Aktualizuj kategorie w SB';
			$catName='changeCatOld'; ?>
			<div id="priceC">
			<label for="priceOld">Nominalna cena produktu (SP):</label>
			<input type="text" id="priceText" name="nominalPriceOld" size="11" value="<?php htmlout($QueryResult[9]); ?>"</>
			<?php 
				if ($Query3!='') {
					$new=floatval($Query3[0]);
					$new0=number_format($new, 2,'.','');
					echo '<br>'.'UWAGA! Rabat wynosi '.$new0.' zł';
				} ?>
				</div><div id="priceCO">
			<label for="pricenew">Nominalna cena produktu (NP):</label>
			<input type="text" id="priceTextO" name="nominalPriceNew" size="11" value="<?php htmlout($QueryResult2[3]); ?>"</>
			<?php 
				if ($Query5!='') {
					$new=floatval($Query5[0]);
					$new0=$new*100;
					echo '<br>'.'UWAGA! Rabat wynosi '.$new0.'% wartości';
				}} ?>
			</div>
			<?php if(isset($_GET['fullEditionN'])){ 
				$button= 'Aktualizuj produkt w nowej bazie';
				$completeButton="Uaktualnij produkt (NB)";
				$tagName='changeTabNew'; $buttonCat='Aktualizuj kategorie w NB';
				$catName='changeCatNew'; ?>
			<div id="priceC">
			<label for="pricenew">Nominalna cena produktu (NP):</label>
			<input type="text" id="priceText" name="nominalPriceNew" size="11" value="<?php htmlout($QueryResult[9]); ?>"</>
			<?php 
				if ($Query3!='') {
					$new=floatval($Query3[0]);
					$new0=$new*100;
					echo '<br>'.'UWAGA! Rabat wynosi '.$new0.'% wartości';
				} ?>
			</div>
			<div id="priceCO">
			<label for="priceOld">Nominalna cena produktu (SP):</label>
			<input type="text" id="priceTextO" name="nominalPriceOld" size="11" value="<?php htmlout($QueryResult2[3]); ?>"</>
			<?php 
				if ($Query5!='') {
					$new=floatval($Query5[0]);
					$new0=number_format($new, 2,'.','');
					echo '<br>'.'UWAGA! Rabat wynosi '.$new0.' zł';
				}} ?>
			</div>
			<fieldset id="fieldC">
						<legend>Kategorie aktywne dla tego produktu:</legend>
						<?php foreach ($this as $category): ?>
						<div>
							<?php 
							htmlout($category['name']);
							?>
						</div>
					<?php endforeach; ?>
				</fieldset>
				<fieldset id="fieldC">
					<input type="button" name="innyDiv2" value="Ukryj listę kategorii" onclick="$('#próbnyDiv').hide(); this.form.elements['innyDiv2'].disabled=true; $(this).css('background-color','white');this.form.elements['innyDiv'].disabled=false;">
						<input type="button" name="innyDiv" value="Przywróć listę kategorii" onclick="$('#próbnyDiv').show(); this.form.elements['innyDiv'].disabled=true; $(this).css('background-color','white'); this.form.elements['innyDiv2'].disabled=false; " disabled>
						<legend>Kategorie aktywne dla tego produktu:</legend>
						<?php foreach ($this2 as $category): ?>
						<div id="próbnyDiv"><label for="category<?php htmlout($category['id']); ?>">
							<input type="checkbox" name="categories[]" 
							id="category<?php htmlout($category['id']); ?>"
							value="<?php htmlout($category['id']); ?>"<?php
							if ($category['selected'])
							{
								echo ' checked';
							}
							?>><?php htmlout($category['name']); ?></label></div>
						<?php endforeach; ?>
				</fieldset>
				<fieldset id="fieldT">
						<legend>Tagi aktywne dla tego produktu:</legend>
						<input type="text" method="post" name="tagsText" size="80" value="<?php htmlout($completeTagNames);?>"</>
						</fieldset>
				<fieldset id="submitButton">
				<legend id="legend">W ilu bazach zapisać zmiany</legend><table><tr><td>
				<div id="checkbox">
				<input type="checkbox" name="change" value="nameChange">Zapisz produkt jako podmieniony</><br>
				<input type="checkbox" name="delete" value="deleteImages">Usuń obecne zdjęcia produktu</><br>
				<select name="howManyBases" id="select">
		<option value="both">Zapisz zmiany w obu bazach</option>
		<option value="tylko1">Tylko w aktualnej bazie</option></td>
	</select></div>
				<td><div>
						<input type="hidden" name="id" value="<?php htmlout($QueryResult[0]); ?>">
						<input id="submitButton2" type="submit" value="<?php htmlout($completeButton);?>">
					</td></tr></table></div></fieldset>
					<hr size="5" width="99.6%"/>
				</html>
