<?php

class OrderOutput extends OutputController{

	public function renderOrderStandardView(){
		$output = $this->twig->render('/orderSearch.html');
		echo $output;
	}

	public function renderOrderError( $error ){
		$output = $this->twig->render('/orderSearch.html', array(
			'title' => 'Niepowodzenie wykonania operacji',
			'result' => 'UWAGA! Operacja zakończona niepowodzeniem!',
			'message' => $error,
			'controller' => 'order',
		));
		echo $output;
	}

	public function renderOrderSearch( $result, $variables ){
		$output = $this->twig->render('/orderSearchResult.html', array(
			'result' => $result,
			'variables'=>$variables,
		));
		echo $output;
	}

	public function renderOrderExtraDetails( $path, $detail, $mailData= null ){
		if($mailData===null){
			$output = $this->twig->render('/'.$path.'.html', array(
				'result' => $detail,
			));
		}else{
			$output = $this->twig->render('/'.$path.'.html', array(
				'result' => $detail,
				'customerData'=>$mailData,
			));
		}
		echo $output;
	}

	public function renderMultipleProductMerge( $mergeDetails, $dates ){
		$output = $this->twig->render('/orderUpdate.html', array(
			'dates' => $dates,
			'orderDetails'=>$mergeDetails,
		));
		echo $output;
	}
}