<!DOCTYPE html>
<html lang="pl">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Generowanie strony sitemap</title>
</head>
<body>
<?php
$root_dir = $_SERVER['DOCUMENT_ROOT'].'/Ad9bisCMS';
$DBHandler=parse_ini_file($root_dir.'/config/database.ini',true);
$secondHost=$DBHandler["secondDB"]["host"];
$secondLogin=$DBHandler["secondDB"]["login"];
$secondPassword=$DBHandler["secondDB"]["password"];
require_once $root_dir.'/config/bootstrap.php';
$dbHandlerOgicom= new DBHandler($secondHost, $secondLogin, $secondPassword);
$ogicomHandler = $dbHandlerOgicom->getDb();

$siteMap= new SitemapFacadeOgicom($ogicomHandler);
$siteMapData=$siteMap->method1();

foreach($siteMapData as $siteCreator){
	$siteCreator['date_upd']=(explode(" ", $siteCreator['date_upd']));
	$site[]=array('www'=>$siteCreator['link'].'/'.$siteCreator['id_product'].'-', "link"=>$siteCreator['link_rewrite'], "date_upd"=>$siteCreator['date_upd'][0], 'id'=>$siteCreator['id_product']);
}
$siteMapCategories=$siteMap->method2();
foreach($siteMapCategories as $siteCreator){
	$siteCreator['date_upd']=(explode(" ", $siteCreator['date_upd']));
	$siteCategory[]=array('catAdress'=>'http://modele-ad9bis.pl/'.$siteCreator['id_category'].'-'.$siteCreator['link_rewrite'], "date_upd"=>$siteCreator['date_upd'][0]);
}

//header('Content-type: text/xml; charset=utf-8');
$xml='<?xml version="1.0" encoding="utf-8"?>';
$xml.='<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';
$xml.='<url><loc>http://modele-ad9bis.pl/</loc><priority>1.0</priority><lastmod>';
$cls_date = new DateTime();
$xml.=$cls_date->format('Y-m-d');
$xml.='</lastmod><changefreq>weekly</changefreq></url>';

foreach($site as $newSite){
	$xml.='<url><loc>http://modele-ad9bis.pl/'.$newSite['www'].$newSite['link'].'</loc><priority>0.5</priority><lastmod>'.$newSite['date_upd'].'</lastmod><changefreq>weekly</changefreq>';
	$image=$siteMap->method3($newSite['id']);
	foreach($image as $singIm){
		$xml.= '<image:image><image:loc>';
		$xml.='modele-ad9bis.pl/'.$newSite['id'].'-'.$singIm['id_image'].'/'.$newSite['link'].'.jpg';
		$title=$siteMap->method4($singIm['id_image']);
		if($title!=null){
			$xml.='</image:loc><image:caption>'.$title.'</image:caption><image:title>'.$title.'</image:title></image:image>';
		}else{
		$xml.='</image:loc><image:caption/><image:title/></image:image>';
		}	
	}
	$xml.='</url>';
}
foreach($siteCategory as $newSiteCategory){
	$xml.='<url><loc>'.$newSiteCategory['catAdress'].'</loc><priority>0.8</priority><lastmod>'.$newSiteCategory['date_upd'].'</lastmod><changefreq>weekly</changefreq></url>';
}
$xml.='<url><loc>http://modele-ad9bis.pl/content/1-wysylka</loc><priority>0.8</priority><changefreq>weekly</changefreq></url><url><loc>http://modele-ad9bis.pl/content/3-regulamin-sklepu</loc><priority>0.8</priority><changefreq>weekly</changefreq></url><url><loc>http://modele-ad9bis.pl/content/4-international-shipment</loc><priority>0.8</priority><changefreq>weekly</changefreq></url><url><loc>http://modele-ad9bis.pl/dostawcy</loc><priority>0.5</priority><changefreq>monthly</changefreq></url><url><loc>http://modele-ad9bis.pl/producenci</loc><priority>0.5</priority><changefreq>monthly</changefreq></url><url><loc>http://modele-ad9bis.pl/nowe-produkty</loc><priority>0.5</priority><changefreq>monthly</changefreq></url><url><loc>http://modele-ad9bis.pl/promocje</loc><priority>0.5</priority><changefreq>monthly</changefreq></url><url><loc>http://modele-ad9bis.pl/sklepy</loc><priority>0.5</priority><changefreq>monthly</changefreq></url><url><loc>http://modele-ad9bis.pl/logowanie</loc><priority>0.5</priority><changefreq>monthly</changefreq></url><url><loc>http://modele-ad9bis.pl/najczesciej-kupowane</loc><priority>0.5</priority><changefreq>monthly</changefreq></url><url><loc>http://modele-ad9bis.pl/index.php?controller=contact-form</loc><priority>0.5</priority><changefreq>monthly</changefreq></url></urlset>';
$filePath = fopen($root_dir.'/sitemap.xml', 'w');
fputs($filePath, $xml);
fclose($filePath);
htmlout('Operacja zakończona powodzeniem!'); 
?>
<p>
<a href="http://localhost/Ad9bisCMS/sitemap.xml">Podgląd aktualnej strony XML</a>
</p>
<p>
<a href="http://localhost/Ad9bisCMS/index.php">Przejście do strony głównej CMS</a>
</p>
</body>
