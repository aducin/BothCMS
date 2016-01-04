<?php

class ProductOutput extends OutputController{

	public function renderProductStandardView( $helper ){
		if( !isset($helper[2]) ){
			$output = $this->twig->render('/productSearch.html', array(
			'authors' => $helper[0],
			'categories'=>$helper[1],
			));
		} else {
			$output = $this->twig->render('/productSearch.html', array(
			'authors' => $helper[0],
			'categories'=>$helper[1],
			'mods'=>$helper[2],
			));
		}
		echo $output;
	}

	public function renderProductError( $error ){
		$output = $this->twig->render('/productSearch.html', array(
			'title' => 'Niepowodzenie wykonania operacji',
			'result' => 'UWAGA! Operacja zakończona niepowodzeniem!',
			'message' => $error,
			'controller' => 'product',
		));
		echo $output;
	}

	public function productIdSearch( $productIdSearch, $oldQueryResult, $imageNumber ){
		$output = $this->twig->render('/idProductResult.html', array(
		'result1' => $productIdSearch,
		'result2' => $oldQueryResult,
		'imageNumber' => $imageNumber,
		));
		echo $output;
	}

	public function productPhraseSearch( $phraseSearchResult, $productPhraseSearch ){
		$output = $this->twig->render('/phraseResult.html', array(
		'result' => $phraseSearchResult,
		'phrase'=>$productPhraseSearch,
		));
		echo $output;
	}

	public function renderShortProductEdition( $path, $productEdition ){
		$output = $this->twig->render('/'.$path.'.html', array(
			'result' => $productEdition,
		));
		echo $output;
	}

	public function renderCompleteProductEdition( 
		$editForm, 
		$completeQueryResult, 
		$categoryAndAuthorList, 
		$completeTagNames, 
		$selCategories,
		$imageNumber ){
		$indexArray = array 
			('1'=>array('indexed'=>'0','activeness'=>'Nieaktywny'),
			'2'=>array('indexed'=>'1','activeness'=>'Aktywny')
		);
		$condArray = array (
			'1'=>array('condition'=>'new','value'=>'Nowy'),
			'2'=>array('condition'=>'used','value'=>'Używany'),
			'3'=>array('condition'=>'refurbished','value'=>'Odnowiony')
		);
		$output = $this->twig->render('/completeEditionResult.html', array(
			'editForm' => $editForm,
			'QueryResult' => $completeQueryResult,
			'authors'=> $categoryAndAuthorList[1],
			'completeTagNames'=>$completeTagNames,
			'completeCatNames'=>$categoryAndAuthorList[0],
			'selCategories'=>$selCategories,
			'indexArray'=>$indexArray,
			'condArray'=>$condArray,
			'imageNumber'=>$imageNumber['all'],
			'singleNumber'=>$imageNumber['single'],)
		);
		echo $output;
	}
}