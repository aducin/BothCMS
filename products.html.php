<!DOCTYPE html>
<html lang="pl">
<head>
	<meta charset="utf-8">
	<title>Wyniki wyszukiwania produktów</title>
	<style type="text/css">
	body{
		background-color: grey;
	}
	#h1{
		text-align:center;
	}
	#table{
		border: 2px ridge brown;width: 92%;
	}
	#TD{border: 1px ridge black;
	}
	#option{
		border: 1px ridge black;padding-top:9px;padding-bottom:9px;background-color:black;
	}
	</style>
</head>
<body><?php
if(isset($_GET['action'])and $_GET['action']=='search'){ ?>
<h1 id="h1">Wyniki wyszukiwania po frazie: <?php htmlout($_GET['text']); ?></h1><?php
	if(!isset($newQuery3)){
	echo'<b>W bazie danych brak wyników dla podanej frazy!</b>';
	exit();} else{ ?>
	<table id="table">
		<tr><th>Numer ID</th><th><b>Nowa nazwa produktu</b></th><th>Na stanie (NP)</th><th>Cena (NP)</th><th>Cena (SP)</th><th>Opcje</th></tr>
		<?php foreach ($newQuery3 as $product): ?>
		<tr><td>
		<?php if(!isset($lastId) OR $lastId!=($product['id']))
		{
					?><center><?php htmlout($product['id']);
					$lastId=($product['id']); ?></td>
					<td id="TD"><b><a href="http://ad9bis.vot.pl/tory-h0/<?php htmlout($product['id']); ?>-cysterna-francuska-primagaz-bttb.html" target="_blank"><?php htmlout($product['name']); ?></a></b><br>
		<?php $product2= new OgicomProduct($secondHost, $secondLogin, $secondPassword);	
		$oldQuery = $product2->getProductQuery($product['id']);
		$oldQueryResult = $oldQuery->fetch();
		if($oldQueryResult[1]!=$product['name'] OR $oldQueryResult[2]!=$product['quantity'])
		{
			echo'<br>Stary panel: <b>'.$oldQueryResult[1].'</b> - ilość to: <b>'.$oldQueryResult[2].'</b>';
		}
		else
		{
			echo'Zgodność nazw i ilości produktu ID 497 w obu bazach.';
		} ?>
	</td><td id="TD"><center><?php htmlout($product['quantity']); ?></td>
<td id="TD"><center><?php $new0=number_format($product['price'], 2,'.','');
$product1= new LinuxPlProduct($firstHost, $firstLogin, $firstPassword);
$newQuery = $product1->getReduction($product['id']);
$newQueryResult = $newQuery->fetch();
			if(!isset($newQueryResult[0])){
			echo$new0.' zł'; }
			else {
				$newQuery1 = $product1->countReduction($product['price'],$newQueryResult[0]);
				echo$newQuery1;	} ?>
</td><td id="TD"><center><?php
$product2= new OgicomProduct($secondHost, $secondLogin, $secondPassword);	
		$oldQuery = $product2->getPrice($product['id']);
		$oldQuery2= $product2->getReduction($product['id']);
		$oldQueryResult2 = $oldQuery2->fetch(); 
		$new0=number_format($oldQuery, 2,'.','');
			if(!isset($oldQueryResult2[0])){
			echo$new0.' zł'; }
			else {
				$new=floatval($oldQueryResult2[0]);
				$new2=number_format($new, 2,'.','');
				$oldQueryResult3=$new0-$new;
				$oldQueryResult4=number_format($oldQueryResult3, 2,'.','');
				echo$oldQueryResult4.'zł<br>W tym rabat: <b>'.$new2.'zł</b>';
			} ?></td>
		<td>
									<form action="?" method="get">
										<div id="option">
											<input type="hidden" name="id" value="<?php htmlout($product['id']); ?>">
											<input type="submit" name="shortEdition" value="Zmiana obu przez nowy panel">
											<input type="submit" name="fullEditionN" value="Kompletna edycja w NP">
										</div>
									</form>
								</td><?php } ?></tr><?php	endforeach;}	} ?></table>
<?php if(isset($_GET['idnr'])): ?>
	<table id="table"> 
		<tr><th></th><th>Numer ID</th><th><b>Nazwa produktu</b></th><th>Na stanie</th><th>Cena</th><th>Opcje</th></tr>
		<tr><td><b>Nowy Panel:</b></td><td><center><?php htmlout($newQueryResult[0]); ?></td><td><center><?php htmlout($newQueryResult[1]); ?></td><td><center><?php htmlout($newQueryResult[2]); ?></td>
			<td><center><?php $new0=number_format($newQueryResult[3], 2,'.','');
			if(!isset($newQueryResult2[0])){
			echo$new0.' zł'; }
			else {
				$product1= new LinuxPlProduct($firstHost, $firstLogin, $firstPassword);
				$newQuery = $product1->countReduction($newQueryResult[3],$newQueryResult2[0]);
				echo$newQuery;
			} ?></td>
			<td><form action="?bothChange" method="get">
					<div id="option">
						<input type="hidden" name="id" value="<?php htmlout($newQueryResult[0]); ?>">
						<input type="submit" name="shortEdition" value="Zmiana obu przez nowy panel">
						<input type="submit" name="fullEditionN" value="Kompletna edycja w NP">
					</div>
				</form>
			</td></tr>
		<tr><td><b>Stary Panel:</b></td><td><center><?php htmlout($oldQueryResult[0]); ?></td><td><center><?php htmlout($oldQueryResult[1]); ?></td><td><center><?php htmlout($oldQueryResult[2]); ?></td>
			<td><center><?php $new0=number_format($oldQueryResult[3], 2,'.','');
			if(!isset($oldQueryResult2[0])){
			echo$new0.' zł'; }
			else {
				$product2= new OgicomProduct($secondHost, $secondLogin, $secondPassword);
				$newQuery = $product2->countReduction($oldQueryResult[3],$oldQueryResult2[0]);
				echo$newQuery;
			} ?></td>
			<td><form action="?" method="get">
					<div id="option">
						<input type="hidden" name="id" value="<?php htmlout($newQueryResult[0]); ?>">
						<input type="submit" name="shortEdition" value="Zmiana obu przez stary panel">
						<input type="submit" name="fullEditionO" value="Kompletna edycja w SP">
					</div>
				</form>
			</td></tr>
	
</table>
<?php endif; ?>
<?php if(isset($product['text'])&&(isset($oldproduct['oldText']))){
	if(($product['text']==$oldproduct['oldText'])AND($product['quantity']==$oldproduct['oldQuantity'])){
		echo '<br>Nazwa oraz ilość produktu nr '.$oldproduct['oldId'].' identyczna w obu panelach.';
	}
}
if(isset($product['text'])&&(isset($oldproduct['oldText']))){
	if($product['text']!=$oldproduct['oldText']){
		echo '<b>NIEZGODNOŚĆ NAZW!</b><br>';
		}
	}
	if(isset($product['quantity'])&&(isset($oldproduct['oldQuantity']))){
	if($product['quantity']!=$oldproduct['oldQuantity']){
		echo '<b>NIEZGODNOŚĆ ILOŚCI!</b><br>';
		}
	}
	?>
<p><a href="?">Nowe wyszukiwanie</a></p>
</body>
</html>
