<?php 

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
		return $c;
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
		return $c;
	}

	public function getLoopTextQuery($where,$name,$pdo) {
		$sql=$this->getSelectLessProduct() . $where;
		$c=$pdo->prepare($sql);
		$c->bindValue(':name', $name);
		$c->execute();
		return $c;
	}

	public function getLoopManufacturerQuery($where,$manufactorer,$pdo) {
		$sql=$this->getSelectLessProduct() . $where;
		$c=$pdo->prepare($sql);
		$c->bindValue(':id_manufacturer', $manufactorer);
		$c->execute();
		return $c;
	}

	public function getLoopCategoryQuery($where,$category,$pdo) {
		$sql=$this->getSelectProduct() . $where;
		$c=$pdo->prepare($sql);
		$c->bindValue(':id_category', $category);
		$c->execute();
		return $c;
	}

	public function getLoopBothQuery($where,$name,$manufactorer,$pdo) {
		$sql=$this->getSelectLessProduct() . $where;
		$c=$pdo->prepare($sql);
		$c->bindValue(':name', $name);
		$c->bindValue(':id_manufacturer', $manufactorer);
		$c->execute();
		return $c;
	}

	public function getLoopBoth2Query($where,$name,$category,$pdo) {
		$sql=$this->getSelectProduct() . $where;
		$c=$pdo->prepare($sql);
		$c->bindValue(':name', $name);
		$c->bindValue(':id_category', $category);
		$c->execute();
		return $c;
	}

	public function getLoopBoth3Query($where,$category,$manufactorer,$pdo) {
		$sql=$this->getSelectProduct() . $where;
		$c=$pdo->prepare($sql);
		$c->bindValue(':id_category', $category);
		$c->bindValue(':id_manufacturer', $manufactorer);
		$c->execute();
		return $c;
	}

	public function getLoopTripleQuery($where,$name,$category,$manufactorer,$pdo) {
		$sql=$this->getSelectProduct() . $where;
		$c=$pdo->prepare($sql);
		$c->bindValue(':name', $name);
		$c->bindValue(':id_category', $category);
		$c->bindValue(':id_manufacturer', $manufactorer);
		$c->execute();
		return $c;
	}

	public function getCategory($productId,$pdo) {
		$this->idProduct=$productId;
		$sql=$this->selectCategory($productId) . $this->getWhereProduct2();
		$c=$pdo->prepare($sql);
		$c->bindValue(':id', $productId);
		$c->execute();
		return $c;
	}

	public function getWholeCategory($pdo) {
		$sql=$this->selectWholeCategory() . $this->getWhereProduct3();
		$c=$pdo->prepare($sql);
		$c->execute();
		return $c;
	}

	public function getReduction($productId, $pdo){
		$sql='SELECT reduction
		FROM ps_specific_price
		WHERE ps_specific_price.id_product=:id';
		$c= $pdo->prepare($sql);
		$c->bindValue(':id', $productId);
		$c->execute();
		return $c;
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
		return $s;
	}

	public function checkIfTag($tagText, $pdo){
		$sql='SELECT id_tag
		FROM ps_tag
		WHERE ps_tag.name= :id';
		$s=$pdo->prepare($sql);
		$s->bindValue(':id', $tagText);
		$s->execute();
		return $s;
	}

	public function insertTag($id_tag, $id, $pdo){
		$sql='INSERT INTO ps_product_tag (id_product, id_tag)
		VALUES (:id, :id_tag)';
		$s=$pdo->prepare($sql);
		$s->bindValue(':id_tag', $id_tag);
		$s->bindValue(':id', $id);
		$s->execute();
	}

	public function createTag($id_tag, $name, $pdo){
		$sql='INSERT INTO ps_tag (id_tag, name)
		VALUES (:id_tag, :name)';
		$s=$pdo->prepare($sql);
		$s->bindValue(':id_tag', $id_tag);
		$s->bindValue(':name', $name);
		$s->execute();
	}

	public function deleteTag($id_tag, $id, $pdo){
		$sql='DELETE FROM ps_product_tag
		WHERE id_product = :id AND id_tag = :id_tag';
		$s=$pdo->prepare($sql);
		$s->bindValue(':id_tag', $id_tag);
		$s->bindValue(':id', $id);
		$s->execute();
	}

	public function deleteWholeTag($id, $pdo){
		$sql='DELETE FROM ps_product_tag
		WHERE id_product = :id';
		$s=$pdo->prepare($sql);
		$s->bindValue(':id', $id);
		$s->execute();
	}

	public function deleteCategory($id, $pdo){
		$sql='DELETE FROM ps_category_product
		WHERE id_product = :id';
		$s=$pdo->prepare($sql);
		$s->bindValue(':id', $id);
		$s->execute();
	}

	public function deleteImage($id, $pdo){
		$sql='DELETE FROM ps_image
		WHERE id_product = :id';
		$s=$pdo->prepare($sql);
		$s->bindValue(':id', $id);
		$s->execute();
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
	}
	
	public function updateManufacturer($author, $id, $pdo){
		$sql='UPDATE ps_product SET
		id_manufacturer= :id_manufacturer
		WHERE id_product = :id';
		$s=$pdo->prepare($sql);
		$s->bindValue(':id', $id);
		$s->bindValue(':id_manufacturer', $author);
		$s->execute();
	}

	public function updateQuantity($quantity, $id, $pdo){
		$sql='UPDATE ps_stock_available SET
		quantity= :quantity
		WHERE id_product = :id';
		$s=$pdo->prepare($sql);
		$s->bindValue(':quantity', $quantity);
		$s->bindValue(':id', $id);
		$s->execute();
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
