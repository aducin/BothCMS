<?php 

abstract class Controller
{
	private $pdo;
	private $secondPDO;

	public function __construct($firstDBHandler, $secondDBHandler){
		$this->pdo=$firstDBHandler;
		$this->secondPDO=$secondDBHandler;
	}

	public function checkOrderDetail($orderNumber){
		$order= new OgicomOrder($this->secondPDO);
		$nameDetails = $order->getQueryLessDetails($orderNumber);
		$nameDetails['reducedTotalProduct']=$nameDetails['total_products']*0.85;
		$nameDetails['orderNumber']=$orderNumber;
		$nameDetails['reducedTotal']=$nameDetails['total_paid']*0.85;
		$detailsCount=$order->getCount($orderNumber);
		$nameDetails['count'] = $detailsCount['COUNT(product_name)'];
		return $nameDetails;
	}

	public function checkUndeliveredData($orderNumber){
		$order= new OgicomOrder($this->secondPDO);
		$confOrderData = $order->getQueryLessDetails($orderNumber);
		$confOrderData['ordNumb']=$orderNumber;
		return $confOrderData;
	}

	public function getCustomerData($customerNumber){
		$order= new OgicomOrder($this->secondPDO);
		$customerData= $order->getOrderCustomerData($customerNumber);
		$customer= new LinuxPlCustomer($this->pdo);
		if($customer->checkIfClientExists($customerData['email'])==6){
			$customerData['new'] = $customer->checkIfClientExists($customerData['email']);
		}
		$customerData['voucherLast']= $order->getLastVoucherNumber($customerNumber);
		$customerData['idCustomer']=$customerNumber;
		return $customerData;
	}

	public function getOgicomImage($idNumber){
		$product= new OgicomProduct($this->secondPDO);
		$imageNumber= $product->image($idNumber);
		return $imageNumber;
	}

	public function getOrderDetails($orderNumber){
		$order= new OgicomOrder($this->secondPDO);
		$details = $order->getQueryDetails($orderNumber);
		foreach ($details as $sDetail){
			$detail2[]=array('id'=>$sDetail['product_id'], 'name'=>$sDetail['name'], 'price'=>number_format($sDetail['product_price'], 2,'.',''), 'reduction'=>number_format($sDetail['reduction_amount'], 2,'.',''), 'reducedPrice'=>($sDetail['product_price']-$sDetail['reduction_amount'])*0.85, 'quantity'=>$sDetail['product_quantity'], 'reducedTotalPrice'=>($sDetail['product_price']-$sDetail['reduction_amount'])*$sDetail['product_quantity']*0.85);
			}
		return $detail2;	
	}

	public function getOrderInformations($option, $orderNumber){
		if($option==1){
			$order= new LinuxPlOrder($this->pdo);
			$query = $order->getQuery($orderNumber);
			$product= new OgicomProduct($this->secondPDO);
		}elseif($option==2){
			$order= new OgicomOrder($this->secondPDO);
			$query = $order->getQuery($_GET['oldorder']);
			$product= new LinuxPlProduct($this->pdo);
			$product2= new OgicomProduct($this->secondPDO);
		}
		foreach ($query as $query2){
			$otherQuery = $product->getProductQuery($query2['product_id']);
			$otherQuery2 = $otherQuery->fetch();
			if($option==1){
				$imageNumber= $product->image($query2['product_id']);
			}elseif($option==2){
				$imageNumber= $product2->image($query2['product_id']);
			}
			if(($otherQuery2['name']==$query2['name'])AND($otherQuery2['quantity']==$query2['quantity'])){
				$queryResult="Zgodność ilości i nazw produktu nr ".$otherQuery2['id_product'];
			}elseif(($otherQuery2['name']==$query2['name'])AND($otherQuery2['quantity']!=$query2['quantity'])){
				$queryResult="Ilość produktu ".$otherQuery2['id_product']." w drugim panelu to: ".$otherQuery2['quantity'];
			}elseif(($otherQuery2['name']!=$query2['name'])AND($otherQuery2['quantity']==$query2['quantity'])){
				$queryResult="Nazwa produktu ".$otherQuery2['id_product']." w drugiej bazie to: ".$otherQuery2['name'];
			}elseif(($otherQuery2['name']!=$query2['name'])AND($otherQuery2['quantity']!=$query2['quantity'])){
				$queryResult="Podwójna niezgodność - (SB): ".$otherQuery2['name'].", a ilość to: ".$oldQuery2['quantity'];
			}
			$result[]=array('id'=>$query2['product_id'], 'name'=>$query2['name'], 'onStock'=>$query2['product_quantity'], 'quantity'=>$query2['quantity'], 'nameResult'=>$queryResult, 'imgNumber'=>$imageNumber);
		}
		return $result;
	}

	public function getUndeliveredData($orderNumber){
		$order= new OgicomOrder($this->secondPDO);
		$confOrderDetail = $order->getQueryDetails($orderNumber);
		foreach ($confOrderDetail as $detail){
			$confOrderDetails[]=array('id'=>$detail['product_id'], 'name'=>$detail['name'], 'price'=>number_format($detail['product_price'], 2,'.',''), 'reduction'=>number_format($detail['reduction_amount'], 2,'.',''), 'quantity'=>$detail['product_quantity']);
		}
		return $confOrderDetails;
	}

	public function getVoucherHistory($customerNumber){
		$ordNumb=0;
		$order= new OgicomOrder($this->secondPDO);
		$voucherHistory= $order->getVoucherNumber($customerNumber);
		foreach ($voucherHistory as $custOrder){
			$ordNumb++;
			$custOrders[]= array('id'=>$custOrder['id_order'], 'reference'=>$custOrder['reference'], 'total'=>$custOrder['total_products'], 'shipping'=>$custOrder['total_shipping'], 'date'=>$custOrder['date_add'], 'orderNumber'=>((($ordNumb-1) % 6) + 1 ));
		}
		return $custOrders;
	}

	public function mergeIntoNewHelper($productNumber){
		$mergeDetails=array('idOrder'=>$productNumber.' w nowym panelu', 'base'=>'Ilość w SP', 'current'=>'Obecna ilość (NP)', 'changed'=>'Ilość po modyfikacji (NP)');
		return $mergeDetails;
	}

	public function mergeIntoNew($productNumber){
		$order= new OgicomOrder($this->secondPDO);
		$product= new LinuxPlProduct($this->pdo);
		$Query = $order->selectOrderQuantity($productNumber);
		foreach ($Query as $Query2){
			$oldQuantity=$product->getQuantity($Query2['product_id']);
			$quantityUpdate=$product->updateQuantity($Query2['quantity'], $Query2['product_id']);
			$finalQuantity=$product->getQuantity($Query2['product_id']);
			$mods[]=array('quantity'=>$Query2['quantity'], 'product_id'=>$Query2['product_id'], 'previousQuantity'=>$oldQuantity, 'finalQuantity'=>$finalQuantity, 'result'=>(string)($finalQuantity-$oldQuantity));
		}
		return $mods;
	}

	public function mergeIntoOldHelper($productNumber){
		$mergeDetails=array('idOrder'=>$productNumber.' w nowym panelu', 'base'=>'Ilość w SP', 'current'=>'Obecna ilość (NP)', 'changed'=>'Ilość po modyfikacji (NP)');
		return $mergeDetails;
	}

	public function mergeIntoOld($productNumber){
		$order= new LinuxPlOrder($this->pdo);
		$product= new OgicomProduct($this->secondPDO);
		$Query = $order->selectOrderQuantity($productNumber);
		foreach ($Query as $Query2){
			$oldQuantity=$product->getQuantity($Query2['product_id']);
			$quantityUpdate=$product->updateQuantity($Query2['quantity'], $Query2['product_id']);
			$finalQuantity=$product->getQuantity($Query2['product_id']);
			$mods[]=array('quantity'=>$Query2['quantity'], 'product_id'=>$Query2['product_id'], 'previousQuantity'=>$oldQuantity, 'finalQuantity'=>$finalQuantity, 'result'=>(string)($finalQuantity-$oldQuantity));
		}
		return $mods;
	}

	public function MergeSingleQuantity($dbNumber, $id, $quantity){
		if($dbNumber==2){
			$product= new LinuxPlProduct($this->pdo);
		}elseif($dbNumber==1){
			$product= new OgicomProduct($this->secondPDO);
		}
		$Query = $product->updateQuantity($quantity, $id);
		$outputOrderOrProduct1 = $product->confirmation($id);
		return $outputOrderOrProduct1;
		unset($product);
	}

	public function orderCheck($orderNumber){
		$order= new OgicomOrder($this->secondPDO);
		$orderSearch = $order->checkIfVoucherDue($orderNumber);
		$totalProducts= array('total'=>$orderSearch['total_products'], 'idCustomer'=>$orderSearch['id_customer']);
		return $totalProducts;
	}

	public function productCategoryList($fullEdition, $id){
		if ($fullEdition=="new"){
			$product1= new LinuxPlProduct($this->pdo);
		}elseif($fullEdition=='old'){
			$product1= new OgicomProduct($this->secondPDO);
		}
		$category = $product1->getCategory($id);
		foreach ($category as $category1){
			$selectedCats[]=$category1['id_category'];
		}
		$result= $product1->getEveryCategory();
		foreach ($result as $result2){
			$categoryList[]=array('id'=>$result2['id_category'], 'name'=>$result2['meta_title'], 'selected'=> in_array($result2['id_category'], $selectedCats));
		}
		return $categoryList;
	}

	public function productCompleteEdition($fullEdition, $id){
		if ($fullEdition=="new"){
			$product1= new LinuxPlProduct($this->pdo);
			$product2= new OgicomProduct($this->secondPDO);
		}elseif($fullEdition=='old'){
			$product1= new OgicomProduct($this->secondPDO);
			$product2= new LinuxPlProduct($this->pdo);
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
			$product1= new LinuxPlProduct($this->pdo);
			$product2= new OgicomProduct($this->secondPDO);
			if ($change== "nameChange"){
				$oldQuery = $product2->insertModyfy($id, $text);
			}
		}elseif($completeUpdate=="Ogicom"){
			$product1= new OgicomProduct($this->secondPDO);
			$product2= new LinuxPlProduct($this->pdo);
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
			$product= new OgicomProduct($this->secondPDO);
		}elseif($completeUpdate=="Ogicom"){
			$product= new LinuxPlProduct($this->pdo);
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
		$product= new LinuxPlProduct($this->pdo);
		$newQuery = $product->updateBoth($id, $price, $text, $quantity);
		$result['first'] = $product->confirmation($_POST['id']);
		$product2= new OgicomProduct($this->secondPDO);
		$oldQuery = $product2->updateBoth($id, $price, $text, $quantity);
		$result['second'] = $product2->confirmation($_POST['id']);
		return $result;
	}

	public function productIdSearchLinuxPl($idNumber){
		$product= new LinuxPlProduct($this->pdo);
		$newQueryResult = $product->getProductDetailedData($idNumber);
		$newQueryResult['price']=number_format($newQueryResult['price'], 2,'.','');
		if($product->getReductionData($idNumber)!=''){
			$newQueryResult['reduct']=$product->countRealPrice($newQueryResult['price'],$product->getReductionData($idNumber));
		}
		return $newQueryResult;
	}

	public function productIdSearchOgicom($idNumber){
		$product= new OgicomProduct($this->secondPDO);
		$oldQueryResult = $product->getProductDetailedData($idNumber);
		$oldQueryResult['price']=number_format($oldQueryResult['price'], 2,'.','');
		if($product->getReductionData($idNumber)!=''){
			$oldQueryResult['reduct']=$product->countRealPrice($oldQueryResult['price'],$product->getReductionData($idNumber));
		}
		return $oldQueryResult;
	}

	public function productPhraseSearch($implodeSelect){
		$product1= new LinuxPlProduct($this->pdo);
		$product2= new OgicomProduct($this->secondPDO);
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
		$helper= new OgicomHelper($this->secondPDO);
		$result= $helper->deleteModyfied($delete);
		executeController('product');
		unset($helper);
	}

	public function productSelectedCategories($fullEdition, $id){
		if ($fullEdition=="new"){
			$product1= new LinuxPlProduct($this->pdo);
		}elseif($fullEdition=='old'){
			$product1= new OgicomProduct($this->secondPDO);
		}
		$category = $product1->getCategory($id);
		foreach ($category as $category1){
			$selCategories[]=array('name'=>$category1['meta_title']);
		}
		return $selCategories;
	}

	public function productShortEdition($idNumber){
		$product= new LinuxPlProduct($this->pdo);
		$bothEdit = $product->getProductDetailedData($idNumber);
		if($product->getReductionData($idNumber)!=0){
			$bothEdit['countReduction']=$product->countReduction($bothEdit['price'], $product->getReductionData($idNumber));
		}
		$product2= new OgicomProduct($this->secondPDO);
		$bothEdit['price2']= $product2->getPrice($idNumber);
		if($product2->getReductionData($idNumber)!=0){
			$bothEdit['countReduction2']=$product2->countReduction($bothEdit['price2'], $product2->getReductionData($idNumber));
		}
		return $bothEdit;
	}

	public function productTag($fullEdition, $id){
		if ($fullEdition=="new"){
			$product1= new LinuxPlProduct($this->pdo);
		}elseif($fullEdition=='old'){
			$product1= new OgicomProduct($this->secondPDO);
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

	public function sendNotification($origin, $orderNumber){
		if($origin=='linuxPl'){
			$order= new LinuxPlOrder($this->pdo);
			$notificationresult = $order->sendNotification($orderNumber);
		}elseif($origin=='ogicom'){
			$order= new OgicomOrder($this->secondPDO);
			$notificationresult = $order->sendNotification($orderNumber);
		}
		return $notificationresult;
		unset($order);
	}
}