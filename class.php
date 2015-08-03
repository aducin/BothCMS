<?php
abstract class Order
{
	protected $idOrder;

	private function getSelectStatement($orderId) {
		$this->idOrder=$orderId;
		return 'SELECT ps_order_detail.product_id, ps_order_detail.id_order, ps_product_lang.name, ps_order_detail.product_quantity, ps_stock_available.quantity FROM ps_order_detail
		INNER JOIN ps_product_lang ON ps_order_detail.product_id=ps_product_lang.id_product
		INNER JOIN ps_stock_available ON ps_order_detail.product_id=ps_stock_available.id_product';
	}

	private function getSelectDetails($orderId) {
		$this->idOrder=$orderId;
		return 'SELECT ps_order_detail.product_id, ps_order_detail.id_order, ps_order_detail.product_price, ps_order_detail.reduction_amount, ps_order_detail.total_price_tax_incl, ps_order_detail.product_quantity, ps_product_lang.name, ps_orders.total_products, ps_orders.total_paid, ps_orders.reference, ps_orders.payment,ps_orders.id_customer, ps_customer.email, ps_customer.firstname, ps_customer.lastname
		FROM ps_order_detail 
		INNER JOIN ps_product_lang ON ps_order_detail.product_id=ps_product_lang.id_product
		INNER JOIN ps_orders ON ps_order_detail.id_order=ps_orders.id_order
		INNER JOIN ps_customer ON ps_orders.id_customer=ps_customer.id_customer';
	}

	private function getToSendNotification($orderId) {
		$this->idOrder=$orderId;
		return 'SELECT ps_orders.id_order, ps_orders.reference, ps_orders.id_customer, ps_customer.email, ps_customer.firstname, ps_customer.lastname
		FROM ps_orders
		INNER JOIN ps_customer ON ps_orders.id_customer=ps_customer.id_customer
		WHERE ps_orders.id_order=:id_number';
	}

	abstract protected function getWhereStatement();

	public function getQuery($orderId,$pdo) {
		$sql=$this->getSelectStatement($orderId) . $this->getWhereStatement();
		$c=$pdo->prepare($sql);
		$c->bindValue(':id_number', $orderId);
		$c->execute();
		return($c);
	}

	public function getQueryDetails($orderId,$pdo) {
		$sql=$this->getSelectDetails($orderId) . $this->getWhereStatement();
		$c=$pdo->prepare($sql);
		$c->bindValue(':id_number', $orderId);
		$c->execute();
		return($c);
	}

	public function SendNotification($orderId,$pdo){
		$sql=$this->getToSendNotification($orderId);
		$c=$pdo->prepare($sql);
		$c->bindValue(':id_number', $orderId);
		$c->execute();
		return($c);
	}

	public function selectOrderQuantity($id_number, $pdo){
		$sql='SELECT ps_stock_available.quantity, ps_order_detail.product_id, ps_order_detail.id_order FROM ps_stock_available
		INNER JOIN ps_order_detail ON ps_order_detail.product_id=ps_stock_available.id_product
		WHERE ps_order_detail.id_order = :id_number';
		$s=$pdo->prepare($sql);
		$s->bindValue(':id_number', $id_number);
		$s->execute();
		return($s);
	}
}

class OldOrder extends Order
{
	protected function getWhereStatement() {
		return " WHERE ps_product_lang.id_lang=3 AND ps_order_detail.id_order = :id_number";
	}

	public function getCount($orderId, $pdo){
		$sql= 'SELECT COUNT(product_name) FROM ps_order_detail 
		WHERE id_order= :id';
		$s=$pdo->prepare($sql);
		$s->bindValue(':id', $orderId);
		$s->execute();
		return($s);
	}
}

class NewOrder extends Order
{
	protected function getWhereStatement() {
		return " WHERE id_order = :id_number";
	}
}

abstract class Product
{
	protected $idProduct;
	private function getSelectProduct() {
		return 'SELECT ps_product_lang.id_product, ps_product_lang.name, ps_stock_available.quantity, ps_product.price, ps_product.id_manufacturer, ps_category_product.id_category
		FROM ps_product_lang INNER JOIN ps_stock_available ON ps_product_lang.id_product=ps_stock_available.id_product
		INNER JOIN ps_product ON ps_product_lang.id_product=ps_product.id_product
		INNER JOIN ps_category_product ON ps_product_lang.id_product= ps_category_product.id_product';
	}

	private function getSelectLessProduct() {
		return 'SELECT ps_product_lang.id_product, ps_product_lang.name, ps_stock_available.quantity, ps_product.price, ps_product.id_manufacturer
		FROM ps_product_lang INNER JOIN ps_stock_available ON ps_product_lang.id_product=ps_stock_available.id_product
		INNER JOIN ps_product ON ps_product_lang.id_product=ps_product.id_product';
	}

	private function selectWholeDetails(){
	return 'SELECT ps_product_lang.id_product, ps_product_lang.name, ps_product_lang.description, ps_product_lang.description_short, ps_product_lang.link_rewrite, ps_product_lang.meta_description, ps_product_lang.meta_title, ps_stock_available.quantity, ps_product.condition, ps_product.price, ps_product.indexed, ps_product.active, ps_product_tag.id_tag
	FROM ps_product_lang INNER JOIN ps_stock_available ON ps_product_lang.id_product=ps_stock_available.id_product
	INNER JOIN ps_product ON ps_product_lang.id_product=ps_product.id_product
	INNER JOIN ps_product_tag ON ps_product_lang.id_product=ps_product_tag.id_product';
	}

	private function selectCategory(){
	return 'SELECT ps_category_product.id_product, ps_category_product.id_category, ps_category_lang.id_lang, ps_category_lang.meta_title FROM ps_category_product
	INNER JOIN ps_category_lang ON ps_category_product.id_category=ps_category_lang.id_category';
	}

	private function selectWholeCategory(){
	return 'SELECT id_category, meta_title FROM ps_category_lang';
	}

	private function selectName(){
	return 'SELECT name FROM ps_product_lang';
	}

	abstract protected function getWhereProduct();

	public function getProductQuery($productId,$pdo) {
		$this->idProduct=$productId;
		$sql=$this->getSelectProduct($productId) . $this->getWhereProduct();
		$c=$pdo->prepare($sql);
		$c->bindValue(':id_number', $productId);
		$c->execute();
		return($c);
	}

	public function getName($productId,$pdo) {
		$this->idProduct=$productId;
		$sql=$this->selectName($productId) . $this->getWhereProduct();
		$c=$pdo->prepare($sql);
		$c->bindValue(':id_number', $productId);
		$c->execute();
		$c1 = $c->fetch();
		return($c1["name"]);

	}

	public function getWholeDetailsQuery($productId,$pdo) {
		$this->idProduct=$productId;
		$sql=$this->selectWholeDetails($productId) . $this->getWhereProduct();
		$c=$pdo->prepare($sql);
		$c->bindValue(':id_number', $productId);
		$c->execute();
		return($c);
	}

	public function getLoopTextQuery($where,$name,$pdo) {
		$sql=$this->getSelectLessProduct() . $where;
		$c=$pdo->prepare($sql);
		$c->bindValue(':name', $name);
		$c->execute();
		return($c);
	}

	public function getLoopManufacturerQuery($where,$manufactorer,$pdo) {
		$sql=$this->getSelectLessProduct() . $where;
		$c=$pdo->prepare($sql);
		$c->bindValue(':id_manufacturer', $manufactorer);
		$c->execute();
		return($c);
	}

	public function getLoopCategoryQuery($where,$category,$pdo) {
		$sql=$this->getSelectProduct() . $where;
		$c=$pdo->prepare($sql);
		$c->bindValue(':id_category', $category);
		$c->execute();
		return($c);
	}

	public function getLoopBothQuery($where,$name,$manufactorer,$pdo) {
		$sql=$this->getSelectLessProduct() . $where;
		$c=$pdo->prepare($sql);
		$c->bindValue(':name', $name);
		$c->bindValue(':id_manufacturer', $manufactorer);
		$c->execute();
		return($c);
	}

	public function getLoopBoth2Query($where,$name,$category,$pdo) {
		$sql=$this->getSelectProduct() . $where;
		$c=$pdo->prepare($sql);
		$c->bindValue(':name', $name);
		$c->bindValue(':id_category', $category);
		$c->execute();
		return($c);
	}

	public function getLoopBoth3Query($where,$category,$manufactorer,$pdo) {
		$sql=$this->getSelectProduct() . $where;
		$c=$pdo->prepare($sql);
		$c->bindValue(':id_category', $category);
		$c->bindValue(':id_manufacturer', $manufactorer);
		$c->execute();
		return($c);
	}

	public function getLoopTripleQuery($where,$name,$category,$manufactorer,$pdo) {
		$sql=$this->getSelectProduct() . $where;
		$c=$pdo->prepare($sql);
		$c->bindValue(':name', $name);
		$c->bindValue(':id_category', $category);
		$c->bindValue(':id_manufacturer', $manufactorer);
		$c->execute();
		return($c);
	}

	public function getCategory($productId,$pdo) {
		$this->idProduct=$productId;
		$sql=$this->selectCategory($productId) . $this->getWhereProduct2();
		$c=$pdo->prepare($sql);
		$c->bindValue(':id', $productId);
		$c->execute();
		return($c);
	}

	public function getWholeCategory($pdo) {
		$sql=$this->selectWholeCategory() . $this->getWhereProduct3();
		$c=$pdo->prepare($sql);
		$c->execute();
		return($c);
	}

	public function getReduction($productId, $pdo){
		$sql='SELECT reduction
		FROM ps_specific_price
		WHERE ps_specific_price.id_product=:id';
		$c= $pdo->prepare($sql);
		$c->bindValue(':id', $productId);
		$c->execute();
		return($c);
	}

	public function selectManufacturer($id, $pdo){
	$sql='SELECT ps_product.id_product, ps_product.id_manufacturer, ps_manufacturer.name
	FROM ps_product INNER JOIN ps_manufacturer
	ON ps_product.id_manufacturer=ps_manufacturer.id_manufacturer
	WHERE ps_product.id_product= :id';
	$s=$pdo->prepare($sql);
	$s->bindValue(':id', $id);
	$s->execute();
	$s1 = $s->fetch();
	return($s1["name"]);
}

	public function selectTag($id, $pdo){
	$sql='SELECT ps_product_tag.id_product, ps_product_tag.id_tag, ps_tag.name
	FROM ps_product_tag INNER JOIN ps_tag ON ps_product_tag.id_tag=ps_tag.id_tag
	WHERE ps_product_tag.id_product= :id';
	$s=$pdo->prepare($sql);
	$s->bindValue(':id', $id);
	$s->execute();
	return ($s);
}

	public function checkIfTag($tagText, $pdo){
	$sql='SELECT id_tag
	FROM ps_tag
	WHERE ps_tag.name= :id';
	$s=$pdo->prepare($sql);
	$s->bindValue(':id', $tagText);
	$s->execute();
	return ($s);
}

	public function insertTag($id_tag, $id, $pdo){
	$sql='INSERT INTO ps_product_tag (id_product, id_tag)
	VALUES (:id, :id_tag)';
	$s=$pdo->prepare($sql);
	$s->bindValue(':id_tag', $id_tag);
	$s->bindValue(':id', $id);
	$s->execute();
	unset($s);
}

	public function createTag($id_tag, $name, $pdo){
	$sql='INSERT INTO ps_tag (id_tag, name)
	VALUES (:id_tag, :name)';
	$s=$pdo->prepare($sql);
	$s->bindValue(':id_tag', $id_tag);
	$s->bindValue(':name', $name);
	$s->execute();
	unset($s);
}

public function deleteTag($id_tag, $id, $pdo){
	$sql='DELETE FROM ps_product_tag
	WHERE id_product = :id AND id_tag = :id_tag';
	$s=$pdo->prepare($sql);
	$s->bindValue(':id_tag', $id_tag);
	$s->bindValue(':id', $id);
	$s->execute();
	unset($s);
}

public function deleteWholeTag($id, $pdo){
	$sql='DELETE FROM ps_product_tag
	WHERE id_product = :id';
	$s=$pdo->prepare($sql);
	$s->bindValue(':id', $id);
	$s->execute();
	unset($s);
}

public function deleteCategory($id, $pdo){
	$sql='DELETE FROM ps_category_product
	WHERE id_product = :id';
	$s=$pdo->prepare($sql);
	$s->bindValue(':id', $id);
	$s->execute();
	unset($s);
}

public function deleteImage($id, $pdo){
	$sql='DELETE FROM ps_image
	WHERE id_product = :id';
	$s=$pdo->prepare($sql);
	$s->bindValue(':id', $id);
	$s->execute();
	unset($s);
}

public function insertCategory($id_category, $id_product, $pdo){
	$sql='INSERT INTO ps_category_product SET
	id_category= :id_category,
	id_product= :id_product';
	$s=$pdo->prepare($sql);
	foreach ($id_category as $categoryId){
		$s->bindValue(':id_category', $categoryId);
		$s->bindValue(':id_product', $id_product);
		$s->execute();
	}
	unset($s);
}

public function insertDifferentCategory($id_category, $id_product, $pdo){
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
	}
	unset($s);
}

	public function insertModyfy($id_product, $name, $pdo){
	$sql='INSERT INTO ps_modyfy SET
	name= :name,
	id_number= :id_product,
	date=CURDATE()';
	$s=$pdo->prepare($sql);
	$s->bindValue(':name', $name);
	$s->bindValue(':id_product', $id_product);
	$s->execute();
	unset($s);
}

	public function updateBoth($id, $nominalPrice, $name, $quantity, $pdo){
		$sql='UPDATE ps_product 
		INNER JOIN ps_product_shop ON ps_product.id_product=ps_product_shop.id_product 
		INNER JOIN ps_product_lang ON ps_product.id_product=ps_product_lang.id_product
		INNER JOIN ps_stock_available ON ps_product.id_product=ps_stock_available.id_product
		SET ps_product_shop.price=:price,
		ps_product.price=:price,
		ps_product_lang.name= :name,
		ps_stock_available.quantity= :quantity
		WHERE ps_product_shop.id_product = :id_product';
		$c= $pdo->prepare($sql);
		$c->bindValue(':id_product', $id);
		$c->bindValue(':price', $nominalPrice);
		$c->bindValue(':name', $name);
		$c->bindValue(':quantity', $quantity);
		$c->execute();
		unset($c);
	}

	public function updateDetailedBoth($id, $nominalPrice, $name, $quantity, $description, $description_short, $meta_title, $meta_description, $link, $condition, $active, $pdo){
		$sql='UPDATE ps_product 
		INNER JOIN ps_product_shop ON ps_product.id_product=ps_product_shop.id_product 
		INNER JOIN ps_product_lang ON ps_product.id_product=ps_product_lang.id_product
		INNER JOIN ps_stock_available ON ps_product.id_product=ps_stock_available.id_product
		SET ps_product_shop.price=:price,
		ps_product.price=:price,
		ps_product_lang.name= :name,
		ps_stock_available.quantity= :quantity,
		ps_product_lang.description= :description,
		ps_product_lang.description_short= :desc_short,
		ps_product_lang.meta_title= :meta_t,
		ps_product_lang.meta_description= :meta_d,
		ps_product_lang.link_rewrite= :link_r,
		ps_product.condition= :cond,
		ps_product_shop.condition= :cond,
		ps_product.active= :active,
		ps_product_shop.active= :active,
		ps_product.indexed= :active,
		ps_product_shop.indexed= :active
		WHERE ps_product_shop.id_product = :id_product';
		$c= $pdo->prepare($sql);
		$c->bindValue(':id_product', $id);
		$c->bindValue(':price', $nominalPrice);
		$c->bindValue(':name', $name);
		$c->bindValue(':quantity', $quantity);
		$c->bindValue(':description', $description);
		$c->bindValue(':desc_short', $description_short);
		$c->bindValue(':meta_t', $meta_title);
		$c->bindValue(':meta_d', $meta_description);
		$c->bindValue(':link_r', $link);
		$c->bindValue(':cond', $condition);
		$c->bindValue(':active', $active);
		$c->execute();
		unset($c);
	}
	
	public function updateManufacturer($author, $id, $pdo){
	$sql='UPDATE ps_product SET
	id_manufacturer= :id_manufacturer
	WHERE id_product = :id';
	$s=$pdo->prepare($sql);
	$s->bindValue(':id', $id);
	$s->bindValue(':id_manufacturer', $author);
	$s->execute();
	unset($s);
}

public function updateQuantity($quantity, $id, $pdo){
	$sql='UPDATE ps_stock_available SET
	quantity= :quantity
	WHERE id_product = :id';
	$s=$pdo->prepare($sql);
	$s->bindValue(':quantity', $quantity);
	$s->bindValue(':id', $id);
	$s->execute();
	unset($s);
}

	public function confirmation($id, $pdo){
	$sql='SELECT id_product, quantity FROM ps_stock_available
	WHERE ps_stock_available.id_product= :id';
	$s=$pdo->prepare($sql);
	$s->bindValue(':id', $id);
	$s->execute();
	$s2 = $s->fetch();
	return ($s2);
	}
}

class OldProduct extends Product
{
	protected function getWhereProduct() {
		return " WHERE ps_product_lang.id_lang=3 AND ps_product_lang.id_product= :id_number";
	}

	protected function getWhereProduct2() {
		return " WHERE ps_category_lang.id_lang=3 AND ps_category_product.id_product= :id";
	}

	protected function getWhereProduct3() {
		return " WHERE id_lang=3 AND id_category NOT IN (1,6)";
	}

	public function countReduction($price, $reduction) {
				$new0=number_format($price, 2,'.','');
				$new=floatval($reduction);
				$new2=number_format($new, 2,'.','');
				$oldQueryResult1=$new0-$new;
				$oldQueryResult2=number_format($oldQueryResult1, 2,'.','');
				return$oldQueryResult2.'zł<br>W tym rabat: <b>'.$new2.'zł</b>';
	}
}

class NewProduct extends Product
{
	protected function getWhereProduct() {
		return " WHERE ps_product_lang.id_product= :id_number";
	}

	protected function getWhereProduct2() {
		return " WHERE ps_category_product.id_product= :id";
	}

	protected function getWhereProduct3() {
		return " WHERE id_category NOT IN (1,2,10,14, 15, 19, 20, 22, 23, 24, 25, 26, 27, 32, 33)";
	}

	public function countReduction($price, $reduction) {
				$new0=number_format($price, 2,'.','');
				$new=floatval($reduction);
				$newQueryResult2=$new0-$new0*$new;
				$newQueryResult3=$new*100;
				$newQueryResult4=$newQueryResult3.'%</b>';
				return$newQueryResult2.'zł<br>W tym rabat: <b>'.$newQueryResult4;
	}
}

abstract class Lists1{
	
	function __construct($pdo){
		$this->pdo=$pdo;}

	function selectWholeManufacturer(){
		$sql='SELECT id_manufacturer, name
		FROM ps_manufacturer';
		$s=$this->pdo->prepare($sql);
		$s->execute();
		return ($s);
		unset($s);
	}

	private function selectWholeCategory1(){
		return 'SELECT id_category, meta_title FROM ps_category_lang';
	}
	
	abstract protected function selectWhere();

	public function selectWholeCategory2() {
		$sql=$this->selectWholeCategory1() . $this->selectWhere();
		$c=$this->pdo->prepare($sql);
		$c->execute();
		return($c);
		unset($s);
	}
}

class OldLists extends Lists1{

	protected function selectWhere() {
	return " WHERE id_lang=3 AND id_category NOT IN (1,6)"; }

	function selectModyfy(){
	$sql='SELECT ps_modyfy.id_number, ps_product_lang.name, ps_modyfy.date, ps_product.price
	FROM ps_modyfy INNER JOIN ps_product_lang ON ps_modyfy.id_number=ps_product_lang.id_product
	INNER JOIN ps_product ON ps_modyfy.id_number=ps_product.id_product
	WHERE ps_product_lang.id_lang=3
	ORDER BY id_number';
	$s=$this->pdo->prepare($sql);
	$s->execute();
	return ($s);
	unset($s);
}

	function deleteMod($id){
	$sql='DELETE FROM ps_modyfy
	WHERE id_number = :id';
	$s=$this->pdo->prepare($sql);
	$s->bindValue(':id', $id);
	$s->execute();
	unset($s);
}

	function __destruct(){
	}

}
class NewLists extends Lists1{

	protected function selectWhere() {
		return " WHERE id_category NOT IN (1,2,10,11,12,14, 15, 19, 20, 21,22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 38, 39, 40, 42,43,46,47,52,53)";
	}

	function __destruct(){
	}
}
