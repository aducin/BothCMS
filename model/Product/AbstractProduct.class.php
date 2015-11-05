<?php 

abstract class Product
{
	public function __construct($DBHandler){
		$this->pdo=$DBHandler;
	}

	protected $idProduct;

	private function getSelectProductSubquery() {
		return 'SELECT ps_product_lang.id_product, ps_product_lang.name, ps_stock_available.quantity, ps_product.price, ps_product.id_manufacturer, ps_category_product.id_category
		FROM ps_product_lang INNER JOIN ps_stock_available ON ps_product_lang.id_product=ps_stock_available.id_product
		INNER JOIN ps_product ON ps_product_lang.id_product=ps_product.id_product
		INNER JOIN ps_category_product ON ps_product_lang.id_product= ps_category_product.id_product';
	}

	private function getSelectLessProductSubquery() {
		return 'SELECT ps_product_lang.id_product, ps_product_lang.name, ps_stock_available.quantity, ps_product.price, ps_product.id_manufacturer
		FROM ps_product_lang INNER JOIN ps_stock_available ON ps_product_lang.id_product=ps_stock_available.id_product
		INNER JOIN ps_product ON ps_product_lang.id_product=ps_product.id_product';
	}

	private function getDetailsSubquery(){
		return 'SELECT ps_product_lang.id_product, ps_product_lang.name, ps_product_lang.description, ps_product_lang.description_short, ps_product_lang.link_rewrite, ps_product_lang.meta_description, ps_product_lang.meta_title, ps_stock_available.quantity, ps_product.condition, ps_product.price, ps_product.indexed, ps_product.active, ps_product_tag.id_tag
		FROM ps_product_lang INNER JOIN ps_stock_available ON ps_product_lang.id_product=ps_stock_available.id_product
		INNER JOIN ps_product ON ps_product_lang.id_product=ps_product.id_product
		INNER JOIN ps_product_tag ON ps_product_lang.id_product=ps_product_tag.id_product';
	}

	private function getCategorySubquery(){
		return 'SELECT ps_category_product.id_product, ps_category_product.id_category, ps_category_lang.id_lang, ps_category_lang.meta_title FROM ps_category_product
		INNER JOIN ps_category_lang ON ps_category_product.id_category=ps_category_lang.id_category';
	}

	private function getEveryCategorySubquery(){
		return 'SELECT id_category, meta_title FROM ps_category_lang';
	}

	private function getNameSubquery(){
		return 'SELECT name FROM ps_product_lang';
	}

	abstract protected function getWhereProductSubquery();

	public function getProductQuery($productId) {
		$this->idProduct=$productId;
		$sql=$this->getSelectProductSubquery($productId) . $this->getWhereProductSubquery();
		$c=$this->pdo->prepare($sql);
		$c->bindValue(':id_number', $productId);
		$c->execute();
		return $c;
	}

	public function getProductDetailedData($productId) {
		$this->idProduct=$productId;
		$sql=$this->getSelectProductSubquery($productId) . $this->getWhereProductSubquery();
		$c=$this->pdo->prepare($sql);
		$c->bindValue(':id_number', $productId);
		$c->execute();
		$subResult = $c->fetch();
		$result=array('id_product'=>$subResult['id_product'],'name'=>$subResult['name'], 'quantity'=>$subResult['quantity'], 'price'=>$subResult['price'], 'id_manufacturer'=>$subResult['id_manufacturer']);
		return $result;
	}

	public function getName($productId) {
		$this->idProduct=$productId;
		$sql=$this->getNameSubquery($productId) . $this->getWhereProductSubquery();
		$c=$this->pdo->prepare($sql);
		$c->bindValue(':id_number', $productId);
		$c->execute();
		$c1 = $c->fetch();
		return($c1["name"]);
	}

	public function getWholeDetailsQuery($productId) {
		$this->idProduct=$productId;
		$sql=$this->getDetailsSubquery($productId) . $this->getWhereProductSubquery();
		$c=$this->pdo->prepare($sql);
		$c->bindValue(':id_number', $productId);
		$c->execute();
		return $c;
	}

	public function getProductData($where) {
		$sql=$this->getSelectProductSubquery() . $where;
		$c=$this->pdo->prepare($sql);
		$c->execute();
		return $c;
	}

	public function getCategory($productId) {
		$this->idProduct=$productId;
		$sql=$this->getCategorySubquery($productId) . $this->getWhereCategorySubquery();
		$c=$this->pdo->prepare($sql);
		$c->bindValue(':id', $productId);
		$c->execute();
		return $c;
	}

	public function getEveryCategory() {
		$sql=$this->getEveryCategorySubquery() . $this->getWhereEveryCategorySubquery();
		$c=$this->pdo->prepare($sql);
		$c->execute();
		return $c;
	}

	public function getReductionData($productId){
		$sql='SELECT reduction
		FROM ps_specific_price
		WHERE ps_specific_price.id_product=:id';
		$c= $this->pdo->prepare($sql);
		$c->bindValue(':id', $productId);
		$c->execute();
		$result = $c->fetch();
		return($result[0]);
	}

	public function getQuantity($id){
		$sql='SELECT quantity FROM ps_stock_available
		WHERE ps_stock_available.id_product= :id';
		$s=$this->pdo->prepare($sql);
		$s->bindValue(':id', $id);
		$s->execute();
		$result =$s->fetch();
		return ($result[0]);
	}

		public function getPrice($productId) {
		$sql='SELECT price FROM ps_product
		WHERE ps_product.id_product=:id';
		$c= $this->pdo->prepare($sql);
		$c->bindValue(':id', $productId);
		$c->execute();
		$c1 = $c->fetch();
		return($c1["price"]);
	}

	public function selectManufacturer($id){
		$sql='SELECT ps_product.id_product, ps_product.id_manufacturer, ps_manufacturer.name
		FROM ps_product INNER JOIN ps_manufacturer
		ON ps_product.id_manufacturer=ps_manufacturer.id_manufacturer
		WHERE ps_product.id_product= :id';
		$s=$this->pdo->prepare($sql);
		$s->bindValue(':id', $id);
		$s->execute();
		$s1 = $s->fetch();
		return($s1["name"]);
	}

	public function selectTag($id){
		$sql='SELECT ps_product_tag.id_product, ps_product_tag.id_tag, ps_tag.name
		FROM ps_product_tag INNER JOIN ps_tag ON ps_product_tag.id_tag=ps_tag.id_tag
		WHERE ps_product_tag.id_product= :id';
		$s=$this->pdo->prepare($sql);
		$s->bindValue(':id', $id);
		$s->execute();
		return $s;
	}

	public function checkIfTag($tagText){
		$sql='SELECT id_tag
		FROM ps_tag
		WHERE ps_tag.name= :id';
		$s=$this->pdo->prepare($sql);
		$s->bindValue(':id', $tagText);
		$s->execute();
		return $s;
	}

	public function insertTag($id_tag, $id){
		$sql='INSERT INTO ps_product_tag (id_product, id_tag)
		VALUES (:id, :id_tag)';
		$s=$this->pdo->prepare($sql);
		$s->bindValue(':id_tag', $id_tag);
		$s->bindValue(':id', $id);
		$s->execute();
	}

	public function createTag($id_tag, $name){
		$sql='INSERT INTO ps_tag (id_tag, name)
		VALUES (:id_tag, :name)';
		$s=$this->pdo->prepare($sql);
		$s->bindValue(':id_tag', $id_tag);
		$s->bindValue(':name', $name);
		$s->execute();
	}

	public function deleteTag($id_tag, $id){
		$sql='DELETE FROM ps_product_tag
		WHERE id_product = :id AND id_tag = :id_tag';
		$s=$this->pdo->prepare($sql);
		$s->bindValue(':id_tag', $id_tag);
		$s->bindValue(':id', $id);
		$s->execute();
	}

	public function deleteWholeTag($id){
		$sql='DELETE FROM ps_product_tag
		WHERE id_product = :id';
		$s=$this->pdo->prepare($sql);
		$s->bindValue(':id', $id);
		$s->execute();
	}

	public function deleteCategory($id){
		$sql='DELETE FROM ps_category_product
		WHERE id_product = :id';
		$s=$this->pdo->prepare($sql);
		$s->bindValue(':id', $id);
		$s->execute();
	}

	public function deleteImage($id){
		$sql='DELETE FROM ps_image
		WHERE id_product = :id';
		$s=$this->pdo->prepare($sql);
		$s->bindValue(':id', $id);
		$s->execute();
	}

	public function insertCategory($id_category, $id_product){
		$sql='INSERT INTO ps_category_product SET
		id_category= :id_category,
		id_product= :id_product';
		$s=$this->pdo->prepare($sql);
		foreach ($id_category as $categoryId){
			$s->bindValue(':id_category', $categoryId);
			$s->bindValue(':id_product', $id_product);
			$s->execute();
		}
	}

	public function insertDifferentCategory($id_category, $id_product){
		$sql='INSERT INTO ps_category_product SET
		id_category= :id_category,
		id_product= :id_product';
		$s=$this->pdo->prepare($sql);
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

	public function insertModyfy($id_product, $name){
		$sql='INSERT INTO ps_modyfy SET
		name= :name,
		id_number= :id_product,
		date=CURDATE()';
		$s=$this->pdo->prepare($sql);
		$s->bindValue(':name', $name);
		$s->bindValue(':id_product', $id_product);
		$s->execute();
	}

	public function updateBoth($id, $nominalPrice, $name, $quantity){
		$sql='UPDATE ps_product 
		INNER JOIN ps_product_shop ON ps_product.id_product=ps_product_shop.id_product 
		INNER JOIN ps_product_lang ON ps_product.id_product=ps_product_lang.id_product
		INNER JOIN ps_stock_available ON ps_product.id_product=ps_stock_available.id_product
		SET ps_product_shop.price=:price,
		ps_product.price=:price,
		ps_product_lang.name= :name,
		ps_stock_available.quantity= :quantity
		WHERE ps_product_shop.id_product = :id_product';
		$c= $this->pdo->prepare($sql);
		$c->bindValue(':id_product', $id);
		$c->bindValue(':price', $nominalPrice);
		$c->bindValue(':name', $name);
		$c->bindValue(':quantity', $quantity);
		$c->execute();
	}

	public function updateDetailedBoth($id, $nominalPrice, $name, $quantity, $description, $description_short, $meta_title, $meta_description, $link, $condition, $active){
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
		try{
		$this->pdo->beginTransaction();
		$c= $this->pdo->prepare($sql);
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
		$this->pdo->commit();
		}catch (PDOException $e){
			$this->pdo->rollBack();
			$error='Błąd w trakcie wykonywania serii zmian w produkcie';
		}
	}
	
	public function updateManufacturer($author, $id){
		$sql='UPDATE ps_product SET
		id_manufacturer= :id_manufacturer
		WHERE id_product = :id';
		$s=$this->pdo->prepare($sql);
		$s->bindValue(':id', $id);
		$s->bindValue(':id_manufacturer', $author);
		$s->execute();
	}

	public function updateQuantity($id, $quantity){
		$sql='UPDATE ps_stock_available SET
		quantity= :quantity
		WHERE id_product = :id';
		$s=$this->pdo->prepare($sql);
		$s->bindValue(':quantity', $quantity);
		$s->bindValue(':id', $id);
		$s->execute();
	}

	public function confirmation($id){
		$sql='SELECT id_product, quantity FROM ps_stock_available
		WHERE ps_stock_available.id_product= :id';
		$s=$this->pdo->prepare($sql);
		$s->bindValue(':id', $id);
		$s->execute();
		$s2 = $s->fetch();
		return ($s2);
	}

	public function image($id){
		$sql='SELECT id_image FROM ps_image
		WHERE id_product= :id AND cover=1';
		$s=$this->pdo->prepare($sql);
		$s->bindValue(':id', $id);
		$s->execute();
		$image=$s->fetch();
		$coverImage=$id.'-'.$image['id_image'];
		return ($coverImage);
	}

	public function getWholeImages($id){
		$sql='SELECT id_image FROM ps_image
		WHERE id_product= :id';
		$image=$this->pdo->prepare($sql);
		$image->bindValue(':id', $id);
		$image->execute();
		return $image;
	}

	public function getImagesTitles($id){
		$sql='SELECT legend FROM ps_image_lang 
		WHERE id_lang=3 AND id_image= :id';
		$s=$this->pdo->prepare($sql);
		$s->bindValue(':id', $id);
		$s->execute();
		$result=$s->fetch();
		return $result['legend'];
	}
}
