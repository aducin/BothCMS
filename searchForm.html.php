<!DOCTYPE html>
<html lang="pl">
<head>
	<meta charset="utf-8">
	<link rel="shortcut icon" href="ad9bis.ico" type="image/x-icon" />
	<title>Zarządzanie produktami</title>
	<script src="/Ad9bisCMS/assets/jquery.js" ></script>
	<script src="/Ad9bisCMS/assets/jscript.js"></script>
	<style type="text/css">
	body{
		background-color: #ab9999;
	}
	#change{
		display: inline;
	list-style: none;
	}
	#legendMod{
	font-size:25px;
	}
	#fldset1{
background-color: #aa7768;width:48%;border: 4px ridge black;
	}
	#fldset{
background-color: #aa7768;width:48%;border: 4px ridge black;
	}
	#fldmod{
background-color: #aa7657;width:48%;border: 4px ridge black;
	}
	h1 {
		width:50%; text-align:center;
	}
	h2 {
		text-align:center;
	}
	#searchList{
		font-size:14pt; font-style:bold;
	}
	#categoryList{
		font-size:14pt; font-style:bold;
	}
	#idText{
		font-size:14pt; font-style:bold; background-color: #aa5557;
	}
	#textText{
		font-size:14pt; font-style:bold;
	}
	#productAuthor{
		margin-left:153px;
	}
	#idnr{
		margin-left:41px;margin-right:41px;
	} 
	#oldorder{
		margin-left:148px;
	}
	#neworder{
		margin-left:148px;
	}
	#text{
		margin-left:50%;
	}
	#upperInput{
		margin-left:21%;padding-top: 3px;padding-bottom: 3px;margin-top:13px;
		font-size:12pt; font-style:italic; padding-right: 11px;
	}
	#searchInput{
		margin-left:41px;margin-right:41px;padding-top: 3px;padding-bottom: 3px;
		font-size:12pt; font-style:italic; padding-right: 11px;margin-top:10px;margin-bottom:10px;
	}
	#searchInput1{
		margin-left:445px;margin-right:35px;padding-top: 3px;padding-bottom: 3px;margin-top:13px;
		font-size:12pt; font-style:italic; padding-right: 11px;
	}
	#logOut{
		margin-left:32.1%;padding-top: 3px;padding-bottom: 3px;margin-top:13px;
		font-size:12pt; font-style:italic;padding-right: 17px;padding-left: 12px;
		margin-bottom:50px;
	}
	#priceAndName{
		border: 2px ridge purple;
	}
	#inputidtext{
		padding-left:9px;padding-right:9px;font-size: 13pt;
	}
	</style>
</head>
<body><fieldset id="fldset1"><form action="" method="post">
	<input id="upperInput" type="submit" value="Produkty" disabled>
	<input type="hidden" name="orders" value="zamówienia">
	<input id="upperInput" type="submit" value="Zamówienia">
</form></fieldset>
	<h1>Zarządzanie produktami obu sklepów</h1>
	<fieldset id="fldset">
		<table><form action="" method="get">
	<h2>Wyszukaj konkretny produkt w obu bazach</h2><table>		
<tr id="idText"><td>
		<label for="idnr" id="inputidtext">Wprowadź numer ID:</label></td>
		<td><input type="text" name="idnr" id="idnr">
	</td><td><input type="hidden" name="action" value="idsearch">
		<input id="searchInput" type="submit" value="Wyszukaj"></td></tr></form></table>
			<hr><h2>Wyświetl produkty, które spełniają następujące kryteria:</h2>
			<form action="" method="get">
			<table><tr><td><div id="searchList">
				<label for="author">Producent produktu:</label></td>
				<td><select name="author" id="productAuthor">
					<option value="">Dowolny producent</option>
					<?php foreach ($authors as $author): ?>
					<option value="<?php htmlout($author['id']); ?>"><?php htmlout($author['name']); ?></option>
				<?php endforeach; ?>
			</select></div></td></tr>
		<tr><td>
		<div id="categoryList">
			<label for="category">Kategoria produktu:</label></td>
			<td><select name="category" id="category">
				<option value="">Dowolna kategoria</option>
				<?php foreach ($categories as $category): ?>
				<option value="<?php htmlout($category['id']); ?>"><?php htmlout($category['name']); ?></option>
			<?php endforeach;?>
		</select>
	</div></td></tr></table>
	<tr><td><div id="textText">
		<label for="text">Zawierające tekst:</label></td>
		<td><input type="text" name="text" id="text"></td>
	</div></td></tr>
	<tr><td><div>
		<input type="hidden" name="action" value="search">
		<input id="searchInput1" type="submit" value="Wyszukaj">
	</div></td></tr>
</form></table>
</fieldset>
<form method="post">
	<input id="logOut" type="submit" name="logout" value="Wyloguj"> 
</form>
<?php if(isset($mod)){ ?>
<fieldset id="fldmod">
	<legend id="legendMod">Produkty podmienione</legend><table>
	<tr><th>Numer ID</th><th><b>Nazwa produktu</b></th><th>Cena</th><th>Data wpisu</th><th>Akcja</th></tr>
	<?php foreach ($mods as $modified): ?>
	<tr>
		<td><center><?php htmlout($modified['id']); ?><center></td>
		<td id="priceAndName"><b><a href="http://ad9bis.vot.pl/tory-h0/<?php htmlout($modified['id']); ?>-cysterna-francuska-primagaz-bttb.html" target="_blank"><?php htmlout($modified['nazwa']); ?></a></b></td>
		<td id="priceAndName"><center><?php $modcena=$modified['cena']*1;
				echo$modcena.'zł';
				$reduction= new OgicomProduct($secondHost, $secondLogin, $secondPassword);
				$row= $reduction->getReduction($modified['id']);
				$row2 = $row->fetch();
				$reducedPrice=$row2[0];
				$reducedPrice2=$reducedPrice*1;
				if ($reducedPrice!=0) {
					echo '<br>'.'Rabat to: '.$reducedPrice2.'zł';
				} ?></center></td>
		<td><?php htmlout($modified['data']); ?></td>
		<td><form action="?" method="get">
			<input type="hidden" name="idMod" value="<?php htmlout($modified['id']); ?>">
		<input type="submit" name="deleterow" value="Usuń wpis"></form></td>
	<?php endforeach; } ?>
</table></fieldset>
</body>
</html>
