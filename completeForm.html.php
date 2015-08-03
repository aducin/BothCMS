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
	#producerC{
		background-color: #bb9998; width:49.4%;padding-left: 10px;
		border: 2px ridge purple;font-style: italic;
	}
	#quantityText{
	margin-bottom: 11px;margin-left:24.2%;
	}
	#conditionText{
	margin-bottom: 11px;margin-left:25.5%;
	}
	#authorText{
	margin-bottom: 11px;margin-left:45.3%;
	}
	#priceC{
		background-color: #cc9998;width:49.4%;padding-left:10px;
		border: 2px ridge purple;font-style: italic;
	}
	#priceCO{
		background-color: #ce9278;width:49.4%;padding-left:10px;
		border: 2px ridge purple;font-style: italic;
	}
	#priceText{
		margin-bottom: 11px;margin-left:55px;
	}
	#priceTextO{
		margin-bottom: 11px;margin-left:51px;
	}
	#condP{
		background-color: #a59998;width:49.4%;padding-left: 10px;
		border: 2px ridge purple;font-style: italic;
	}
	#indexP{
		background-color: #a59668;width:49.4%;padding-bottom: 10px;padding-left: 10px;
		border: 2px ridge purple;font-style: italic;
	}
	#fieldC{
		background-color: #dddd98;width:700px;
		border: 2px ridge purple;
	}
	#fieldT{
		background-color: #ffff98;width:700px;
		border: 2px ridge purple;
	}
	#quantity{
		background-color: #a59338;width:49.4%;padding-left: 10px;
		border: 2px ridge purple;font-style: italic;}
	#tagText{
		background-color: #fffff5;width:10.2%;
		border: 2px ridge purple;font-style: italic;}
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
	</style>
	<script src="jquery.js" ></script>
	<script src="jscript.js"></script>
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
			<textarea id="text" name="text" rows="1" cols="30"><?php htmlout($QueryResult[1]); ?></textarea>
			<label for="description_short">Krótki opis produktu:<label>
			<textarea id="description_short" name="description_short" rows="1" cols="30"><?php htmlout($QueryResult[3]); ?></textarea>
			<label for="description">Pełny opis produktu:<label>
			<textarea id="description" name="description" rows="3" cols="70"><?php htmlout($QueryResult[2]); ?></textarea>
			<label for="link">Aktualny link do produktu:</label>
			<textarea id="link" name="link" rows="1" cols="30"><?php htmlout($QueryResult[4]); ?></textarea>
			<label for="meta_title">Aktualny Meta-tytuł:</label>
			<textarea id="meta_title" name="meta_title" rows="1" cols="30"><?php htmlout($QueryResult[6]); ?></textarea>
			<label for="meta_description">Aktualny Meta-opis:</label>
			<textarea id="meta_description" name="meta_description" rows="2" cols="30"><?php htmlout($QueryResult[5]); ?></textarea>
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
			<?php if($_GET['action']!='Kompletna edycja w NP'){
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
			<?php if($_GET['action']!='Kompletna edycja w SP'){ 
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
				<fieldset id="submitButton">
				<legend id="legend">W ilu bazach zapisać zmiany</legend><table><tr><td>
				<div id="checkbox">
				<input type="checkbox" name="zmiana" value="zmianaNazwy">Zapisz produkt jako podmieniony</><br>
				<input type="checkbox" name="delete" value="deleteImages">Usuń obecne zdjęcia produktu</><br>
				<select name="howManyBases" id="select">
		<option value="obie">Zapisz zmiany w obu bazach</option>
		<option value="tylko1">Tylko w aktualnej bazie</option></td>
	</select></div>
				<td><div>
						<input type="hidden" name="id" value="<?php htmlout($QueryResult[0]); ?>">
						<input id="submitButton2" type="submit" value="<?php htmlout($completeButton);?>">
					</td></tr></table></div></fieldset>
				<fieldset id="fieldT">
						<legend>Tagi aktywne dla tego produktu:</legend>
						<?php foreach ($this3 as $productTags): ?>
						<form action="?<?php htmlout($tagName);?>" method="post"><div>
							<input type="text" id="tagText" name="tagText[]" size="11" value="<?php htmlout($productTags['name']);?>"</>
							<input type="hidden" name="tagID" value="<?php htmlout($productTags['id']); ?>">
							<?php endforeach; ?>
							</div>
						</fieldset></form><hr size="5" width="99.6%"/><fieldset id="AddTag">
						<legend>Dodaj/usuń tag</legend>
					<form action="?<?php htmlout($tagName);?>Add" method="post"><div><table><tr>
							<td><input type="text" id="tagAddText" name="newtagText" size="20" onchange="if(this.value!=''){ this.form.elements['newTag'].disabled=false; } else { this.form.elements['newTag'].disabled=true; }">
							<input type="hidden" name="id" value="<?php htmlout($QueryResult[0]); ?>">
							<input type="submit" id="newTag" name="newTag" value="Dodaj nowy tag" disabled>
						</td></div></form>
						<form action="?<?php htmlout($tagName);?>Cut" method="post"><div><td>
							<input type="text" id="tagCutText" name="textCut" size="20" onchange="if(this.value!=''){ this.form.elements['cutTag'].disabled=false; } else { this.form.elements['cutTag'].disabled=true; }">
							<input type="hidden" name="id" value="<?php htmlout($QueryResult[0]); ?>">
							<input type="submit" id="newTag" name="cutTag" value="Usuń tag" disabled>
						</td></tr></table></div></form>
				</fieldset>
				</html>