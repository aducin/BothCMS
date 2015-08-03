<!DOCTYPE html>
<?php
if($include==0){
	$zmienna1='Stary panel - zamówienie nr ';
	$zmienna2='Ilość w SP';
	$zmienna3='Obecna ilość (NP)';
	$zmienna4='Ilość po modyfikacji (NP)';
	} else{
	$zmienna1='Nowy panel - zamówienie nr ';
	$zmienna2='Ilość w NP';
	$zmienna3='Obecna ilość (SP)';
	$zmienna4='Ilość po modyfikacji (SP)';
	}; ?>
<html lang="pl">
<head>
	<meta charset="utf-8">
	<title>Wyniki ujednolicania produktów</title>
</head>
<body>
	<h1>Wyniki operacji:</h1>
	<table style="background-color:silver;" border="2" width="50%" cellspacing="10">
		<col style="background-color:grey;"><colgroup span="2" style="background-color:pink;"><colgroup span="1" style="background-color:yellow;">
		<caption><b><?php echo($zmienna1.' '.$_GET['id_number']);?></b></caption>
		<tr><th><b>Numer ID</b></th><th><?php htmlout($zmienna2); ?></th><th><?php htmlout($zmienna3); ?></th><th><?php htmlout($zmienna4); ?></th><th>Uwagi</th></tr>
		<?php
		foreach ($mods as $row): ?>
		<tr>
		<td style="text-align:center;"><?php htmlout($row['product_id']); ?></td>
		<td style="text-align:center;"><?php htmlout($row['quantity']); ?></td>
		<?php 
		$quantity2=$row['quantity'];
		if($zmienna2=='Ilość w SP'){
		$newTry= new NewProduct;
		$newQuery = $newTry->confirmation($row['product_id'], $newpdo);
		$quantity= $newQuery["quantity"]; ?>
		<td style="text-align:center;"><?php htmlout($quantity); ?></td>
		<?php $newQuery = $newTry->updateQuantity($quantity2, $row['product_id'], $newpdo);
		$newQuery = $newTry->confirmation($row['product_id'], $newpdo);
		$quantity2= $newQuery["quantity"];
	}
		elseif($zmienna2=='Ilość w NP'){
		$oldTry= new OldProduct;
		$oldQuery = $oldTry->confirmation($row['product_id'], $oldpdo);
		$quantity= $oldQuery["quantity"]; ?>
			<td style="text-align:center;"><?php htmlout($quantity); ?></td>
			<?php $oldQuery = $oldTry->updateQuantity($quantity2, $row['product_id'], $oldpdo);
			$oldQuery = $oldTry->confirmation($row['product_id'], $oldpdo);
			$quantity2= $oldQuery["quantity"]; } ?>
			<td style="text-align:center;"><?php htmlout($quantity2); ?></td>
			<td style="text-align:center;"><?php 
			if ($quantity!=$quantity2)
			{
				echo 'Zmodyfikowano ilość';}
				else {
					echo'- -';	
				}	; ?></td>
	<?php endforeach; ?>
</table>
<p><a href="?">Nowe wyszukiwanie</a></p>
</body>
</html>
