<?php
function html($text)
{
	return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}
function htmlout($text)
{
	echo html($text);
}
function updateQuantity($quantity, $id, $pdo){
	$sql='UPDATE ps_stock_available SET
	quantity= :quantity
	WHERE id_product = :id';
	$s=$pdo->prepare($sql);
	$s->bindValue(':quantity', $quantity);
	$s->bindValue(':id', $id);
	$s->execute();
}

function updateProductName($text, $id, $pdo){
	$sql='UPDATE ps_product_lang SET
	name= :name
	WHERE id_product = :id';
	$s=$pdo->prepare($sql);
	$s->bindValue(':name', $text);
	$s->bindValue(':id', $id);
	$s->execute();
}

function updateManufacturer($author, $id, $pdo){
	$sql='UPDATE ps_product SET
	id_manufacturer= :id_manufacturer
	WHERE id_product = :id';
	$s=$pdo->prepare($sql);
	$s->bindValue(':id', $id);
	$s->bindValue(':id_manufacturer', $author);
	$s->execute();
}

function confirmation($id, $pdo){
	$sql='SELECT id_product, quantity FROM ps_stock_available
	WHERE ps_stock_available.id_product= :id';
	$s=$pdo->prepare($sql);
	$s->bindValue(':id', $id);
	$s->execute();
	$row= $s->fetch();
	return ($row);
}

function selectProduct($id, $newpdo){
	$newsql='SELECT ps_product_lang.id_product, ps_product_lang.name, ps_stock_available.quantity, ps_category_product.id_category, ps_category_lang.meta_title, ps_product.price
	FROM ps_product_lang INNER JOIN ps_stock_available ON ps_product_lang.id_product=ps_stock_available.id_product
	INNER JOIN ps_category_product ON ps_product_lang.id_product=ps_category_product.id_product
	INNER JOIN ps_category_lang ON ps_category_product.id_category=ps_category_lang.id_category
	INNER JOIN ps_product ON ps_product_lang.id_product=ps_product.id_product
	WHERE ps_product_lang.id_product= :id';
	$s=$newpdo->prepare($newsql);
	$s->bindValue(':id', $id);
	$s->execute();
	$row= $s->fetch();
	return ($row);
}
function selectManufacturer($id, $pdo){
	$sql='SELECT ps_product.id_product, ps_product.id_manufacturer, ps_product.id_manufacturer, ps_manufacturer.name
	FROM ps_product INNER JOIN ps_manufacturer
	ON ps_product.id_manufacturer=ps_manufacturer.id_manufacturer
	WHERE ps_product.id_product= :id';
	$s=$pdo->prepare($sql);
	$s->bindValue(':id', $id);
	$s->execute();
	$row= $s->fetch();
	return ($row);
}
function selectWholeManufacturer($pdo){
	$sql='SELECT id_manufacturer, name
	FROM ps_manufacturer';
	$s=$pdo->prepare($sql);
	$s->execute();
	return ($s);
}
function selectCategory($id, $pdo){
	$sql='SELECT ps_category_product.id_product, ps_category_product.id_category, ps_category_lang.meta_title FROM ps_category_product
	INNER JOIN ps_category_lang ON ps_category_product.id_category=ps_category_lang.id_category
	WHERE ps_category_product.id_product= :id';
	$s=$pdo->prepare($sql);
	$s->bindValue(':id', $id);
	$s->execute();
	return ($s);
}
function selectWholeCategoryOld($pdo){
	$sql='SELECT id_category, meta_title FROM ps_category_lang
	WHERE id_lang=3 AND id_category NOT IN (1,6)';
	$s=$pdo->prepare($sql);
	$s->execute();
	return ($s);
}
function selectWholeCategory($pdo){
	$sql='SELECT id_category, meta_title FROM ps_category_lang
	WHERE id_category NOT IN (1,2,10,14, 15, 19, 20, 22, 23, 24, 25, 26, 27, 32, 33)';
	$s=$pdo->prepare($sql);
	$s->execute();
	return ($s);
}
function selectCategoryOld($id, $pdo){
	$sql='SELECT ps_category_product.id_product, ps_category_product.id_category, ps_category_lang.id_lang, ps_category_lang.meta_title FROM ps_category_product
	INNER JOIN ps_category_lang ON ps_category_product.id_category=ps_category_lang.id_category
	WHERE ps_category_lang.id_lang=3 AND ps_category_product.id_product= :id';
	$s=$pdo->prepare($sql);
	$s->bindValue(':id', $id);
	$s->execute();
	return ($s);
}
function deleteCategory($id, $pdo){
	$sql='DELETE FROM ps_category_product
	WHERE id_product = :id';
	$s=$pdo->prepare($sql);
	$s->bindValue(':id', $id);
	$s->execute();
}
function insertCategory($id_category, $id_product, $pdo){
	$sql='INSERT INTO ps_category_product SET
	id_category= :id_category,
	id_product= :id_product';
	$s=$pdo->prepare($sql);
	foreach ($id_category as $categoryId){
	$s->bindValue(':id_category', $categoryId);
	$s->bindValue(':id_product', $id_product);
	$s->execute();}
}
function insertDifferentCategory($id_category, $id_product, $pdo){
	$sql='INSERT INTO ps_category_product SET
	id_category= :id_category,
	id_product= :id_product';
	$s=$pdo->prepare($sql);
	foreach ($id_category as $categoryId){
		if ($categoryId== 6){
			$categoryId=2;
		}
		elseif ($categoryId== 2){
			$categoryId=6;
		}
		$s->bindValue(':id_category', $categoryId);
		$s->bindValue(':id_product', $id_product);
		$s->execute();
}	}
function selectManufacturerOld($id, $oldpdo){
	$oldsql='SELECT ps_product.id_product, ps_product.id_manufacturer, ps_product.id_manufacturer, ps_manufacturer.name
	FROM ps_product INNER JOIN ps_manufacturer
	ON ps_product.id_manufacturer=ps_manufacturer.id_manufacturer
	WHERE ps_category_lang.id_lang=3 AND ps_product.id_product= :id';
	$t=$oldpdo->prepare($oldsql);
	$t->bindValue(':id', $id);
	$t->execute();
	$row= $t->fetch();
	return ($row);
}
function selectPriceRedOld($oldpdo){
	$oldsql='SELECT ps_product_lang.id_product, ps_product_lang.name, ps_product_shop.price, ps_specific_price.reduction
	FROM ps_product_lang INNER JOIN ps_product_shop ON ps_product_lang.id_product=ps_product_shop.id_product
	INNER JOIN ps_specific_price ON ps_product_lang.id_product=ps_specific_price.id_product
	WHERE ps_product_lang.id_lang=3 AND ps_product_lang.id_product= :id';
		//ORDER BY id_product
	$t=$oldpdo->prepare($oldsql);
	$t->execute();
	$row= $t->fetch();
	return ($row);
}
function selectOrderQuantity($id_number, $pdo, $secondpdo, $include){
	$sql='SELECT ps_stock_available.quantity, ps_order_detail.product_id, ps_order_detail.id_order FROM ps_stock_available
	INNER JOIN ps_order_detail ON ps_order_detail.product_id=ps_stock_available.id_product
	WHERE ps_order_detail.id_order = :id_number';
	$s=$pdo->prepare($sql);
	$s->bindValue(':id_number', $id_number);
	$s->execute();
	include 'orderUpgrade.html.php';
}
function getPrice($newid, $pdo){
	$sql='SELECT ps_product.price
	FROM ps_product 
	WHERE ps_product.id_product=:id';
	$w2= $pdo->prepare($sql);
	$w2->bindValue(':id', $newid);
	$w2->execute();
	$row= $w2->fetch();
	return ($row);
}
function getName($newid, $pdo){
	$sql='SELECT name FROM ps_product_lang
	WHERE ps_product_lang.id_product=:id';
	$w2= $pdo->prepare($sql);
	$w2->bindValue(':id', $newid);
	$w2->execute();
	$row= $w2->fetch();
	return ($row);
}
function getNameOld($newid, $pdo){
	$sql='SELECT name FROM ps_product_lang
	WHERE ps_product_lang.id_lang=3 AND ps_product_lang.id_product=:id';
	$w2= $pdo->prepare($sql);
	$w2->bindValue(':id', $newid);
	$w2->execute();
	$row= $w2->fetch();
	return ($row);
}
function getReduction($productId, $pdo){
	$sql='SELECT reduction, ps_product_lang.id_product
	FROM ps_specific_price INNER JOIN ps_product_lang ON ps_product_lang.id_product=ps_specific_price.id_product
	WHERE ps_product_lang.id_product=:id';
	$w3= $pdo->prepare($sql);
	$w3->bindValue(':id', $productId);
	$w3->execute();
	$row= $w3->fetch();
	return ($row);
}
function countReductionNew($cena, $rabat){
	$prReduction='Rabat: '.($rabat*100).' %';
	$reductionValue=$cena*$rabat;
	$countedPrice=$cena-$reductionValue.'zł';
	echo $countedPrice.'<br>';
	if ($rabat!=0) {
		echo'<b>'.$prReduction.'</b>';}
}
function countReductionOld($cenaSP, $rabatSP){
	$rabat=$rabatSP+0;
	$rabat2='Rabat: '.$rabat.'zł';
	$countedPrice=$cenaSP-$rabat.'zł';
	echo$countedPrice.'<br>';
	if ($rabat!=0) {
		echo'<b>'.$rabat2.'</b>'; }
}

function selectNameQuantityOld($id, $pdo){
	$sql='SELECT ps_product_lang.id_product, ps_product_lang.name, ps_stock_available.quantity
	FROM ps_product_lang INNER JOIN ps_stock_available ON ps_product_lang.id_product=ps_stock_available.id_product
	WHERE ps_product_lang.id_lang=3 AND ps_product_lang.id_product= :id';
	$s=$pdo->prepare($sql);
	$s->bindValue(':id', $id);
	$s->execute();
	$row= $s->fetch();
	return ($row);
}
function updatePrice($id, $nominalPrice, $pdo){
	$sql= 'UPDATE ps_product SET
	price= :price
	WHERE id_product = :id';
	$s=$pdo->prepare($sql);
	$s->bindValue(':id', $_POST['id']);
	$s->bindValue(':price', $nominalPrice);
	$s->execute();
}
function selectWholeDetailsOld($id, $pdo){
	$sql='SELECT ps_product_lang.id_product, ps_product_lang.name, ps_product_lang.description, ps_product_lang.description_short, ps_product_lang.link_rewrite, ps_product_lang.meta_description, ps_product_lang.meta_title, ps_stock_available.quantity, ps_product.condition, ps_product.active, ps_product.indexed, ps_product.price, ps_product_tag.id_tag
	FROM ps_product_lang INNER JOIN ps_stock_available ON ps_product_lang.id_product=ps_stock_available.id_product
	INNER JOIN ps_product ON ps_product_lang.id_product=ps_product.id_product
	INNER JOIN ps_product_tag ON ps_product_lang.id_product=ps_product_tag.id_product
	WHERE ps_product_lang.id_lang=3 AND ps_product_lang.id_product= :id';
	$s=$pdo->prepare($sql);
	$s->bindValue(':id', $id);
	$s->execute();
	$row= $s->fetch();
	return ($row);
}
function selectWholeDetails($id, $pdo){
	$sql='SELECT ps_product_lang.id_product, ps_product_lang.name, ps_product_lang.description, ps_product_lang.description_short, ps_product_lang.link_rewrite, ps_product_lang.meta_description, ps_product_lang.meta_title, ps_stock_available.quantity, ps_product.condition, ps_product.price, ps_product.indexed, ps_product.active, ps_product_tag.id_tag
	FROM ps_product_lang INNER JOIN ps_stock_available ON ps_product_lang.id_product=ps_stock_available.id_product
	INNER JOIN ps_product ON ps_product_lang.id_product=ps_product.id_product
	INNER JOIN ps_product_tag ON ps_product_lang.id_product=ps_product_tag.id_product
	WHERE ps_product_lang.id_product= :id';
	$s=$pdo->prepare($sql);
	$s->bindValue(':id', $id);
	$s->execute();
	$row= $s->fetch();
	return ($row);
}
	function selectTag($id, $pdo){
	$sql='SELECT ps_product_tag.id_product, ps_product_tag.id_tag, ps_tag.name
	FROM ps_product_tag INNER JOIN ps_tag ON ps_product_tag.id_tag=ps_tag.id_tag
	WHERE ps_product_tag.id_product= :id';
	$s=$pdo->prepare($sql);
	$s->bindValue(':id', $id);
	$s->execute();
	return ($s);
}
function updateDesc($description, $id, $pdo){
	$sql='UPDATE ps_product_lang SET
	description= :description
	WHERE id_product = :id';
	$s=$pdo->prepare($sql);
	$s->bindValue(':description', $description);
	$s->bindValue(':id', $id);
	$s->execute();
}
function updateDesc_short($description_short, $id, $pdo){
	$sql='UPDATE ps_product_lang SET
	description_short= :desc_short
	WHERE id_product = :id';
	$s=$pdo->prepare($sql);
	$s->bindValue(':desc_short', $description_short);
	$s->bindValue(':id', $id);
	$s->execute();
}
function updateMeta_title($meta_title, $id, $pdo){
	$sql='UPDATE ps_product_lang SET
	meta_title= :meta_t
	WHERE id_product = :id';
	$s=$pdo->prepare($sql);
	$s->bindValue(':meta_t', $meta_title);
	$s->bindValue(':id', $id);
	$s->execute();
}
function updateMeta_desc($meta_description, $id, $pdo){
	$sql='UPDATE ps_product_lang SET
	meta_description= :meta_d
	WHERE id_product = :id';
	$s=$pdo->prepare($sql);
	$s->bindValue(':meta_d', $meta_description);
	$s->bindValue(':id', $id);
	$s->execute();
}
function updateLink($link, $id, $pdo){
	$sql='UPDATE ps_product_lang SET
	link_rewrite= :link_r
	WHERE id_product = :id';
	$s=$pdo->prepare($sql);
	$s->bindValue(':link_r', $link);
	$s->bindValue(':id', $id);
	$s->execute();
}
function updateIndex($index, $id, $pdo){
	$sql='UPDATE ps_product SET
	ps_product.indexed= :index
	WHERE id_product = :id';
	$s=$pdo->prepare($sql);
	$s->bindValue(':index', $index);
	$s->bindValue(':id', $id);
	$s->execute();
}
function updateIndexShop($index, $id, $pdo){
	$sql='UPDATE ps_product_shop SET
	ps_product_shop.indexed= :index
	WHERE id_product = :id';
	$s=$pdo->prepare($sql);
	$s->bindValue(':index', $index);
	$s->bindValue(':id', $id);
	$s->execute();
}
function updateActive($active, $id, $pdo){
	$sql='UPDATE ps_product SET
	ps_product.active= :active
	WHERE id_product = :id';
	$s=$pdo->prepare($sql);
	$s->bindValue(':active', $active);
	$s->bindValue(':id', $id);
	$s->execute();
}
function updateActiveShop($active, $id, $pdo){
	$sql='UPDATE ps_product_shop SET
	ps_product_shop.active= :active
	WHERE id_product = :id';
	$s=$pdo->prepare($sql);
	$s->bindValue(':active', $active);
	$s->bindValue(':id', $id);
	$s->execute();
}
function updateCondition($condition, $id, $pdo){
	$sql='UPDATE ps_product SET
	ps_product.condition= :cond
	WHERE id_product = :id';
	$s=$pdo->prepare($sql);
	$s->bindValue(':cond', $condition);
	$s->bindValue(':id', $id);
	$s->execute();
}
function updateConditionShop($condition, $id, $pdo){
	$sql='UPDATE ps_product_shop SET
	ps_product_shop.condition= :cond
	WHERE id_product = :id';
	$s=$pdo->prepare($sql);
	$s->bindValue(':cond', $condition);
	$s->bindValue(':id', $id);
	$s->execute();
}
	function checkIfTag($tagText, $pdo){
	$sql='SELECT id_tag
	FROM ps_tag
	WHERE ps_tag.name= :id';
	$s=$pdo->prepare($sql);
	$s->bindValue(':id', $tagText);
	$s->execute();
	$row= $s->fetch();
	return ($row);
}
function deleteTag($id_tag, $id, $pdo){
	$sql='DELETE FROM ps_product_tag
	WHERE id_product = :id AND id_tag = :id_tag';
	$s=$pdo->prepare($sql);
	$s->bindValue(':id_tag', $id_tag);
	$s->bindValue(':id', $id);
	$s->execute();
}
function insertTag($id_tag, $id, $pdo){
	$sql='INSERT INTO ps_product_tag (id_product, id_tag)
	VALUES (:id, :id_tag)';
	$s=$pdo->prepare($sql);
	$s->bindValue(':id_tag', $id_tag);
	$s->bindValue(':id', $id);
	$s->execute();
}
function createTag($id_tag, $name, $pdo){
	$sql='INSERT INTO ps_tag (id_tag, name)
	VALUES (:id_tag, :name)';
	$s=$pdo->prepare($sql);
	$s->bindValue(':id_tag', $id_tag);
	$s->bindValue(':name', $name);
	$s->execute();
}
function deleteWholeTag($id, $pdo){
	$sql='DELETE FROM ps_product_tag
	WHERE id_product = :id';
	$s=$pdo->prepare($sql);
	$s->bindValue(':id', $id);
	$s->execute();
}
function insertModyfy($id_product, $name, $pdo){
	$sql='INSERT INTO ps_modyfy SET
	name= :name,
	id_number= :id_product,
	date=CURDATE()';
	$s=$pdo->prepare($sql);
	$s->bindValue(':name', $name);
	$s->bindValue(':id_product', $id_product);
	$s->execute();
}
	function selectModyfy($pdo){
	$sql='SELECT ps_modyfy.id_number, ps_product_lang.name, ps_modyfy.date
	FROM ps_modyfy INNER JOIN ps_product_lang ON ps_modyfy.id_number=ps_product_lang.id_product
	WHERE ps_product_lang.id_lang=3
	ORDER BY id_number';
	$s=$pdo->prepare($sql);
	$s->execute();
	return ($s);
}
function deleteMod($id, $pdo){
	$sql='DELETE FROM ps_modyfy
	WHERE id_number = :id';
	$s=$pdo->prepare($sql);
	$s->bindValue(':id', $id);
	$s->execute();
}
