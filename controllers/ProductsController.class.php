<?php

class ProductsController
{
	private $creator;
	private $helper;
	private $output;
	private $root_dir;

	public function __construct($firstDBHandler, $secondDBHandler) {
        
		$this->creator = new ProductCreator($firstDBHandler, $secondDBHandler);
		$this->helper = new OgicomHelper($secondDBHandler);
		$this->output = new OutputController();
		$this->root_dir = $_SERVER['DOCUMENT_ROOT'].'/Ad9bisCMS/controllers/output.php';
        if (!isset($_GET['action']))
            $this->getHelpers();
        else
            $this-> $_GET['action'] ();
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
			$mods[]= array(
				'id'=>$mod['id_number'], 
				'nazwa'=>$mod['name'], 
				'data'=>$mod['date'], 
				'cena'=>number_format($mod['price'], 2,'.','').'zł', 
				'reduction'=>$productReduction
			);
		}
		$helper=array($authors, $categories, $mods);
		require_once $this->root_dir; 
	}
	function idSearch(){
		$product=$this->creator->createProduct('LinuxPl');
		$productIdSearch = $product->getProductDetailedData($_GET['idnr']);
		$productIdSearch['price']=number_format($productIdSearch['price'], 2,'.','');
		if($product->getReductionData($_GET['idnr'])!=''){
			$productIdSearch['reduct']=$product->countRealPrice($newQueryResult['price'],$product->getReductionData($idNumber));
		}
		if($productIdSearch['name']==''){
			$error='Brak produktu o podanym ID.';
		}else{
			$product=$this->creator->createProduct('Ogicom');
			$oldQueryResult = $product->getProductDetailedData($_GET['idnr']);
			$oldQueryResult['price']=number_format($oldQueryResult['price'], 2,'.','');
			if($product->getReductionData($_GET['idnr'])!=''){
				$oldQueryResult['reduct']=$product->countRealPrice($oldQueryResult['price'],$product->getReductionData($_GET['idnr']));
			}
		}
		$imageNumber= $product->image($_GET['idnr']);
		require_once $this->root_dir; 
	}

	public function search(){
		if ($_GET['text'] =='' AND $_GET['category'] =='' AND $_GET['author'] ==''){
			$error='Nie chcesz chyba wypisywać wszystkich produktów z bazy...? Zaznacz chociaż z 1 kryterium wyszukiwania!';
		}else{
			$params[]=array(
				'text'=>$_GET['text'],
				'category'=>$_GET['category'],
				'author'=>$_GET['author']
			);
			foreach ($params as $result){
				if ($result['text'] == "") {
					unset($result['text']);
				} else {
					$prequery[] = " name LIKE '"."%".$_GET['text']."%'";
				}
				if ($result['category'] == ""){ 
					unset($result['category']);
				} else {
					$prequery[]= " id_category =".$_GET['category'];
				}
				if ($result['author'] == "") {
					unset($result['author']);
				} else {
					$prequery[] = " id_manufacturer =".$_GET['author'];
				}
				$implodeSelect=' WHERE'.implode(" AND",$prequery).' GROUP BY id_product ORDER BY id_product';
				$product1=$this->creator->createProduct('LinuxPl');
				$product2=$this->creator->createProduct('Ogicom');
				$newQuery = $product1->getProductData($implodeSelect);

				foreach ($newQuery as $newQuery2){
					$imageNumber= $product2->image($newQuery2['id_product']);
					$reduction=$product1->getReductionData($newQuery2['id_product']);
					$oldQuery = $product2->getProductQuery($newQuery2['id_product']);
					$oldQuery2 = $oldQuery->fetch();
					$reduction2=$product2->getReductionData($oldQuery2['id_product']);
					if (($oldQuery2['name'] == $newQuery2['name']) AND ($oldQuery2['quantity'] == $newQuery2['quantity'])){
						$queryResult=array(
							'confirmation'=>'Zgodność ilości i nazw produktu nr '.$oldQuery2['id_product'], 
							'coherence'=>1
						);
					} elseif (($oldQuery2['name'] == $newQuery2['name'])AND($oldQuery2['quantity'] != $newQuery2['quantity'])){
						$queryResult="Ilość produktu ".$oldQuery2['id_product']." w starym panelu to: ".$oldQuery2['quantity'];
					} elseif (($oldQuery2['name'] != $newQuery2['name']) AND ($oldQuery2['quantity'] == $newQuery2['quantity'])){
						$queryResult="Nazwa produktu ".$oldQuery2['id_product']." w starej bazie to: ".$oldQuery2['name'];
					} elseif (($oldQuery2['name'] != $newQuery2['name']) AND ($oldQuery2['quantity'] != $newQuery2['quantity'])){
						$queryResult="Podwójna niezgodność - (SB): ".$oldQuery2['name'].", a ilość to: ".$oldQuery2['quantity'];
					}
					if (($reduction!=0) AND ($reduction2!=0)){
						$phraseSearchResult[]=array(
						    'id'=>$newQuery2['id_product'], 
							'name'=>$newQuery2['name'], 
							'quantity'=>$newQuery2['quantity'], 
							'priceRed'=>($product1->countRealPrice($newQuery2['price'], $reduction)), 
							'result'=>$queryResult, 
							'priceRed2'=>($product2->countRealPrice($oldQuery2['price'], $reduction2)), 
							'priceResult'=>1, 
							'imgNumber'=>$imageNumber
						);
					} elseif (($reduction == 0)AND($reduction2 != 0)){
						$phraseSearchResult[]=array(
							'id'=>$newQuery2['id_product'], 
							'name'=>$newQuery2['name'], 
							'quantity'=>$newQuery2['quantity'], 
							'price'=>number_format($newQuery2['price'], 2,'.','').'zł', 
							'result'=>$queryResult, 
							'priceRed2'=>($product2->countRealPrice($oldQuery2['price'], $reduction2)),
							'priceResult'=>1, 
							'imgNumber'=>$imageNumber);
					} elseif (($reduction!=0) AND ($reduction2==0)){
						$phraseSearchResult[] = array('id'=>$newQuery2['id_product'], 
							'name'=>$newQuery2['name'], 
							'quantity'=>$newQuery2['quantity'], 
							'priceRed'=>($product1->countRealPrice($newQuery2['price'], $reduction)), 
							'result'=>$queryResult, 
							'price2'=>number_format($oldQuery2['price'], 2,'.','').'zł', 
							'priceResult'=>1, 
							'imgNumber'=>$imageNumber);
					} else {
						$phraseSearchResult[] = array(
							'id'=>$newQuery2['id_product'], 
							'name'=>$newQuery2['name'], 
							'quantity'=>$newQuery2['quantity'], 
							'price'=>number_format($newQuery2['price'], 2,'.','').'zł', 
							'result'=>$queryResult, 
							'price2'=>number_format($oldQuery2['price'], 2,'.','').'zł', 
							'imgNumber'=>$imageNumber);
					}
				}
				if (!isset($phraseSearchResult)){
					$error='W bazie nie znaleziono produktów spełniających podane kryteria!';
				} else {
					$productPhraseSearch=($_GET['text']);
				}	
			}
		}
		require_once $this->root_dir;
	}

	public function shortEdition(){
		try{
			$product=$this->creator->createProduct('LinuxPl');
			$productShortEdition = $product->getProductDetailedData($_GET['id']);
			if($product->getReductionData($_GET['id'])!=0){
				$productShortEdition['countReduction']=$product->countReduction($productShortEdition['price'], $product->getReductionData($_GET['id']));
			}
			$product2=$this->creator->createProduct('Ogicom');
			$productShortEdition['price2']= $product2->getPrice($_GET['id']);
			if($product2->getReductionData($_GET['id'])!=0){
				$productShortEdition['countReduction2']=$product2->countReduction($productShortEdition['price2'], $product2->getReductionData($_GET['id']));
			}
		}catch (PODException $e){
			$error='Błąd przy pobieraniu informacji o produkcie: ' . $e->getMessage();
		}
		$this->output->renderView('editionShortTemplate', $productShortEdition);
	}

	public function completeEdition(){
		if (isset($_GET['linux'])){
			$editForm='editcompleteformnew';
			$product1=$this->creator->createProduct('LinuxPl');
			$product2=$this->creator->createProduct('Ogicom');
		}elseif(isset($_GET['ogicom'])){
			$editForm='editcompleteformold';
			$product1=$this->creator->createProduct('Ogicom');
			$product2=$this->creator->createProduct('LinuxPl');
		}
		$Query = $product1->getWholeDetailsQuery($_GET['id']);
		$completeQueryResult = $Query->fetch();
		$completeQueryResult['reduction']=$product1->getReductionData($_GET['id']);
		$completeQueryResult['manufacturer'] = $product1->selectManufacturer($_GET['id']);
		$completeQueryResult['secondPrice'] = $product2->getPrice($_GET['id']);
		$completeQueryResult['reduction2']= $product2->getReductionData($_GET['id']);
		$category = $product1->getCategory($_GET['id']);
		foreach ($category as $category1){
			$selCategories[]=array('name'=>$category1['meta_title']);
			$selectedCats[]=$category1['id_category'];
		}
		$result= $product1->getEveryCategory();
		foreach ($result as $result2){
			$categoryList[]=array(
				'id'=>$result2['id_category'], 
				'name'=>$result2['meta_title'], 
				'selected'=> in_array($result2['id_category'], $selectedCats)
			);
		}
		$result= $this->helper->selectWholeManufacturer();
		foreach ($result as $row){
			$authors[]= array(
				'id'=> $row['id_manufacturer'], 
				'name'=> $row['name']
			);
		}
		$categoryAndAuthorList=array($categoryList,$authors);
		$selectTag = $product1->selectTag($_GET['id']);
		foreach ($selectTag as $tag){
			if($tag!=''){
				$tagNames[]=array(
					'id'=>$tag['id_tag'],
					'name'=>$tag['name']
				);
			}else{
				$tag='';
				$tagNames='';
			}
		}
		foreach ($tagNames as $tagName){
			$tags[]=$tagName['name']; 
			$completeTagNames=implode(", ", $tags);
		}
		require_once $this->root_dir;
	}

	public function updateShort(){
		if ($_POST['text']==''){
			$error='Brak aktualnego wpisu: nazwa produktu!';
		}elseif ($_POST['quantity']==''){
			$error='Brak aktualnego wpisu: nowa liczba produktu!';
		}else try{
			$product=$this->creator->createProduct('LinuxPl');
			$newQuery = $product->updateBoth($_POST['id'], $_POST['nominalPriceNew'], $_POST['text'], $_POST['quantity']);
			$outputOrderOrProduct1['first'] = $product->confirmation($_POST['id']);
			$product2=$this->creator->createProduct('Ogicom');
			$oldQuery = $product2->updateBoth($_POST['id'], $_POST['nominalPriceOld'], 
			$_POST['text'], $_POST['quantity']);
			$outputOrderOrProduct1['second'] = $product2->confirmation($_POST['id']);
		}catch (PDOExceptioon $e){
			$error='Aktualizacja danych nie powiodła się: ' . $e->getMessage();
		}
		require_once $this->root_dir;
	}

	public function updateComplete(){
		if ($_POST['text']==''){
			$error='Musisz podać nazwę produktu!';
		}elseif ($_POST['quantity']==''){
			$error='Musisz podać nową ilość produktu!';
		}elseif ($_POST['categories']==''){
			$error='Nie znaleziono kategorii do zapisania!';
		}else try{
			if($_GET['action']=='editcompleteformnew'){
				$product1=$this->creator->createProduct('LinuxPl');
				$product2=$this->creator->createProduct('Ogicom');
				if ($change== "nameChange"){
					$oldQuery = $product2->insertModyfy($_POST['id'], $_POST['text']);
				}
			}elseif($_GET['action']=='editcompleteformold'){
				$product1=$this->creator->createProduct('Ogicom');
				$product2=$this->creator->createProduct('LinuxPl');
				$priceNew=$newPrice;
				$priceOld=$oldPrice;
				$newPrice=$priceOld;
				$oldPrice=$priceNew;
				if ($_POST['change']== "nameChange"){
					$oldQuery = $product1->insertModyfy($_POST['id'], $_POST['text']);
				}
			}
			if(!isset($_POST['delete'])){
				$_POST['delete']='';
			if(!isset($_POST['change']))
				$_POST['change']='';
			}
			if (isset($_POST['delete']) and $_POST['delete']== "deleteImages"){
				$product1->deleteImage($_POST['id']);
			}
			$Query = $product1->updateDetailedBoth($_POST['id'], $_POST['newPrice'], 
			$_POST['text'], $_POST['quantity'], $_POST['description'], 
			$_POST['decriptionShort'], $_POST['metaTitle'], $_POST['metaDescription'], 
			str_replace(" ","-", $link), $_POST['condition'], $_POST['active']);
			$sth=str_replace(" ", "-", $link);
			$Query = $product1->updateManufacturer($_POST['manufacturer'], $_POST['id']);
			$Query = $product1->deleteCategory($id);
			if(isset($category)){
				$Query = $product1->insertCategory($_POST['category'], $_POST['id']);
			}
			$Query = $product1->deleteWholeTag($_POST['id']);
			foreach (explode(", ", $tags) as $tagText){
				$Query = $product1->checkIfTag($tagText);
				$Query2 = $Query->fetch();
				$checkedTagId= $Query2[0];
				if($checkedTagId!=0){
					$Query = $product1->insertTag($checkedTagId, $_POST['id']);
				}else{
					$Query = $product1->createTag($checkedTagId, $tagText);
					$Query = $product1->checkIfTag($tagText);
					$Query2 = $Query->fetch();
					$checkedTagId= $Query2[0];
					$Query = $product1->insertTag($checkedTagId, $_POST['id']);
				}
			}
			$outputOrderOrProduct1['first'] = $product1->confirmation($_POST['id']);
		}catch (PDOExceptioon $e){
			$error='Aktualizacja produktu w edytowanej bazie nie powiodła się: ' . $e->getMessage();
		}
		if(!isset($error)){
			if (isset($_POST['howManyBases'])and $_POST['howManyBases']== 'both'){
				try{
					if (isset($_POST['delete']) and $_POST['delete']== "deleteImages"){
						$Query = $product2->deleteImage($_POST['id']);
					}
					$Query = $product2->updateDetailedBoth(
						$_POST['id'], $_POST['newPrice'], $_POST['text'], 
						$_POST['quantity'], $_POST['description'], $_POST['decriptionShort'], 
						$_POST['metaTitle'], $_POST['metaDescription'], str_replace(" ","-", $link), 
						$_POST['condition'], $_POST['active']);
					$Query = $product2->updateManufacturer($_POST['manufacturer'], $_POST['id']);
					$outputOrderOrProduct1['second']=$product2->confirmation($_POST['id']);
					$Query = $product2->deleteCategory($_POST['id']);
					if(isset($category)){
						$Query = $product2->insertDifferentCategory($_POST['category'], $_POST['id']);
					}
					$Query = $product2->deleteWholeTag($_POST['id']);
					foreach (explode(", ", $tags) as $tagText){
						$Query = $product2->checkIfTag($tagText);
						$Query2 = $Query->fetch();
						$checkedTagId= $Query2[0];
						if($checkedTagId!=0){
							$Query = $product2->insertTag($checkedTagId, $_POST['id']);
						}else{
							$Query = $product2->createTag($checkedTagId, $tagText);
							$Query = $product2->checkIfTag($tagText);
							$Query2 = $Query->fetch();
							$checkedTagId= $Query2[0];
							$Query = $product2->insertTag($checkedTagId, $_POST['id']);
						}
					}		 
				}catch (PDOExceptioon $e){
					$error='Aktualizacja produktu w drugiej bazie nie powiodła się: ' . $e->getMessage();
				}
			}
		}
		require_once $this->root_dir;
	}

	public function deleteRow(){
		$this->helper->deleteModyfied($_POST['idMod']);
		$this->getHelpers();
	}
}

	
