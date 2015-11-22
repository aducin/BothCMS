<?php

class OrdersController
{
	private $creator;
	private $LinuxPlOrder;
	private $LinuxPlHandler;
	private $OgicomOrder;
    private $output;
	private $existingClient;
	private $root_dir;

	public function __construct($firstDBHandler, $secondDBHandler) {
        
        $this->creator= new ProductCreator($firstDBHandler, $secondDBHandler);
        $this->LinuxPlHandler=$firstDBHandler;
		$this->LinuxPlOrder= new LinuxPlOrder($firstDBHandler);
		$this->OgicomOrder= new OgicomOrder($secondDBHandler);
        $this->output= new OutputController();
		$this->root_dir = $_SERVER['DOCUMENT_ROOT'].'/Ad9bisCMS/controllers/output.php';
        if(isset($_POST['action'])){
        	require_once $_SERVER['DOCUMENT_ROOT'].'/Ad9bisCMS/controllers/mail.php';
        }elseif (isset($_GET['action'])){
            $this-> $_GET['action'] ();
        }else{
            $this->output->renderStandardView();
        }
    }

    public function setExistingClient($client){
		$this->existingClient=$client;
	}

    private function finalOrderQuery($query, $product, $imageSource){
    	foreach ($query as $singleQuery){
			$nextQuery = $product->getProductQuery($singleQuery['product_id']);
			$finalQuery = $nextQuery->fetch();
			$imageNumber= $imageSource->image($singleQuery['product_id']);
			if(($finalQuery['name']==$singleQuery['name'])AND($finalQuery['quantity']==$singleQuery['quantity'])){
				$queryResult="Zgodność ilości i nazw produktu nr ".$finalQuery['id_product'];
			}elseif(($finalQuery['name']==$singleQuery['name'])AND($finalQuery['quantity']!=$singleQuery['quantity'])){
				$queryResult="Ilość produktu ".$finalQuery['id_product']." w drugim panelu to: ".$singleQuery['quantity'];
			}elseif(($finalQuery['name']!=$singleQuery['name'])AND($finalQuery['quantity']==$singleQuery['quantity'])){
				$queryResult="Nazwa produktu ".$finalQuery['id_product']." w drugiej bazie to: ".$singleQuery['name'];
			}elseif(($finalQuery['name']!=$singleQuery['name'])AND($finalQuery['quantity']!=$singleQuery['quantity'])){
				$queryResult="Podwójna niezgodność - (SB): ".$finalQuery['name'].", a ilość to: ".$singleQuery['quantity'];
			}
			$result[]=array('id'=>$singleQuery['product_id'], 'name'=>$singleQuery['name'], 'onStock'=>$singleQuery['product_quantity'], 'quantity'=>$singleQuery['quantity'], 'nameResult'=>$queryResult, 'imgNumber'=>$imageNumber);
		}if (!isset($result[0]['id'])){
			$result=0;
		}
		return $result;
    }

    public function newOrder(){
    	$query = $this->LinuxPlOrder->getQuery($_GET['neworder']);
    	$product= $this->creator->createProduct('Ogicom');
    	$imageSource= $this->creator->createProduct('Ogicom');
    	$result=$this->finalOrderQuery($query, $product, $imageSource);
    	$ordedSearch=array('onstock'=>'Na stanie (NP)', 'ordered'=>'Zamówione (NP)', 'button1'=>'Kompletna edycja w NP', 'button2'=>'Wyrównaj ilość w starej bazie', 'button3'=>'Zmiana obu przez nowy panel', 'button4'=>'Uaktualnij ilości w starej bazie', 'form'=>'linuxPl', 'action'=>'BPSQO', 'orderNr'=>$_GET['neworder']);
    	if( $result==0 ){
			$error = 'W bazie danych nie ma zamówienia o podanym numerze!';
            $this->output->renderErrorInOrder( $error );
		} else {
            $this->output->renderOrderSearch($result, $ordedSearch);
        }
    }

    public function oldOrder(){
    	$query = $this->OgicomOrder->getQuery($_GET['oldorder']);
    	$product= $this->creator->createProduct('LinuxPl');
    	$imageSource= $this->creator->createProduct('Ogicom');
    	$result=$this->finalOrderQuery($query, $product, $imageSource);
    	$ordedSearch=array('onstock'=>'Na stanie (SP)', 'ordered'=>'Zamówione (SP)', 'button1'=>'Kompletna edycja w SP', 'button2'=>'Wyrównaj ilość w nowej bazie', 'button3'=>'Zmiana obu przez stary panel', 'button4'=>'Uaktualnij ilości w nowej bazie','form'=>'ogicom', 'action'=>'BPSQN', 'orderNr'=>$_GET['oldorder']);
    	if( $result==0 ){
			$error = 'W bazie danych nie ma zamówienia o podanym numerze!';
            $this->output->renderErrorInOrder( $error );
		} else {
            $this->output->renderOrderSearch($result, $ordedSearch);
        }
    }

    public function orderVoucher(){
    	$orderSearch = $this->OgicomOrder->checkIfVoucherDue($_GET['orderVoucher']);
    	$totalProducts= array('total'=>$orderSearch['total_products'], 'idCustomer'=>$orderSearch['id_customer']);
    	if($totalProducts['total']<50){
    		$error='Kwota zamówienia wynosi '.$totalProducts['total'].'zł i jest zbyt mała, aby przyznać kolejny kupon.';
    	}elseif($totalProducts['total']==''){
    		$error='W bazie nie znaleziono zamówienia nr '.$_GET['orderVoucher'].'.';
    	}elseif($totalProducts['total']>=50){
    		$this->setExistingClient(new LinuxPlCustomer($this->LinuxPlHandler));
    		$customerData= $this->OgicomOrder->getOrderCustomerData($totalProducts['idCustomer']);
    		if($this->existingClient->checkIfClientExists($customerData['email'])==6){
    			$customerData['new'] = $this->existingClient->checkIfClientExists($customerData['email']);
    		}
    		$customerData['voucherLast']= $this->OgicomOrder->getLastVoucherNumber($totalProducts['idCustomer']);
    		$customerData['idCustomer']=$totalProducts['idCustomer'];
    		$ordNumb=0;
			$voucherHistory= $this->OgicomOrder->getVoucherNumber($totalProducts['idCustomer']);
			foreach ($voucherHistory as $custOrder){
				$ordNumb++;
				$voucherNumber[]= array('id'=>$custOrder['id_order'], 'reference'=>$custOrder['reference'], 'total'=>$custOrder['total_products'], 'shipping'=>$custOrder['total_shipping'], 'date'=>$custOrder['date_add'], 'orderNumber'=>((($ordNumb-1) % 6) + 1 ));
			}
			foreach ($voucherNumber as $custOrder){
				$customerData['voucher']=$custOrder['orderNumber'];
			}
    	}
        !isset($error) ? ($this->output->renderOrderExtraDetails('voucherSearchResult', $voucherNumber, $customerData)) :
        ($this->output->renderErrorInOrder( $error ));
    }

    public function detailorder(){
    	$sixthOrderDiscount = $this->OgicomOrder->getQueryLessDetails($_GET['detailorder']);
    	$sixthOrderDiscount['reducedTotalProduct']=$sixthOrderDiscount['total_products']*0.85;
    	$sixthOrderDiscount['orderNumber']=$_GET['detailorder'];
    	$detailsCount=$this->OgicomOrder->getCount($_GET['detailorder']);
    	$sixthOrderDiscount['count'] = $detailsCount['COUNT(product_name)'];
    	if ( !isset($sixthOrderDiscount['total_paid']) ){
    		$error="W starej bazie nie ma zamówienia o podanym numerze!";
            $this->output->renderErrorInOrder( $error );
    	} else {
            $sixthOrderDiscount['reducedTotal']=$sixthOrderDiscount['total_paid']*0.85;
    		$details = $this->OgicomOrder->getQueryDetails($_GET['detailorder']);
    		foreach ( $details as $sDetail ){
    			$detail[]=array(
                    'id'=>$sDetail['product_id'], 
                    'name'=>$sDetail['name'], 
                    'price'=>number_format($sDetail['product_price'], 2,'.',''), 
                    'reduction'=>number_format($sDetail['reduction_amount'], 2,'.',''), 
                    'reducedPrice'=>($sDetail['product_price']-$sDetail['reduction_amount'])*0.85, 
                    'quantity'=>$sDetail['product_quantity'], 
                    'reducedTotalPrice'=>($sDetail['product_price']-$sDetail['reduction_amount'])*$sDetail['product_quantity']*0.85
                );
    		}
            $this->output->renderOrderExtraDetails( 'discountSearchResult', $detail, $sixthOrderDiscount );
    	}
    }

    public function undeliveredConfirmation(){
    	$undeliveredOrderConf = $this->OgicomOrder->getQueryLessDetails($_GET['undeliveredConfirmation']);
    	$undeliveredOrderConf['ordNumb']=$_GET['undeliveredConfirmation'];
    	if(!isset($undeliveredOrderConf['total_paid'])){
    		$error="W starej bazie nie ma zamówienia o podanym numerze!";
    	}else{
    		$confOrderDetail = $this->OgicomOrder->getQueryDetails($_GET['undeliveredConfirmation']);
    		foreach ($confOrderDetail as $detail){
    			$confOrderDetails[]=array('id'=>$detail['product_id'], 'name'=>$detail['name'], 'price'=>number_format($detail['product_price'], 2,'.',''), 'reduction'=>number_format($detail['reduction_amount'], 2,'.',''), 'quantity'=>$detail['product_quantity']);
    		}
    	}
        !isset($error) ? $this->output->renderOrderExtraDetails('undeliveredMailOrder', $confOrderDetails, $undeliveredOrderConf) :
        ($this->output->renderErrorInOrder( $error ));
    }

    public function notification(){
    	if($_GET['send']=='ogicom'){
			$order= $this->OgicomOrder;
		}elseif($_GET['send']=='linuxPl'){
			$order= $this->LinuxPlOrder;
		}
		$orderTracking = $order->sendNotification($_GET['notification']);
		if($orderTracking==''){
			$error='W wybranej bazie danych brak zamówienia o podanym numerze!';
		}
        !isset($error) ? $this->output->renderOrderExtraDetails('shipmentNotification', $orderTracking) :
        ($this->output->renderErrorInOrder( $error ));
    }

    public function singleMerge(){
        try {
            if (isset($_GET['BPSQO'])){
                $product= $this->creator->createProduct('Ogicom');
            } elseif (isset($_GET['BPSQN'])){
                $product= $this->creator->createProduct('LinuxPl');
            }
            $Query = $product->updateQuantity($_GET['quantity'], $_GET['id']);
            $outputSingleOrder['first'] = $product->confirmation($_GET['id']);
            $this->output->renderSingleUpdate($outputSingleOrder);
        } catch (Exception $e) {
            $error='Nie udało się uaktualnić pojedynczego produktu!';
            $this->output->renderErrorInOrder( $error );
        }
    }

    public function orderMerge(){
    	try{
			if($_GET['mergeQuantities']== 'Uaktualnij ilości w nowej bazie'){
				$mergeDetails=array(
                    'idOrder'=>$_GET['id_number'].' w nowym panelu', 
                    'base'=>'Ilość w SP', 
                    'current'=>'Obecna ilość (NP)', 
                    'changed'=>'Ilość po modyfikacji (NP)'
                );
				$product= $this->creator->createProduct('LinuxPl');
				$Queries = $this->OgicomOrder->selectOrderQuantity($_GET['id_number']);
			}elseif ($_GET['mergeQuantities']== 'Uaktualnij ilości w starej bazie'){
				$mergeDetails=array(
                    'idOrder'=>$_GET['id_number'].' w nowym panelu', 
                    'base'=>'Ilość w SP', 
                    'current'=>'Obecna ilość (NP)', 
                    'changed'=>'Ilość po modyfikacji (NP)'
                );
				$product= $this->creator->createProduct('Ogicom');
				$Queries = $this->LinuxPlOrder->selectOrderQuantity($_GET['id_number']);
			}	
			foreach ($Queries as $Query){
				$oldQuantity=$product->getQuantity($Query['product_id']);
				$quantityUpdate=$product->updateQuantity($Query['quantity'], $Query['product_id']);
				$finalQuantity=$product->getQuantity($Query['product_id']);
				$mods[]=array(
                    'quantity'=>$Query['quantity'], 
                    'product_id'=>$Query['product_id'], 
                    'previousQuantity'=>$oldQuantity, 
                    'finalQuantity'=>$finalQuantity, 
                    'result'=>(string)($finalQuantity-$oldQuantity
                ));
            }
            $this->output->renderMultipleProductMerge( $mergeDetails, $mods );	
		}catch (PDOExceptioon $e){
			$error='Pobranie ilości w zamówieniu nie powiodło się: ' . $e->getMessage();
            $this->output->renderErrorInOrder( $error );
		}
    }

    private function orderSearch(){
    	if(isset($_GET['neworder'])AND($_GET['neworder']!='')){
    		$this->neworder();
    	}elseif(isset($_GET['oldorder'])AND($_GET['oldorder']!='')){
    		$this->oldorder();
    	}elseif(isset($_GET['orderVoucher'])AND($_GET['orderVoucher']!='')){
    		$this->orderVoucher();
    	}elseif(isset($_GET['detailorder'])AND($_GET['detailorder']!='')){
    		$this->detailorder();
    	}elseif(isset($_GET['undeliveredConfirmation'])AND($_GET['undeliveredConfirmation']!='')){
    		$this->undeliveredConfirmation();
    	}elseif(isset($_GET['notification'])AND($_GET['notification']!='')){
    		$this->notification();
    	}
    }
}