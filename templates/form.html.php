<!DOCTYPE html>
<html lang="pl">
<head>
	<meta charset="utf-8">
	<title>Edycja produktu</title>
	<style type="text/css">
	textarea {
		display: block;
		width: 50%;
	}
	#ramka{
		border: 2px ridge purple; text-align:left;background-color:silver;
		width: 500px;
	}
	#h1{
		width: 500px;
	}
	#nameText{
		width:400px; margin-left: 83px;
	}
	#priceS{
		margin-left: 62px;
	}
	#priceN{
		margin-left: 56px;
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
			function fokus(quantityAdd)
{
    var el = document.getElementById(quantityAdd);
    el.focus();
}
			</script>
		</head>
		<?php if (isset($_GET['shortEdition'])and $_GET['shortEdition']== 'Zmiana obu przez stary panel')
		{
			$baza='- informacje ze starego panelu.';
		}
		else
		{
			$baza='- informacje z nowego panelu.';
		} ?>
<body onload="fokus('quantityAdd');">
	<legend><h1 id="h1">Edycja produktu ID: <?php htmlout($QueryResult[0]);
	echo' ';
	htmlout($baza); ?><h1></legend>
	<form name="form" action="<?php htmlout($editForm); ?>" method="post" onsubmit="wyswietlAlert()">
		<div id="ramka">
			<label for="text">Aktualna nazwa produktu:</label>
			<textarea id="nameText" name="text" rows="1" cols="30"><?php 
if(isset($_GET['shortEdition'])and $_GET['shortEdition']== 'Zmiana obu przez nowy panel')
		{
			htmlout($QueryResult[1]);
		}
		elseif(isset($_GET['shortEdition'])and $_GET['shortEdition']== 'Zmiana obu przez stary panel')
		{
			htmlout($QueryResult2[1]);
		} ?></textarea>
			</div><div id="ramka">
			<label for="quantity">Obecna ilość na sklepie: <?php 
			if(isset($_GET['shortEdition'])and $_GET['shortEdition']== 'Zmiana obu przez nowy panel')
		{
			htmlout($QueryResult[2]);
		}
		elseif(isset($_GET['shortEdition'])and $_GET['shortEdition']== 'Zmiana obu przez stary panel')
		{
			htmlout($QueryResult2[2]);
		} ?><br>
			Podaj ilość do zmiany: <input type="text" id="quantityAdd" name="quantity">
			</label>
			</div>
			<div id="ramka">
				<label for="priceOld">Cena produktu (SP): <?php if(isset($Query4)){
				$product2= new OgicomProduct($secondHost, $secondLogin, $secondPassword);
				$oldQuery = $product2->countReduction($QueryResult2[3],$Query4);
				echo$oldQuery;} ?></label>
				<input type="text" id="priceS" name="nominalPriceOld" size="15" value="<?php htmlout($QueryResult2[3]); ?>"</><br>
				</div><div id="ramka">
				<label for="pricenew">Cena produktu (NP): <?php if(isset($Query3)){
				$product1= new LinuxPlProduct($firstHost, $firstLogin, $firstPassword);
				$newQuery = $product1->countReduction($QueryResult[3],$Query3);
				echo$newQuery;} ?></label>
				<input type="text" id="priceN" name="nominalPriceNew" size="15" value="<?php htmlout($QueryResult[3]); ?>"</>
			</div>
					<div>
						<input type="hidden" name="id" value="<?php htmlout($QueryResult[0]); ?>">
						<input type="submit" value="<?php htmlout($button); ?>">
					</div>
			</form>
		</body>
		</html>
