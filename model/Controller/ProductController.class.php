<?php

class ProductController
{
	private $creator;

	public function __construct($firstDBHandler, $secondDBHandler){
		$this->creator= new ProductCreator($firstDBHandler, $secondDBHandler);
	}

	public function getHelpers(){
		$result= $this->helper->selectWholeManufacturer();
		foreach ($result as $row){
			$authors[]= array('id'=> $row['id_manufacturer'], 'name'=> $row['name']);
		}
		$result= $this->helper->getCategoryData();
		foreach ($result as $row){
			$categories[]= array('id'=>$row['id_category'], 'name'=>$row['meta_title']);
		}
		$result= $this->helper->getModyfiedData();
		$product=$this->creator->createProduct('Ogicom');
		foreach ($result as $mod){
			$productReduction=$product->getReductionData($mod['id_number']);
			$mods[]= array('id'=>$mod['id_number'], 'nazwa'=>$mod['name'], 'data'=>$mod['date'], 'cena'=>number_format($mod['price'], 2,'.','').'zł', 'reduction'=>$productReduction);
		}
		return array($authors, $categories, $mods);
	}

	public function getOgicomImage($idNumber){
		$product=$this->creator->createProduct('Ogicom');
		$imageNumber= $product->image($idNumber);
		return $imageNumber;
	}

	public function productCategoryandManufacturerList($fullEdition, $id){
		if ($fullEdition=="new"){
			$product1=$this->creator->createProduct('LinuxPl');
		}elseif($fullEdition=='old'){
			$product1=$this->creator->createProduct('Ogicom');
		}
		$category = $product1->getCategory($id);
		foreach ($category as $category1){
			$selectedCats[]=$category1['id_category'];
		}
		$result= $product1->getEveryCategory();
		foreach ($result as $result2){
			$categoryList[]=array('id'=>$result2['id_category'], 'name'=>$result2['meta_title'], 'selected'=> in_array($result2['id_category'], $selectedCats));
		}
		$result= $this->helper->selectWholeManufacturer();
		foreach ($result as $row){
			$authors[]= array('id'=> $row['id_manufacturer'], 'name'=> $row['name']);
		}
		return array($categoryList,$authors);
	}

	public function productCompleteEdition($fullEdition, $id){
		if ($fullEdition=="new"){
			$product1=$this->creator->createProduct('LinuxPl');
			$product2=$this->creator->createProduct('Ogicom');
		}elseif($fullEdition=='old'){
			$product1=$this->creator->createProduct('Ogicom');
			$product2=$this->creator->createProduct('LinuxPl');
		}
		$Query = $product1->getWholeDetailsQuery($id);
		$QueryResult = $Query->fetch();
		$QueryResult['reduction']=$product1->getReductionData($id);
		$QueryResult['manufacturer'] = $product1->selectManufacturer($id);
		$QueryResult['secondPrice'] = $product2->getPrice($id);
		$QueryResult['reduction2']= $product2->getReductionData($id);
		return $QueryResult;
	}

	public function productCompleteUpdate($completeUpdate, $id, $newPrice, $oldPrice, $delete, $change, $text, $quantity, $description, $decriptionShort, 
		$metaTitle, $metaDescription, $link, $condition, $active, $manufacturer, $category, $tags){
		if($completeUpdate=="LinuxPl"){
			$product1=$this->creator->createProduct('LinuxPl');
			$product2=$this->creator->createProduct('Ogicom');
			if ($change== "nameChange"){
				$oldQuery = $product2->insertModyfy($id, $text);
			}
		}elseif($completeUpdate=="Ogicom"){
			$product1=$this->creator->createProduct('Ogicom');
			$product2=$this->creator->createProduct('LinuxPl');
			$priceNew=$newPrice;
			$priceOld=$oldPrice;
			$newPrice=$priceOld;
			$oldPrice=$priceNew;
			if ($change== "nameChange"){
				$oldQuery = $product1->insertModyfy($id, $text);
			}
		}
		if (isset($delete) and $delete== "deleteImages"){
			$Query = $product1->deleteImage($id);
		}
		$Query = $product1->updateDetailedBoth($id, $newPrice, $text, $quantity, $description, $decriptionShort, $metaTitle, $metaDescription, str_replace(" ","-", $link), $condition, $active);
		$sth=str_replace(" ", "-", $link);
		$Query = $product1->updateManufacturer($manufacturer, $id);
		$Query = $product1->deleteCategory($id);
		if(isset($category)){
			$Query = $product1->insertCategory($category, $id);
		}
		$Query = $product1->deleteWholeTag($id);
		foreach (explode(", ", $tags) as $tagText){
			$Query = $product1->checkIfTag($tagText);
			$Query2 = $Query->fetch();
			$checkedTagId= $Query2[0];
			if($checkedTagId!=0){
				$Query = $product1->insertTag($checkedTagId, $id);
			}else{
				$Query = $product1->createTag($checkedTagId, $tagText);
				$Query = $product1->checkIfTag($tagText);
				$Query2 = $Query->fetch();
				$checkedTagId= $Query2[0];
				$Query = $product1->insertTag($checkedTagId, $id);
			}
		}
		$outputOrderOrProduct1 = $product1->confirmation($id);
		return $outputOrderOrProduct1;
	}

	public function productCompleteSecondUpdate($completeUpdate, $id, $newPrice, $oldPrice, $delete, $change, $text, $quantity, $description, $decriptionShort, 
		$metaTitle, $metaDescription, $link, $condition, $active, $manufacturer, $category, $tags){
		if($completeUpdate=="LinuxPl"){
			$product=$this->creator->createProduct('Ogicom');
		}elseif($completeUpdate=="Ogicom"){
			$product=$this->creator->createProduct('LinuxPl');
			$priceNew=$newPrice;
			$priceOld=$oldPrice;
			$newPrice=$priceOld;
			$oldPrice=$priceNew;
		}
		if (isset($delete) and $delete== "deleteImages"){
			$Query = $product1->deleteImage($id);
		}
		$Query = $product->updateDetailedBoth($id, $oldPrice, $text, $quantity, $description, $decriptionShort, $metaTitle, $metaDescription, str_replace(" ","-", $link), $condition, $active);
		$Query = $product->updateManufacturer($manufacturer, $id);
		$outputOrderOrProduct1=$product->confirmation($_POST['id']);
		$Query = $product->deleteCategory($_POST['id']);
		if(isset($category)){
			$Query = $product->insertDifferentCategory($category, $id);
		}
		$Query = $product->deleteWholeTag($id);
		foreach (explode(", ", $tags) as $tagText){
			$Query = $product->checkIfTag($tagText);
			$Query2 = $Query->fetch();
			$checkedTagId= $Query2[0];
			if($checkedTagId!=0){
				$Query = $product->insertTag($checkedTagId, $id);
			}else{
				$Query = $product->createTag($checkedTagId, $tagText);
				$Query = $product->checkIfTag($tagText);
				$Query2 = $Query->fetch();
				$checkedTagId= $Query2[0];
				$Query = $product->insertTag($checkedTagId, $id);
			}
		}
		return $outputOrderOrProduct1;
	}

	public function productDoubleUpdate($id, $price, $text, $quantity){
		$product=$this->creator->createProduct('LinuxPl');
		$newQuery = $product->updateBoth($id, $price, $text, $quantity);
		$result['first'] = $product->confirmation($_POST['id']);
		$product2=$this->creator->createProduct('Ogicom');
		$oldQuery = $product2->updateBoth($id, $price, $text, $quantity);
		$result['second'] = $product2->confirmation($_POST['id']);
		return $result;
	}

	public function productIdSearchLinuxPl($idNumber){
		$product=$this->creator->createProduct('LinuxPl');
		$newQueryResult = $product->getProductDetailedData($idNumber);
		$newQueryResult['price']=number_format($newQueryResult['price'], 2,'.','');
		if($product->getReductionData($idNumber)!=''){
			$newQueryResult['reduct']=$product->countRealPrice($newQueryResult['price'],$product->getReductionData($idNumber));
		}
		return $newQueryResult;
	}

	public function productIdSearchOgicom($idNumber){
		$product=$this->creator->createProduct('Ogicom');
		$oldQueryResult = $product->getProductDetailedData($idNumber);
		$oldQueryResult['price']=number_format($oldQueryResult['price'], 2,'.','');
		if($product->getReductionData($idNumber)!=''){
			$oldQueryResult['reduct']=$product->countRealPrice($oldQueryResult['price'],$product->getReductionData($idNumber));
		}
		return $oldQueryResult;
	}

	public function productPhraseSearch($implodeSelect){
		$product1=$this->creator->createProduct('LinuxPl');
		$product2=$this->creator->createProduct('Ogicom');
		$newQuery = $product1->getProductData($implodeSelect);
		foreach ($newQuery as $newQuery2){
			$imageNumber= $product2->image($newQuery2['id_product']);
			$reduction=$product1->getReductionData($newQuery2['id_product']);
			$oldQuery = $product2->getProductQuery($newQuery2['id_product']);
			$oldQuery2 = $oldQuery->fetch();
			$reduction2=$product2->getReductionData($oldQuery2['id_product']);
			if(($oldQuery2['name']==$newQuery2['name'])AND($oldQuery2['quantity']==$newQuery2['quantity'])){
				$queryResult=array('confirmation'=>'Zgodność ilości i nazw produktu nr '.$oldQuery2['id_product'], 'coherence'=>1);
			}elseif(($oldQuery2['name']==$newQuery2['name'])AND($oldQuery2['quantity']!=$newQuery2['quantity'])){
				$queryResult="Ilość produktu ".$oldQuery2['id_product']." w starym panelu to: ".$oldQuery2['quantity'];
			}elseif(($oldQuery2['name']!=$newQuery2['name'])AND($oldQuery2['quantity']==$newQuery2['quantity'])){
				$queryResult="Nazwa produktu ".$oldQuery2['id_product']." w starej bazie to: ".$oldQuery2['name'];
			}elseif(($oldQuery2['name']!=$newQuery2['name'])AND($oldQuery2['quantity']!=$newQuery2['quantity'])){
				$queryResult="Podwójna niezgodność - (SB): ".$oldQuery2['name'].", a ilość to: ".$oldQuery2['quantity'];
			}
			if(($reduction!=0)AND($reduction2!=0)){
				$searchResult[]=array('id'=>$newQuery2['id_product'], 'name'=>$newQuery2['name'], 'quantity'=>$newQuery2['quantity'], 'priceRed'=>($product1->countRealPrice($newQuery2['price'], $reduction)), 'result'=>$queryResult, 'priceRed2'=>($product2->countRealPrice($oldQuery2['price'], $reduction2)), 'priceResult'=>1, 'imgNumber'=>$imageNumber);
			}elseif(($reduction==0)AND($reduction2!=0)){
				$searchResult[]=array('id'=>$newQuery2['id_product'], 'name'=>$newQuery2['name'], 'quantity'=>$newQuery2['quantity'], 'price'=>number_format($newQuery2['price'], 2,'.','').'zł', 'result'=>$queryResult, 'priceRed2'=>($product2->countRealPrice($oldQuery2['price'], $reduction2)),'priceResult'=>1, 'imgNumber'=>$imageNumber);
			}elseif(($reduction!=0)AND($reduction2==0)){
				$searchResult[]=array('id'=>$newQuery2['id_product'], 'name'=>$newQuery2['name'], 'quantity'=>$newQuery2['quantity'], 'priceRed'=>($product1->countRealPrice($newQuery2['price'], $reduction)), 'result'=>$queryResult, 'price2'=>number_format($oldQuery2['price'], 2,'.','').'zł', 'priceResult'=>1, 'imgNumber'=>$imageNumber);
			}else{
				$searchResult[]=array('id'=>$newQuery2['id_product'], 'name'=>$newQuery2['name'], 'quantity'=>$newQuery2['quantity'], 'price'=>number_format($newQuery2['price'], 2,'.','').'zł', 'result'=>$queryResult, 'price2'=>number_format($oldQuery2['price'], 2,'.','').'zł', 'imgNumber'=>$imageNumber);
			}
		}
		return $searchResult;
	}

	public function productSearchRowDeletion($delete){
		$result= $this->helper->deleteModyfied($delete);
		executeController('product');
	}

	public function productSelectedCategories($fullEdition, $id){
		if ($fullEdition=="new"){
			$product1=$this->creator->createProduct('LinuxPl');
		}elseif($fullEdition=='old'){
			$product1=$this->creator->createProduct('Ogicom');
		}
		$category = $product1->getCategory($id);
		foreach ($category as $category1){
			$selCategories[]=array('name'=>$category1['meta_title']);
		}
		return $selCategories;
	}

	public function productShortEdition($idNumber){
		$product=$this->creator->createProduct('LinuxPl');
		$bothEdit = $product->getProductDetailedData($idNumber);
		if($product->getReductionData($idNumber)!=0){
			$bothEdit['countReduction']=$product->countReduction($bothEdit['price'], $product->getReductionData($idNumber));
		}
		$product2=$this->creator->createProduct('Ogicom');
		$bothEdit['price2']= $product2->getPrice($idNumber);
		if($product2->getReductionData($idNumber)!=0){
			$bothEdit['countReduction2']=$product2->countReduction($bothEdit['price2'], $product2->getReductionData($idNumber));
		}
		return $bothEdit;
	}

	public function productTag($fullEdition, $id){
		if ($fullEdition=="new"){
			$product1=$this->creator->createProduct('LinuxPl');
		}elseif($fullEdition=='old'){
			$product1=$this->creator->createProduct('Ogicom');
		}
		$selectTag = $product1->selectTag($id);
		foreach ($selectTag as $tag){
			if($tag!=''){
				$this3[]=array('id'=>$tag['id_tag'],'name'=>$tag['name']);
			}else{
				$tag='';
				$this3='';
			}
		}
		foreach ($this3 as $this4){
			$tagNames[]=$this4['name']; 
			$completeTagNames=implode(", ", $tagNames);
		}
		return $completeTagNames;
	}

	public function setHelper($helper){
		$this->helper=$helper;
	}
}
