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
		text-align:center;
	}
	</style>
</head>
<body><?php
if(isset($_GET['action'])and $_GET['action']=='search'){ ?>
<h1 id="h1">Wyniki wyszukiwania po frazie: <?php htmlout($_GET['text']); ?></h1><?php
	if(!isset($searchResult)){
	echo'<b>W bazie danych brak wyników dla podanej frazy!</b>';
	exit();} else{ ?>
	<table id="table">
		<tr><th>Numer ID</th><th><b>Nowa nazwa produktu</b></th><th>Na stanie (NP)</th><th>Cena (NP)</th><th>Cena (SP)</th><th>Opcje</th></tr>
		<?php foreach ($searchResult as $product): ?>
		<tr><td>
		<?php if(!isset($lastId) OR $lastId!=($product['id'])){
					?><center><?php htmlout($product['id']);
					$lastId=($product['id']); ?></td>
					<td id="TD"><b><a href="http://ad9bis.vot.pl/tory-h0/<?php htmlout($product['id']); ?>-cysterna-francuska-primagaz-bttb.html" target="_blank"><?php htmlout($product['name']); ?></a></b>
		<?php echo'<br>'.$product['result']; ?>
		</td><td id="TD"><center><?php echo$product['quantity']; ?></td>
	<td id="TD"><center><?php echo$product['price']; ?>
</td><td id="TD"><center><?php echo$product['price2']; ?></td>
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
		<tr><td><b>Nowy Panel:</b></td><td><center><?php htmlout($newQueryResult['id_product']); ?></td><td><center><?php htmlout($newQueryResult['name']); ?></td><td><center><?php htmlout($newQueryResult['quantity']); ?></td>
			<td><center><?php htmlout($newQueryResult['price']);
			if(isset($newQueryResult['reduct'])){
				?> - <b><?php htmlout($newQueryResult['reduct']['reduction']);?></b><br>
				<?php htmlout($newQueryResult['reduct']['realPrice']);
			}
			?></td>
			<td><form action="?bothChange" method="get">
					<div id="option">
						<input type="hidden" name="id" value="<?php htmlout($newQueryResult['id_product']); ?>">
						<input type="submit" name="shortEdition" value="Zmiana obu przez nowy panel">
						<input type="submit" name="fullEditionN" value="Kompletna edycja w NP">
					</div>
				</form>
			</td></tr>
		<tr><td><b>Stary Panel:</b></td><td><center><?php htmlout($oldQueryResult['id_product']); ?></td><td><center><?php htmlout($oldQueryResult['name']); ?></td><td><center><?php htmlout($oldQueryResult['quantity']); ?></td>
			<td><center><?php htmlout($oldQueryResult['price']);
			if(isset($oldQueryResult['reduct'])){
				?> - <b><?php htmlout($oldQueryResult['reduct']['reduction']);?></b><br>
				<?php htmlout($oldQueryResult['reduct']['realPrice']);} ?></td>
			<td><form action="?" method="get">
					<div id="option">
						<input type="hidden" name="id" value="<?php htmlout($newQueryResult['id_product']); ?>">
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
