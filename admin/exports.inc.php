<?
session_start();

require_once("../../../tools/produits.php");
require_once("../../../tools/categories.php");
require_once("../../include/fonctions.inc.php");
//set_time_limit(180); 

 


function ExportGoogleDetail($chemin,$nameFile,$ext, $typeSolde, $langue = 1, $devise, $tauxConv){
	
	
	$_SESSION['id_langue'] =$langue;
	/*
		Type Promo : 
		1: Promo
		2: soldes flottants
		3: soldes
	*/
	
	///attributs 
	/*
	
	identifiant [id] : indique l'identifiant de l'article
	titre [title] : indique le titre de l'article
	descriptif [description] : indique la description du produit
	catégorie de produits Google [google_product_category] : indique la catégorie Google de l'article
	catégorie [product_type] : indique la catégorie de l'article
	lien [link] : URL pointant directement vers la page de votre produit sur votre site Web
	lien image [image_link] : indique l'URL d'une image de l'article
	lien image supplémentaire [additional_image_link] : indique les URL supplémentaires des images de l'article
	état [condition] : condition ou état de l'article
	disponibilité [availability] : indique la disponibilité de l'article
	prix [price] : prix de l'article
	prix soldé [sale_price] : prix soldé annoncé de l'article
	période de validité du prix soldé [sale_price_effective_date] : indique la plage de dates au cours de laquelle l'article est soldé
	
	marque [brand] : marque de l'article
	ean [gtin] : code d'article international GTIN (Global Trade Identification Number) de l'article   --> Non obligatoire si MARQUE
	référence fabricant [mpn] : indique la référence fabricant de l'article --> Non obligatoire si MARQUE
	
	identifiant groupe [item_group_id] : indique l'identifiant partagé pour toutes les variantes d'un même produit ---> REF PRODUIT ==> MODIFIER LES TITRES PROD AVEC LES VARIANTES
	couleur [color] : indique la couleur de l'article
	matière [material] : indique la matière de l'article
	motif [pattern] : indique le motif/l'imprimé de l'article
	taille [size] : indique la taille de l'article
	
	
	==> SPECIFIQUE A l'HABILLEMENT
	sexe [gender] : indique le genre de l'article
	tranche d'âge [age_group] : indique la tranche d'âge cible de l'article
	couleur [color] : indique la couleur de l'article
	taille [size] : indique la taille de l'article
	
	
	TVA [tax] ==> UNIQUEMENT POUR LES US
	livraison [shipping]
	poids du colis [shipping_weight] : indique le poids de l'article pour les frais de port
	
	*/
	
	$strSqlInfoBoutique = "select * from boutique_param order by id_boutique_param desc limit 0,1";
	$resultInfoBoutique = mysql_query($strSqlInfoBoutique) or die ("Erreur de lecture des infos de la boutique");
	if($num= mysql_num_rows($resultInfoBoutique) > 0){
		$rowInfoBoutique = mysql_fetch_array($resultInfoBoutique);
	}else{
		return false;
	}
	
	
	
	$Fnm = $chemin.$nameFile.$ext;
	$Flux = fopen($Fnm,"w+");
 	
	/// entete   
 	$FluxStr .= '<?xml version="1.0"?>' ;
	$FluxStr .= '<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">' ;
	/// ouverture du flux   
	$FluxStr .= '<channel>' ;
	
	//description boutique
	
	if($langue == 1){
		$FluxStr .= '<title><![CDATA['.utf8_encode($rowInfoBoutique['nom_boutique_fr'].' - '.$rowInfoBoutique['detail_boutique_fr']).']]></title>' ;
		$FluxStr .= '<link><![CDATA[http://www.couteau-laguiole.com/fr/]]></link>' ;
		$FluxStr .= '<description><![CDATA['.utf8_encode($rowInfoBoutique['desc_boutique_fr']).']]></description>' ;
	}else{
		$FluxStr .= '<title><![CDATA[Laguiole French Knives]]></title>' ;
		$FluxStr .= '<link><![CDATA[http://www.couteau-laguiole.com/en/]]></link>' ;
		$FluxStr .= '<description><![CDATA['.utf8_encode($rowInfoBoutique['desc_boutique_en']).']]></description>' ;	
	}


	
	/// lecture des categories
	$strSqlCat = "select * from catal_categorie where suppr=0 and publier =1 order by ordre asc";
	$resultCat = mysql_query($strSqlCat) or die ("Erreur de lecture des catégories publiées");
	if($num = mysql_num_rows($resultCat) > 0){
		$tabProduitList = array();
		$tabExportProd = array();
		while($rowCat = mysql_fetch_array($resultCat)){
			
			$ariane ="";
			$categorie1 ="";
			$categorie2 ="";
			$categorie3 ="";
			///arriane catégorie
			if(!empty($rowCat['id_pere_n1'])){
				$TabN1 = LinkCategorie($rowCat['id_pere_n1']);			 
				if($langue==1){
					$ariane .= stripslashes($TabN1['libelle_fr'])."   &gt;  "   ;
					$categorie1 .= stripslashes($TabN1['libelle_fr']);
				}else{
					$ariane .= stripslashes($TabN1['libelle_en'])."  &gt;  " ;
					$categorie1 .= stripslashes($TabN1['libelle_en']);
				}
			}
			 
           
			if(!empty($rowCat['id_pere_n2'])){
				$TabN2 = LinkCategorie($rowCat['id_pere_n2']);		 
       		 	if($langue==1){
					$ariane .= stripslashes($TabN2['libelle_fr'])."  &gt;  "   ;
					$categorie2 .= stripslashes($TabN2['libelle_fr']);
				}else{
					$ariane .= stripslashes($TabN2['libelle_en'])."  &gt;  " ;
					$categorie2 .= stripslashes($TabN2['libelle_en']);
				}
             }
				 
                
             
			if(!empty($rowCat['id_pere_n3'])){
				$TabN3 = LinkCategorie($rowCat['id_pere_n3']);			 
				//$ariane .= ($TabN3['libelle'])."  &gt;  "   ;
				if($langue==1){
					$ariane .= stripslashes($TabN3['libelle_fr'])."  &gt;  "   ;
					$categorie3 .= stripslashes($TabN3['libelle_fr']);
				}else{
					$ariane .= stripslashes($TabN3['libelle_en'])."  &gt;  " ;
					$categorie3 .= stripslashes($TabN3['libelle_en']);
				}
				
			} 
			
			
			
			if($langue==1){
				$ariane .=  ($rowCat['libelle_fr']);
				$categorie2 .=  ($rowCat['libelle_fr']);
			}else{
				$ariane .=  ($rowCat['libelle_en']);
				$categorie2 .=  ($rowCat['libelle_en']);
			}
			
			
			$ariane = str_replace("€","euro",$ariane);
			$categorie1 = str_replace("€","euro",$categorie1);
			$categorie2 = str_replace("€","euro",$categorie2);
			$categorie3 = str_replace("€","euro",$categorie3);
	
			
			$tabProduitCat = ListingCatalogue($rowCat['id_categorie']);
			
			
			
			for($i= 0; $i < count($tabProduitCat['id_produit']); $i++){
				
				
					if(!IsInArray($tabExportProd,$tabProduitCat['id_produit'][$i])){
						
							
							/// lecture marques produit
							/*
							$strSqlMark = "select catal_produit.id_marque, marques.* from catal_produit, marques ";
							$strSqlMark .= " where catal_produit.id_produit = ".$tabProduitCat['id_produit'][$i]." and catal_produit.id_marque = marques.id_marque";
							$resultMark = mysql_query($strSqlMark) or die ("Erreur lecture info mark : ".$strSqlMark."<hr>".mysql_error());
							$rowMark = mysql_fetch_array($resultMark);
					 		*/
					 
					 		
							$strSqLLibCatCurr = "select libelle_fr,libelle_en from catal_categorie where id_categorie = ".$tabProduitCat['id_categorie'][$i];
							$resultLibCatCurr = mysql_query($strSqLLibCatCurr) or die ("Erreur de lecutre de cat curr");
							$rowLibCatCurr = mysql_fetch_array($resultLibCatCurr);
					 
					 
					 
							$MainPhotoProduit = MainPhotoProduit($tabProduitCat['id_produit'][$i],true);
							$PrixProduit = aPartirDe($tabProduitCat['id_produit'][$i]);
							$lienProduit = strtolower(UrlRewriter($tabProduitCat['libelle'][$i]))."-0-".$tabProduitCat['id_produit'][$i]."-".$tabProduitCat['id_categorie'][$i]."-".$tabProduitCat['n1'][$i]."-".$tabProduitCat['n1'][$i]."-".$tabProduitCat['n2'][$i]."-".$tabProduitCat['n3'][$i]."-".$devise.".html";
							$lienProduit  = str_replace("--","-",$lienProduit);
							
							
							/// lecture des modele existants
							
							$strSqlSpec = "select * from catal_produit_spec where id_produit = ".$tabProduitCat['id_produit'][$i]." and nb_pieces > 0 and suppr =0 and publier = 1";
							$resultSpec = mysql_query($strSqlSpec) or die ("Erreur de lecture des spec produits : ".$strSqlSpec."<hr>".mysql_error());
							
							if($num= mysql_num_rows($resultSpec) > 0){
								
								while($rowSpec = mysql_fetch_array($resultSpec)){
									
										
										$critereLib = "";
										
										/// lecture des criteres
										if($rowSpec['id_critere1'] != 0 ){
											$strSqlLibC1 = "select libelle_fr,libelle_en from catal_critere   where id_critere = ".$rowSpec['id_critere1'];
											$resultLibC1 = mysql_query($strSqlLibC1) or die ("Erreur de lecture du lib C1 <hr>".mysql_error()."<hr>".$strSqlLibC1);
											$rowLibC1 = mysql_fetch_array($resultLibC1);
										 
											
											$strSqlValC1 = "select libelle_fr,libelle_en from catal_critere_val   where id_critere_val = ".$rowSpec['id_critere1_val'];
											$resultValC1 = mysql_query($strSqlValC1) or die ("Erreur de lecture du val C1 <hr>".mysql_error()."<hr>".$strSqlValC1);
											$rowValC1 = mysql_fetch_array($resultValC1);
											
											if($langue==1){
												$critereLib .= " | ".$rowValC1['libelle_fr'];
											}else{
												$critereLib .= " | ".$rowValC1['libelle_en'];
											}
										}
										
										
										if($rowSpec['id_critere2'] != 0 ){
											$strSqlLibC2 = "select libelle_fr,libelle_en from catal_critere   where id_critere = ".$rowSpec['id_critere2'];
											$resultLibC2 = mysql_query($strSqlLibC2) or die ("Erreur de lecture du lib C2 <hr>".mysql_error()."<hr>".$strSqlLibC2);
											$rowLibC2 = mysql_fetch_array($resultLibC2);
										 
											
											$strSqlValC2 = "select libelle_fr ,libelle_en from catal_critere_val   where id_critere_val = ".$rowSpec['id_critere2_val'];
											$resultValC2 = mysql_query($strSqlValC2) or die ("Erreur de lecture du val C2 <hr>".mysql_error()."<hr>".$strSqlValC2);
											$rowValC2 = mysql_fetch_array($resultValC2);
											
											if($langue==1){
												$critereLib .= " | ".$rowValC2['libelle_fr'];
											}else{
												$critereLib .= " | ".$rowValC2['libelle_en'];
											}
										}
										
										
										if($rowSpec['id_critere3'] != 0 ){
											$strSqlLibC3 = "select libelle_fr,libelle_en from catal_critere   where id_critere = ".$rowSpec['id_critere3'];
											$resultLibC3 = mysql_query($strSqlLibC3) or die ("Erreur de lecture du lib C3 <hr>".mysql_error()."<hr>".$strSqlLibC3);
											$rowLibC3 = mysql_fetch_array($resultLibC3);
										 
											
											$strSqlValC3 = "select libelle_fr,libelle_en from catal_critere_val   where id_critere_val = ".$rowSpec['id_critere3_val'];
											$resultValC3 = mysql_query($strSqlValC3) or die ("Erreur de lecture du val C3 <hr>".mysql_error()."<hr>".$strSqlValC3);
											$rowValC3 = mysql_fetch_array($resultValC3);
											
											if($langue==1){
												$critereLib .= " | ".$rowValC3['libelle_fr'];
											}else{
												$critereLib .= " | ".$rowValC3['libelle_en'];
											}
										}
										
									
									
 							
										///items avec spec
										if($langue ==1){
											$rep = "fr";	
										}else{
											$rep = "en";	
										}
										
											$FluxStr .= '<item>' ;
											$FluxStr .= '<title><![CDATA['.utf8_encode(NoAccent($tabProduitCat['libelle'][$i]).$critereLib).']]></title>' ;
											
											$FluxStr .= '<link><![CDATA['.utf8_encode($rowInfoBoutique['url_boutique']."/".$rep."/".$lienProduit).']]></link>' ;
											$FluxStr .= '<description><![CDATA['.utf8_encode(strip_tags($tabProduitCat['descriptif'][$i])).']]></description>' ;
											$FluxStr .= '<g:id><![CDATA['.utf8_encode( strtoupper("LAG-".$tabProduitCat['id_produit'][$i]."-".$rowSpec['id_produit_spec'])) .']]></g:id>' ;
											//$FluxStr .= '<g:id><![CDATA['.utf8_encode( strtoupper("LAG-".$tabProduitCat['id_produit'][$i])) .']]></g:id>' ;
											
											
											 
											
											$FluxStr .= '<g:product_type><![CDATA['.utf8_encode(NoAccent($ariane )).']]></g:product_type>' ;
											 
											
											$FluxStr .= '<g:condition>new</g:condition>' ;
											$FluxStr .= '<g:gtin><![CDATA['.utf8_encode(($tabProduitCat['ean'][$i])).']]></g:gtin>';
											
											
											
											//$tabPrix = aPartirDe($tabProduitCat['id_produit'][$i]);
											
											//number_format(TVA($rowSpec['prix_ht']*$tauxConv),2)
											if($rowSpec['is_promo'] == 1){
												$FluxStr .= '<g:price>'.number_format(TVA($rowSpec['prix_promo']*$tauxConv),2).'</g:price>' ;
												//$FluxStr .= '<g:price>'.number_format(TVA($tabPrix['a_partir_de'][0]*$tauxConv),2).'</g:price>' ;
											}else{
												$FluxStr .= '<g:price>'.number_format(TVA($rowSpec['prix_ht']*$tauxConv),2).'</g:price>' ;
											}
											
											if($PrixProduit['ispromo'][0])  {
												switch($typeSolde){
													case 1:
														//promo std
														$FluxStr .= '<g:sale_price>'.number_format(TVA($rowSpec['prix_ht']*$tauxConv),2).'</g:sale_price>' ;
														//$FluxStr .= '<g:sale_price>'.number_format(TVA($tabPrix['a_partir_de'][0]*$tauxConv),2).'</g:sale_price>' ;
														break;
													case 2:
														//soldes flottants
														$FluxStr .= '<g:sale_price>'.number_format(TVA($rowSpec['prix_ht']*$tauxConv),2).'</g:sale_price>' ;
														break;
													case 3:
														//soldes
														$FluxStr .= '<g:sale_price>'.number_format(TVA($rowSpec['prix_ht']*$tauxConv),2).'</g:sale_price>' ;
														break;
												}
											}
											
											$FluxStr .= '<g:availability>in stock</g:availability>' ;
											$FluxStr .= '<g:quantity>200</g:quantity>' ;
											
											
											if($rowSpec['id_photo_produit'] != 0){
												
												$strSqlPhoto = "select img_list from catal_photo_produit where id_photo_produit = ".$rowSpec['id_photo_produit'];
												$resultPhoto = mysql_query($strSqlPhoto) or die ("Erreur de lecture de la photo specifique ");
												$rowPhoto = mysql_fetch_array($resultPhoto);
												if(is_file("../../../produit/".$rowPhoto['img_list'])){
													$FluxStr .= '<g:image_link>'.$rowInfoBoutique['url_boutique']."/produit/".$rowPhoto['img_list'].'</g:image_link>' ;
												}else{
													$FluxStr .= '<g:image_link>'.$rowInfoBoutique['url_boutique']."/produit/".$MainPhotoProduit['img_list'][0].'</g:image_link>' ;
												}
											}else{
												$FluxStr .= '<g:image_link>'.$rowInfoBoutique['url_boutique']."/produit/".$MainPhotoProduit['img_list'][0].'</g:image_link>' ;
											}
											
											 
											
											$FluxStr .= '<g:shipping>' ;
												$FluxStr .= '<g:country>FR</g:country>' ;
												$FluxStr .= '<g:service>Standard</g:service>' ;
												$FluxStr .= '<g:price>5</g:price>' ;
											$FluxStr .= '</g:shipping>' ;
											$FluxStr .= '<g:brand><![CDATA['.utf8_encode(NoAccent("Laguiole Actiforge")).']]></g:brand>' ;
											$FluxStr .= '<g:marque_niveau_2><![CDATA['.utf8_encode(NoAccent($categorie1)).']]></g:marque_niveau_2>' ;
											$FluxStr .= '<g:categorie><![CDATA['.utf8_encode(NoAccent($categorie2)).']]></g:categorie>' ;
																						
											if($langue==1){
												
											 	$FluxStr .= '<g:mpn><![CDATA['.utf8_encode( substr(strtoupper($rowInfoBoutique['nom_boutique_fr']),0,1).$tabProduitCat['id_produit'][$i].$rowSpec['id_produit_spec']).date("Y").']]></g:mpn>';
											}else{
												$FluxStr .= '<g:mpn><![CDATA['.utf8_encode( substr(strtoupper('Laguiole French Knives'),0,1).$tabProduitCat['id_produit'][$i].$rowSpec['id_produit_spec']).date("Y").']]></g:mpn>';	
											}


											$FluxStr .= '<g:devise>'.$devise.'</g:devise>' ;
											
											
											$FluxStr .= '</item>' ;
							
						 		}
						 	}
							
							
							array_push($tabExportProd,$tabProduitCat['id_produit'][$i]);
					}
			}
		
		}
			
	}
	
	
	
	// Ajout du produit personnalisé
	
	
	$prixPerso = number_format(TVA(31.33*$tauxConv),2); // a partir de 37€
	$imgPerso = "http://www.couteau-laguiole.com/produit/pfi2721-couteau-laguiole-pliant-manche-en-corne-blonde-mitres-en-laiton-12-cm-155426.jpg";
	
	if($langue ==1){
		$titrePerso = "Couteau Laguiole personnalisé ";
		$lienPerso  = "http://www.couteau-laguiole.com/fr/catalogue-laguiole-personnalise-3-3-0-0-0-0.html";
		$DescPerso ="La rubrique Laguiole personnalisé vous permet de fabriquer votre couteau Laguiole selon vos envies. Vous pouvez rendre votre couteau Laguiole unique en demandant une gravure sur la lame et/ou une gravure sur le ressort. Les lettres sont gravées à la main dans l'acier, puis le ressort gravé est trempé pour le durcir. Nous assemblons ensuite les éléments du couteau selon votre choix. Délai de fabrication : 15 jours";
		$catPerso ="Laguiole pliants";
	}else{
		$titrePerso = "Customized Laguiole knife";
		$lienPerso  = "http://www.couteau-laguiole.com/en/catalogue-customized-laguiole-3-3-0-0-0-0.html";
		$DescPerso ="You can build and personalize your own Laguiole knife according to your wishes. For an even more personal touch, you can have your knife engraved with your name. The different elements you have chosen for your knife are then assembled. Making allows 15 days.";
		$catPerso ="Laguiole pocket knives ";
	}
	
	
	
	$produitPerso ="<item><title><![CDATA[".(strip_tags($titrePerso))."]]></title><link><![CDATA[".$lienPerso."]]></link><description><![CDATA[".(strip_tags($DescPerso)) ."]]></description><g:id><![CDATA[LAG-PERSO]]></g:id><g:product_type><![CDATA[".(strip_tags($catPerso)) ."]]></g:product_type><g:condition>new</g:condition><g:gtin><![CDATA[3700737705786]]></g:gtin><g:price>".$prixPerso."</g:price><g:availability>in stock</g:availability><g:quantity>200</g:quantity><g:image_link>".$imgPerso."</g:image_link><g:shipping><g:country>FR</g:country><g:service>Standard</g:service><g:price>5</g:price></g:shipping><g:brand><![CDATA[Laguiole Actiforge]]></g:brand><g:mpn><![CDATA[3700737705786]]></g:mpn><g:devise>".$devise."</g:devise></item>";
	
	$FluxStr .= $produitPerso;
	
	/// fermeture du flux
	$FluxStr .= '</channel>' ;
	$FluxStr .= '</rss>' ;
	
	fputs($Flux,$FluxStr);
	
	fclose($Flux);

	return $nameFile.$ext;
	
}

 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 function ExportBeezupDetail($chemin,$nameFile,$ext, $typeSolde, $langue = 1, $devise, $tauxConv){
	
	$_SESSION['id_langue'] =$langue;
	/*
		Type Promo : 
		1: Promo
		2: soldes flottants
		3: soldes
	*/
	
	///attributs 
	/*
	
	identifiant [id] : indique l'identifiant de l'article
	titre [title] : indique le titre de l'article
	descriptif [description] : indique la description du produit
	catégorie de produits Google [google_product_category] : indique la catégorie Google de l'article
	catégorie [product_type] : indique la catégorie de l'article
	lien [link] : URL pointant directement vers la page de votre produit sur votre site Web
	lien image [image_link] : indique l'URL d'une image de l'article
	lien image supplémentaire [additional_image_link] : indique les URL supplémentaires des images de l'article
	état [condition] : condition ou état de l'article
	disponibilité [availability] : indique la disponibilité de l'article
	prix [price] : prix de l'article
	prix soldé [sale_price] : prix soldé annoncé de l'article
	période de validité du prix soldé [sale_price_effective_date] : indique la plage de dates au cours de laquelle l'article est soldé
	
	marque [brand] : marque de l'article
	ean [gtin] : code d'article international GTIN (Global Trade Identification Number) de l'article   --> Non obligatoire si MARQUE
	référence fabricant [mpn] : indique la référence fabricant de l'article --> Non obligatoire si MARQUE
	
	identifiant groupe [item_group_id] : indique l'identifiant partagé pour toutes les variantes d'un même produit ---> REF PRODUIT ==> MODIFIER LES TITRES PROD AVEC LES VARIANTES
	couleur [color] : indique la couleur de l'article
	matière [material] : indique la matière de l'article
	motif [pattern] : indique le motif/l'imprimé de l'article
	taille [size] : indique la taille de l'article
	
	
	==> SPECIFIQUE A l'HABILLEMENT
	sexe [gender] : indique le genre de l'article
	tranche d'âge [age_group] : indique la tranche d'âge cible de l'article
	couleur [color] : indique la couleur de l'article
	taille [size] : indique la taille de l'article
	
	
	TVA [tax] ==> UNIQUEMENT POUR LES US
	livraison [shipping]
	poids du colis [shipping_weight] : indique le poids de l'article pour les frais de port
	
	*/
	
	$strSqlInfoBoutique = "select * from boutique_param order by id_boutique_param desc limit 0,1";
	$resultInfoBoutique = mysql_query($strSqlInfoBoutique) or die ("Erreur de lecture des infos de la boutique");
	if($num= mysql_num_rows($resultInfoBoutique) > 0){
		$rowInfoBoutique = mysql_fetch_array($resultInfoBoutique);
	}else{
		return false;
	}
	
	
	
	$Fnm = $chemin.$nameFile.$ext;
	$Flux = fopen($Fnm,"w+");
 	
	/// entete   
 	$FluxStr .= '<?xml version="1.0" encoding="UTF-8"?>' ;
	
	if($langue ==1){
		
		$FluxStr .= '<catalogue lang="FR">' ;
	}else{
		$FluxStr .= '<catalogue lang="EN">' ;
	}
	
	 


	
	/// lecture des categories
	$strSqlCat = "select * from catal_categorie where suppr=0 and publier =1 order by ordre asc";
	$resultCat = mysql_query($strSqlCat) or die ("Erreur de lecture des catégories publiées");
	if($num = mysql_num_rows($resultCat) > 0){
		$tabProduitList = array();
		$tabExportProd = array();
		while($rowCat = mysql_fetch_array($resultCat)){
			
			$ariane ="";
			///arriane catégorie
			if(!empty($rowCat['id_pere_n1'])){
				$TabN1 = LinkCategorie($rowCat['id_pere_n1']);	
 				$ariane .= ($TabN1['libelle'])."  > "  ;
			}
			 
           
			if(!empty($rowCat['id_pere_n2'])){
				$TabN2 = LinkCategorie($rowCat['id_pere_n2']);		 
       		 	$ariane .= ($TabN2['libelle'])."  >  "  ;
             }
				 
                
             
			if(!empty($rowCat['id_pere_n3'])){
				$TabN3 = LinkCategorie($rowCat['id_pere_n3']);			 
				$ariane .= ($TabN3['libelle'])."  >  "   ;
			} 
			
			if($langue ==1){
				$ariane .= stripslashes($rowCat['libelle_fr']);
			}else{
				$ariane .= stripslashes($rowCat['libelle_en']);
			}
			$ariane = str_replace("€","euro",$ariane);
	
			
			$tabProduitCat = ListingCatalogue($rowCat['id_categorie']);
			
			
			
			for($i= 0; $i < count($tabProduitCat['id_produit']); $i++){
				
				
					if(!IsInArray($tabExportProd,$tabProduitCat['id_produit'][$i])){
						
							
							/// lecture marques produit
							/*
							$strSqlMark = "select catal_produit.id_marque, marques.* from catal_produit, marques ";
							$strSqlMark .= " where catal_produit.id_produit = ".$tabProduitCat['id_produit'][$i]." and catal_produit.id_marque = marques.id_marque";
							$resultMark = mysql_query($strSqlMark) or die ("Erreur lecture info mark : ".$strSqlMark."<hr>".mysql_error());
							$rowMark = mysql_fetch_array($resultMark);
					 		*/
					 
					 		
							
					 		$strSqLLibCatCurr = "select libelle_fr,libelle_en from catal_categorie where id_categorie = ".$tabProduitCat['id_categorie'][$i];
							$resultLibCatCurr = mysql_query($strSqLLibCatCurr) or die ("Erreur de lecutre de cat curr");
							$rowLibCatCurr = mysql_fetch_array($resultLibCatCurr);
					 
					 
							$MainPhotoProduit = MainPhotoProduit($tabProduitCat['id_produit'][$i],true);
							$PrixProduit = aPartirDe($tabProduitCat['id_produit'][$i]);
							
							$lienProduit = strtolower(UrlRewriter($tabProduitCat['libelle'][$i]))."-0-".$tabProduitCat['id_produit'][$i]."-".$tabProduitCat['id_categorie'][$i]."-".$tabProduitCat['n1'][$i]."-".$tabProduitCat['n1'][$i]."-".$tabProduitCat['n2'][$i]."-".$tabProduitCat['n3'][$i].".html";
							$lienProduit  = str_replace("--","-",$lienProduit);
							
							
							/// lecture des modele existants
							$strSqlSpec = "select * from catal_produit_spec where id_produit = ".$tabProduitCat['id_produit'][$i]." and nb_pieces > 0 and suppr =0 and publier = 1";
							$resultSpec = mysql_query($strSqlSpec) or die ("Erreur de lecture des spec produits : ".$strSqlSpec."<hr>".mysql_error());
							
							if($num= mysql_num_rows($resultSpec) > 0){
								
								while($rowSpec = mysql_fetch_array($resultSpec)){
									
										
										$critereLib = "";
										
										/// lecture des criteres
										if($rowSpec['id_critere1'] != 0 ){
											$strSqlLibC1 = "select libelle_fr,libelle_en from catal_critere   where id_critere = ".$rowSpec['id_critere1'];
											$resultLibC1 = mysql_query($strSqlLibC1) or die ("Erreur de lecture du lib C1 <hr>".mysql_error()."<hr>".$strSqlLibC1);
											$rowLibC1 = mysql_fetch_array($resultLibC1);
										 
											
											$strSqlValC1 = "select libelle_fr,libelle_en from catal_critere_val   where id_critere_val = ".$rowSpec['id_critere1_val'];
											$resultValC1 = mysql_query($strSqlValC1) or die ("Erreur de lecture du val C1 <hr>".mysql_error()."<hr>".$strSqlValC1);
											$rowValC1 = mysql_fetch_array($resultValC1);
											
											if($langue==1){
												$critereLib .= " | ".$rowValC1['libelle_fr'];
											}else{
												$critereLib .= " | ".$rowValC1['libelle_en'];
											}
										}
										
										
										if($rowSpec['id_critere2'] != 0 ){
											$strSqlLibC2 = "select libelle_fr,libelle_en from catal_critere   where id_critere = ".$rowSpec['id_critere2'];
											$resultLibC2 = mysql_query($strSqlLibC2) or die ("Erreur de lecture du lib C2 <hr>".mysql_error()."<hr>".$strSqlLibC2);
											$rowLibC2 = mysql_fetch_array($resultLibC2);
										 
											
											$strSqlValC2 = "select libelle_fr ,libelle_en from catal_critere_val   where id_critere_val = ".$rowSpec['id_critere2_val'];
											$resultValC2 = mysql_query($strSqlValC2) or die ("Erreur de lecture du val C2 <hr>".mysql_error()."<hr>".$strSqlValC2);
											$rowValC2 = mysql_fetch_array($resultValC2);
											
											if($langue==1){
												$critereLib .= " | ".$rowValC2['libelle_fr'];
											}else{
												$critereLib .= " | ".$rowValC2['libelle_en'];
											}
										}
										
										
										if($rowSpec['id_critere3'] != 0 ){
											$strSqlLibC3 = "select libelle_fr,libelle_en from catal_critere   where id_critere = ".$rowSpec['id_critere3'];
											$resultLibC3 = mysql_query($strSqlLibC3) or die ("Erreur de lecture du lib C3 <hr>".mysql_error()."<hr>".$strSqlLibC3);
											$rowLibC3 = mysql_fetch_array($resultLibC3);
										 
											
											$strSqlValC3 = "select libelle_fr,libelle_en from catal_critere_val   where id_critere_val = ".$rowSpec['id_critere3_val'];
											$resultValC3 = mysql_query($strSqlValC3) or die ("Erreur de lecture du val C3 <hr>".mysql_error()."<hr>".$strSqlValC3);
											$rowValC3 = mysql_fetch_array($resultValC3);
											
											if($langue==1){
												$critereLib .= " | ".$rowValC3['libelle_fr'];
											}else{
												$critereLib .= " | ".$rowValC3['libelle_en'];
											}
										}
										
										
									
 							
										///items avec spec
										if($langue ==1){
											$rep = "fr";	
										}else{
											$rep = "en";	
										}
										
											$FluxStr .= '<product>' ;
											$FluxStr .= '<titre><![CDATA['.utf8_encode(NoAccent($tabProduitCat['libelle'][$i]).$critereLib).']]></titre>' ;
											
											if($langue == 1){
												$FluxStr .= '<categorie><![CDATA['.utf8_encode(NoAccent($ariane." > ".$rowLibCatCurr['libelle_fr'])).']]></categorie>';
											}else{
												$FluxStr .= '<categorie><![CDATA['.utf8_encode(NoAccent($ariane." > ".$rowLibCatCurr['libelle_en'])).']]></categorie>';
											}
											$FluxStr .= '<identifiant_unique><![CDATA[LAG'.$tabProduitCat['id_produit'][$i]."-".$rowSpec['id_produit_spec'].']]></identifiant_unique>';
											$FluxStr .= '<ean><![CDATA['.utf8_encode(($tabProduitCat['ean'][$i])).']]></ean>';
											
											if($rowSpec['is_promo'] == 1){
												$FluxStr .= '<prix currency="'.$devise.'">'.number_format(TVA($rowSpec['prix_promo']*$tauxConv),2).'</prix>' ;
												$FluxStr .= '<prixBarre currency="'.$devise.'">'.number_format(TVA($rowSpec['prix_ht']*$tauxConv),2).'</prixBarre>';
											}else{
												$FluxStr .= '<prix currency="'.$devise.'">'.number_format(TVA($rowSpec['prix_ht']*$tauxConv),2).'</prix>' ;
											}
											
											$FluxStr .= '<url_produit><![CDATA['.utf8_encode($rowInfoBoutique['url_boutique']."/".$rep."/".$lienProduit).']]></url_produit>';
										 	
											
										 
										 
									 
											
											if($rowSpec['id_photo_produit'] != 0){
												
												$strSqlPhoto = "select img_list from catal_photo_produit where id_photo_produit = ".$rowSpec['id_photo_produit'];
												$resultPhoto = mysql_query($strSqlPhoto) or die ("Erreur de lecture de la photo specifique ");
												$rowPhoto = mysql_fetch_array($resultPhoto);
												if(is_file("../../../produit/".$rowPhoto['img_list'])){
													 
													$FluxStr .= '<url_image><![CDATA['.$rowInfoBoutique['url_boutique']."/produit/".$rowPhoto['img_list'].']]></url_image>';
												}else{
													 
													$FluxStr .= '<url_image><![CDATA['.$rowInfoBoutique['url_boutique']."/produit/".$MainPhotoProduit['img_list'][0].']]></url_image>';
												}
											}else{
												$FluxStr .= '<url_image><![CDATA['.$rowInfoBoutique['url_boutique']."/produit/".$MainPhotoProduit['img_list'][0].']]></url_image>';
											}
											$FluxStr .= '<description><![CDATA['.utf8_encode(strip_tags($tabProduitCat['descriptif'][$i])).']]></description>' ;
											
											$FluxStr .= '<disponibilite>En Stock</disponibilite>';
											$FluxStr .= '<delai_de_livraison>2-3</delai_de_livraison>';
											$FluxStr .= '<frais_de_port>0</frais_de_port>';
											$FluxStr .= '<marque><![CDATA[LAGUIOLE]]></marque>';
									 
											
											 
											
											$FluxStr .= '</product>' ;
							
								}
							}
							
							
							array_push($tabExportProd,$tabProduitCat['id_produit'][$i]);
					}
			}
		
		}
			
	}
	
	
	
	
	
	/// fermeture du flux
	$FluxStr .= '</catalogue>' ;
 
	
	fputs($Flux,$FluxStr);
	
	fclose($Flux);

	return $nameFile.$ext;
	
}

























 function ExportTrackFeeder($chemin,$nameFile,$ext, $typeSolde, $langue = 1, $devise, $tauxConv){
	
	$_SESSION['id_langue'] =$langue;
	 
	
	$strSqlInfoBoutique = "select * from boutique_param order by id_boutique_param desc limit 0,1";
	$resultInfoBoutique = mysql_query($strSqlInfoBoutique) or die ("Erreur de lecture des infos de la boutique");
	if($num= mysql_num_rows($resultInfoBoutique) > 0){
		$rowInfoBoutique = mysql_fetch_array($resultInfoBoutique);
	}else{
		return false;
	}
	
	
	
	$Fnm = $chemin.$nameFile.$ext;
	$Flux = fopen($Fnm,"w+");
 	
	/// entete   
	$FluxStr ="";
 	$FluxStr .= 'SKU; Categorie; Libelle; Description; Marque; Prix; Frais de port; Etat produit; Ecotaxe; Image; Lien; Actif; EAN; Promo; Dispo; Devise; Stock;' ;
	$FluxStr .= "\r\n";
	
	 
	 


	
	/// lecture des categories
	$strSqlCat = "select * from catal_categorie where suppr=0 and publier =1 order by ordre asc";
	$resultCat = mysql_query($strSqlCat) or die ("Erreur de lecture des catégories publiées");
	if($num = mysql_num_rows($resultCat) > 0){
		$tabProduitList = array();
		$tabExportProd = array();
		while($rowCat = mysql_fetch_array($resultCat)){
			
			$ariane ="";
			///arriane catégorie
			if(!empty($rowCat['id_pere_n1'])){
				$TabN1 = LinkCategorie($rowCat['id_pere_n1']);			 
				$ariane .= ($TabN1['libelle'])."  > "  ;
			}
			 
           
			if(!empty($rowCat['id_pere_n2'])){
				$TabN2 = LinkCategorie($rowCat['id_pere_n2']);		 
       		 	$ariane .= ($TabN2['libelle'])."  >  "  ;
             }
				 
                
             
			if(!empty($rowCat['id_pere_n3'])){
				$TabN3 = LinkCategorie($rowCat['id_pere_n3']);			 
				$ariane .= ($TabN3['libelle'])."  >  "   ;
			} 
			
			if($langue ==1){
				$ariane .= stripslashes($rowCat['libelle_fr']);
			}else{
				$ariane .= stripslashes($rowCat['libelle_en']);
			}
			$ariane = str_replace("€","euro",$ariane);
	
			
			$tabProduitCat = ListingCatalogue($rowCat['id_categorie']);
			
			
			
			for($i= 0; $i < count($tabProduitCat['id_produit']); $i++){
				
				
					if(!IsInArray($tabExportProd,$tabProduitCat['id_produit'][$i])){
						
							
							/// lecture marques produit
							/*
							$strSqlMark = "select catal_produit.id_marque, marques.* from catal_produit, marques ";
							$strSqlMark .= " where catal_produit.id_produit = ".$tabProduitCat['id_produit'][$i]." and catal_produit.id_marque = marques.id_marque";
							$resultMark = mysql_query($strSqlMark) or die ("Erreur lecture info mark : ".$strSqlMark."<hr>".mysql_error());
							$rowMark = mysql_fetch_array($resultMark);
					 		*/
					 
					 		
							
					 
					 
					 
							$MainPhotoProduit = MainPhotoProduit($tabProduitCat['id_produit'][$i],true);
							$PrixProduit = aPartirDe($tabProduitCat['id_produit'][$i]);
							
							$lienProduit = strtolower(UrlRewriter($tabProduitCat['libelle'][$i]))."-0-".$tabProduitCat['id_produit'][$i]."-".$tabProduitCat['id_categorie'][$i]."-".$tabProduitCat['n1'][$i]."-".$tabProduitCat['n1'][$i]."-".$tabProduitCat['n2'][$i]."-".$tabProduitCat['n3'][$i].".html";
							$lienProduit  = str_replace("--","-",$lienProduit);
							
							
							/// lecture des modele existants
							$strSqlSpec = "select * from catal_produit_spec where id_produit = ".$tabProduitCat['id_produit'][$i]." and nb_pieces > 0 and suppr =0 and publier = 1";
							$resultSpec = mysql_query($strSqlSpec) or die ("Erreur de lecture des spec produits : ".$strSqlSpec."<hr>".mysql_error());
							
							if($num= mysql_num_rows($resultSpec) > 0){
								
								while($rowSpec = mysql_fetch_array($resultSpec)){
									
										
										$critereLib = "";
										
										/// lecture des criteres
										if($rowSpec['id_critere1'] != 0 ){
											$strSqlLibC1 = "select libelle_fr,libelle_en from catal_critere   where id_critere = ".$rowSpec['id_critere1'];
											$resultLibC1 = mysql_query($strSqlLibC1) or die ("Erreur de lecture du lib C1 <hr>".mysql_error()."<hr>".$strSqlLibC1);
											$rowLibC1 = mysql_fetch_array($resultLibC1);
										 
											
											$strSqlValC1 = "select libelle_fr,libelle_en from catal_critere_val   where id_critere_val = ".$rowSpec['id_critere1_val'];
											$resultValC1 = mysql_query($strSqlValC1) or die ("Erreur de lecture du val C1 <hr>".mysql_error()."<hr>".$strSqlValC1);
											$rowValC1 = mysql_fetch_array($resultValC1);
											
											if($langue==1){
												$critereLib .= " | ".$rowValC1['libelle_fr'];
											}else{
												$critereLib .= " | ".$rowValC1['libelle_en'];
											}
										}
										
										
										if($rowSpec['id_critere2'] != 0 ){
											$strSqlLibC2 = "select libelle_fr,libelle_en from catal_critere   where id_critere = ".$rowSpec['id_critere2'];
											$resultLibC2 = mysql_query($strSqlLibC2) or die ("Erreur de lecture du lib C2 <hr>".mysql_error()."<hr>".$strSqlLibC2);
											$rowLibC2 = mysql_fetch_array($resultLibC2);
										 
											
											$strSqlValC2 = "select libelle_fr ,libelle_en from catal_critere_val   where id_critere_val = ".$rowSpec['id_critere2_val'];
											$resultValC2 = mysql_query($strSqlValC2) or die ("Erreur de lecture du val C2 <hr>".mysql_error()."<hr>".$strSqlValC2);
											$rowValC2 = mysql_fetch_array($resultValC2);
											
											if($langue==1){
												$critereLib .= " | ".$rowValC2['libelle_fr'];
											}else{
												$critereLib .= " | ".$rowValC2['libelle_en'];
											}
										}
										
										
										if($rowSpec['id_critere3'] != 0 ){
											$strSqlLibC3 = "select libelle_fr,libelle_en from catal_critere   where id_critere = ".$rowSpec['id_critere3'];
											$resultLibC3 = mysql_query($strSqlLibC3) or die ("Erreur de lecture du lib C3 <hr>".mysql_error()."<hr>".$strSqlLibC3);
											$rowLibC3 = mysql_fetch_array($resultLibC3);
										 
											
											$strSqlValC3 = "select libelle_fr,libelle_en from catal_critere_val   where id_critere_val = ".$rowSpec['id_critere3_val'];
											$resultValC3 = mysql_query($strSqlValC3) or die ("Erreur de lecture du val C3 <hr>".mysql_error()."<hr>".$strSqlValC3);
											$rowValC3 = mysql_fetch_array($resultValC3);
											
											if($langue==1){
												$critereLib .= " | ".$rowValC3['libelle_fr'];
											}else{
												$critereLib .= " | ".$rowValC3['libelle_en'];
											}
										}
										
										
									
 							
										///items avec spec
										if($langue ==1){
											$rep = "fr";	
										}else{
											$rep = "en";	
										}
										
										
										
											 
											$titreProd =utf8_encode(NoAccent($tabProduitCat['libelle'][$i]).$critereLib) ;
											$CatProd = utf8_encode(NoAccent($ariane));
											$SKUProd = 'LAG'.$tabProduitCat['id_produit'][$i]."-".$rowSpec['id_produit_spec'];
											$EANProd =  utf8_encode(($tabProduitCat['ean'][$i])) ;
											
											if($rowSpec['is_promo'] == 1){
												$PromoProd = 1;
												$DeviseProd = $devise;
												$PrixdProd =  number_format(TVA($rowSpec['prix_ht']*$tauxConv),2) ;
											}else{
												$PromoProd = 0;
												$DeviseProd = $devise;
												$PrixdProd =   number_format(TVA($rowSpec['prix_ht']*$tauxConv),2)  ;
											}
											
									 
										 	$lienProd = utf8_encode($rowInfoBoutique['url_boutique']."/".$rep."/".$lienProduit);
											
										 
										 
									 
											
											if($rowSpec['id_photo_produit'] != 0){
												
												$strSqlPhoto = "select img_list from catal_photo_produit where id_photo_produit = ".$rowSpec['id_photo_produit'];
												$resultPhoto = mysql_query($strSqlPhoto) or die ("Erreur de lecture de la photo specifique ");
												$rowPhoto = mysql_fetch_array($resultPhoto);
												if(is_file("../../../produit/".$rowPhoto['img_list'])){
													 
													$ImgProd =  $rowInfoBoutique['url_boutique']."/produit/".$rowPhoto['img_list'] ;
												}else{
													$ImgProd =   $rowInfoBoutique['url_boutique']."/produit/".$MainPhotoProduit['img_list'][0];
												}
											}else{
												$ImgProd =  $rowInfoBoutique['url_boutique']."/produit/".$MainPhotoProduit['img_list'][0] ;
											}
											
											$DescProd =  utf8_encode(strip_tags($tabProduitCat['descriptif'][$i])) ;
											$DescProd  = str_replace("\r\n"," ",$DescProd  );
											$DescProd  = html_entity_decode($DescProd,ENT_QUOTES);
											$DescProd  =str_replace("&rsquo;","'", 	$DescProd );
											$DescProd  =str_replace("&rdquo;","'", 	$DescProd );
											$DescProd  =str_replace("&ldquo;","'", 	$DescProd );
											$DescProd  =str_replace("&lsquo;","'", 	$DescProd );
											$DescProd  =str_replace("&ndash;","-", 	$DescProd );
											$DescProd  =str_replace(";",",", 	$DescProd );
											 
											
											$DescProd  =str_replace("&oelig;","oe", 	$DescProd );
											
																					
											$StockProd = 1;
											$DelaiProd = '2-3 jours';
											$FPProd = 0;
											$MarqueProd = 'LAGUIOLE';
									 
											
											 
											//$FluxStr .= 'SKU; Categorie; Libelle; Description courte; Marque; Prix; Frais de port; Etat produit; Ecotaxe; Image; Lien; Actif; EAN; Promo; Dispo; Devise; Stock; Poids;Delai\n' ;
										  $FluxStr .= $SKUProd.';'.$CatProd.';'.$titreProd.';'.str_replace("\r\n"," ",html_entity_decode($DescProd,ENT_QUOTES)).';'.$MarqueProd.';'.$PrixdProd.';'.$FPProd.';NEUF;0;'.$ImgProd.';'.$lienProd.';1;'.$EANProd.';'.$PromoProd.';1;'.$DeviseProd.';1000;' ;
										  $FluxStr .= "\r\n";
							
								}
							}
							
							
							array_push($tabExportProd,$tabProduitCat['id_produit'][$i]);
					}
			}
		
		}
			
	}
	
	
	
	
	
	/// fermeture du flux
 
 
	
	fputs($Flux,$FluxStr);
	
	fclose($Flux);

	return $nameFile.$ext;
	
}







 function ExportTrackFeederAll($chemin,$nameFile,$ext, $typeSolde ){
	
	
	
	$Fnm = $chemin.$nameFile.$ext;
	$Flux = fopen($Fnm,"w+");
 	
	/// entete   
	$FluxStr ="";
 	$FluxStr .= 'SKU; Categorie; Libelle; Description; Marque; Prix; Frais de port; Etat produit; Ecotaxe; Image; Lien; Actif; EAN; Promo; Dispo; Devise; Stock; Langue;' ;
	$FluxStr .= "\r\n";
	
	
	
	
	///$exportBeezupEURFR = ExportTrackFeeder("../../../export/","trackfeeder-shop-detail-fr-eur", ".csv",$_POST['type_promotion'], $_POST['id_langue'], 'EUR', 1);
	//********************************
	//EUR FR 1
	//********************************	
	
	
	
	$_SESSION['id_langue'] =1;
	$langue =1;
	$devise='EUR';
	$tauxConv = 1;
	$libLangue = "FR";
	
	$strSqlInfoBoutique = "select * from boutique_param order by id_boutique_param desc limit 0,1";
	$resultInfoBoutique = mysql_query($strSqlInfoBoutique) or die ("Erreur de lecture des infos de la boutique");
	if($num= mysql_num_rows($resultInfoBoutique) > 0){
		$rowInfoBoutique = mysql_fetch_array($resultInfoBoutique);
	}else{
		return false;
	}
	

	
	/// lecture des categories
	$strSqlCat = "select * from catal_categorie where suppr=0 and publier =1 order by ordre asc";
	$resultCat = mysql_query($strSqlCat) or die ("Erreur de lecture des catégories publiées");
	if($num = mysql_num_rows($resultCat) > 0){
		$tabProduitList = array();
		$tabExportProd = array();
		while($rowCat = mysql_fetch_array($resultCat)){
			
			$ariane ="";
			///arriane catégorie
			if(!empty($rowCat['id_pere_n1'])){
				$TabN1 = LinkCategorie($rowCat['id_pere_n1']);			 
				$ariane .= ($TabN1['libelle'])."  > "  ;
			}
			 
           
			if(!empty($rowCat['id_pere_n2'])){
				$TabN2 = LinkCategorie($rowCat['id_pere_n2']);		 
       		 	$ariane .= ($TabN2['libelle'])."  >  "  ;
             }
				 
                
             
			if(!empty($rowCat['id_pere_n3'])){
				$TabN3 = LinkCategorie($rowCat['id_pere_n3']);			 
				$ariane .= ($TabN3['libelle'])."  >  "   ;
			} 
			
			if($langue ==1){
				$ariane .= stripslashes($rowCat['libelle_fr']);
			}else{
				$ariane .= stripslashes($rowCat['libelle_en']);
			}
			$ariane = str_replace("€","euro",$ariane);
	
			
			$tabProduitCat = ListingCatalogue($rowCat['id_categorie']);
			
			
			
			for($i= 0; $i < count($tabProduitCat['id_produit']); $i++){
				
				
					if(!IsInArray($tabExportProd,$tabProduitCat['id_produit'][$i])){
						
							
							/// lecture marques produit
							/*
							$strSqlMark = "select catal_produit.id_marque, marques.* from catal_produit, marques ";
							$strSqlMark .= " where catal_produit.id_produit = ".$tabProduitCat['id_produit'][$i]." and catal_produit.id_marque = marques.id_marque";
							$resultMark = mysql_query($strSqlMark) or die ("Erreur lecture info mark : ".$strSqlMark."<hr>".mysql_error());
							$rowMark = mysql_fetch_array($resultMark);
					 		*/
					 
					 		
							
					 
					 
					 
							$MainPhotoProduit = MainPhotoProduit($tabProduitCat['id_produit'][$i],true);
							$PrixProduit = aPartirDe($tabProduitCat['id_produit'][$i]);
							
							$lienProduit = strtolower(UrlRewriter($tabProduitCat['libelle'][$i]))."-0-".$tabProduitCat['id_produit'][$i]."-".$tabProduitCat['id_categorie'][$i]."-".$tabProduitCat['n1'][$i]."-".$tabProduitCat['n1'][$i]."-".$tabProduitCat['n2'][$i]."-".$tabProduitCat['n3'][$i].".html";
							$lienProduit  = str_replace("--","-",$lienProduit);
							
							
							/// lecture des modele existants
							$strSqlSpec = "select * from catal_produit_spec where id_produit = ".$tabProduitCat['id_produit'][$i]." and nb_pieces > 0 and suppr =0 and publier = 1";
							$resultSpec = mysql_query($strSqlSpec) or die ("Erreur de lecture des spec produits : ".$strSqlSpec."<hr>".mysql_error());
							
							if($num= mysql_num_rows($resultSpec) > 0){
								
								while($rowSpec = mysql_fetch_array($resultSpec)){
									
										
										$critereLib = "";
										
										/// lecture des criteres
										if($rowSpec['id_critere1'] != 0 ){
											$strSqlLibC1 = "select libelle_fr,libelle_en from catal_critere   where id_critere = ".$rowSpec['id_critere1'];
											$resultLibC1 = mysql_query($strSqlLibC1) or die ("Erreur de lecture du lib C1 <hr>".mysql_error()."<hr>".$strSqlLibC1);
											$rowLibC1 = mysql_fetch_array($resultLibC1);
										 
											
											$strSqlValC1 = "select libelle_fr,libelle_en from catal_critere_val   where id_critere_val = ".$rowSpec['id_critere1_val'];
											$resultValC1 = mysql_query($strSqlValC1) or die ("Erreur de lecture du val C1 <hr>".mysql_error()."<hr>".$strSqlValC1);
											$rowValC1 = mysql_fetch_array($resultValC1);
											
											if($langue==1){
												$critereLib .= " | ".$rowValC1['libelle_fr'];
											}else{
												$critereLib .= " | ".$rowValC1['libelle_en'];
											}
										}
										
										
										if($rowSpec['id_critere2'] != 0 ){
											$strSqlLibC2 = "select libelle_fr,libelle_en from catal_critere   where id_critere = ".$rowSpec['id_critere2'];
											$resultLibC2 = mysql_query($strSqlLibC2) or die ("Erreur de lecture du lib C2 <hr>".mysql_error()."<hr>".$strSqlLibC2);
											$rowLibC2 = mysql_fetch_array($resultLibC2);
										 
											
											$strSqlValC2 = "select libelle_fr ,libelle_en from catal_critere_val   where id_critere_val = ".$rowSpec['id_critere2_val'];
											$resultValC2 = mysql_query($strSqlValC2) or die ("Erreur de lecture du val C2 <hr>".mysql_error()."<hr>".$strSqlValC2);
											$rowValC2 = mysql_fetch_array($resultValC2);
											
											if($langue==1){
												$critereLib .= " | ".$rowValC2['libelle_fr'];
											}else{
												$critereLib .= " | ".$rowValC2['libelle_en'];
											}
										}
										
										
										if($rowSpec['id_critere3'] != 0 ){
											$strSqlLibC3 = "select libelle_fr,libelle_en from catal_critere   where id_critere = ".$rowSpec['id_critere3'];
											$resultLibC3 = mysql_query($strSqlLibC3) or die ("Erreur de lecture du lib C3 <hr>".mysql_error()."<hr>".$strSqlLibC3);
											$rowLibC3 = mysql_fetch_array($resultLibC3);
										 
											
											$strSqlValC3 = "select libelle_fr,libelle_en from catal_critere_val   where id_critere_val = ".$rowSpec['id_critere3_val'];
											$resultValC3 = mysql_query($strSqlValC3) or die ("Erreur de lecture du val C3 <hr>".mysql_error()."<hr>".$strSqlValC3);
											$rowValC3 = mysql_fetch_array($resultValC3);
											
											if($langue==1){
												$critereLib .= " | ".$rowValC3['libelle_fr'];
											}else{
												$critereLib .= " | ".$rowValC3['libelle_en'];
											}
										}
										
										
									
 							
										///items avec spec
										if($langue ==1){
											$rep = "fr";	
										}else{
											$rep = "en";	
										}
										
										
										
											 
											$titreProd =utf8_encode(NoAccent($tabProduitCat['libelle'][$i]).$critereLib) ;
											$CatProd = utf8_encode(NoAccent($ariane));
											$SKUProd = 'LAG'.$tabProduitCat['id_produit'][$i]."-".$rowSpec['id_produit_spec']."-".$libLangue."-".$devise;
											$EANProd =  utf8_encode(($tabProduitCat['ean'][$i])) ;
											
											if($rowSpec['is_promo'] == 1){
												$PromoProd = 1;
												$DeviseProd = $devise;
												$PrixdProd =  number_format(TVA($rowSpec['prix_ht']*$tauxConv),2) ;
											}else{
												$PromoProd = 0;
												$DeviseProd = $devise;
												$PrixdProd =   number_format(TVA($rowSpec['prix_ht']*$tauxConv),2)  ;
											}
											
									 
										 	$lienProd = utf8_encode($rowInfoBoutique['url_boutique']."/".$rep."/".$lienProduit);
											
										 
										 
									 
											
											if($rowSpec['id_photo_produit'] != 0){
												
												$strSqlPhoto = "select img_list from catal_photo_produit where id_photo_produit = ".$rowSpec['id_photo_produit'];
												$resultPhoto = mysql_query($strSqlPhoto) or die ("Erreur de lecture de la photo specifique ");
												$rowPhoto = mysql_fetch_array($resultPhoto);
												if(is_file("../../../produit/".$rowPhoto['img_list'])){
													 
													$ImgProd =  $rowInfoBoutique['url_boutique']."/produit/".$rowPhoto['img_list'] ;
												}else{
													$ImgProd =   $rowInfoBoutique['url_boutique']."/produit/".$MainPhotoProduit['img_list'][0];
												}
											}else{
												$ImgProd =  $rowInfoBoutique['url_boutique']."/produit/".$MainPhotoProduit['img_list'][0] ;
											}
											
											$DescProd =  utf8_encode(strip_tags($tabProduitCat['descriptif'][$i])) ;
											$DescProd  = str_replace("\r\n"," ",$DescProd  );
											$DescProd  = html_entity_decode($DescProd,ENT_QUOTES);
											$DescProd  =str_replace("&rsquo;","'", 	$DescProd );
											$DescProd  =str_replace("&rdquo;","'", 	$DescProd );
											$DescProd  =str_replace("&ldquo;","'", 	$DescProd );
											$DescProd  =str_replace("&lsquo;","'", 	$DescProd );
											$DescProd  =str_replace("&ndash;","-", 	$DescProd );
											$DescProd  =str_replace(";",",", 	$DescProd );
											 
											
											$DescProd  =str_replace("&oelig;","oe", 	$DescProd );
											
																					
											$StockProd = 1;
											$DelaiProd = '2-3 jours';
											$FPProd = 0;
											$MarqueProd = 'LAGUIOLE';
									 
											
											 
											//$FluxStr .= 'SKU; Categorie; Libelle; Description courte; Marque; Prix; Frais de port; Etat produit; Ecotaxe; Image; Lien; Actif; EAN; Promo; Dispo; Devise; Stock; Poids;Delai\n' ;
										//$FluxStr .= 'SKU; Categorie; Libelle; Description; Marque; Prix; Frais de port; Etat produit; Ecotaxe; Image; Lien; Actif; EAN; Promo; Dispo; Devise; Stock; Langue;' ;	
										  $FluxStr .= $SKUProd.';'.$CatProd.';'.$titreProd.';'.str_replace("\r\n"," ",html_entity_decode($DescProd,ENT_QUOTES)).';'.$MarqueProd.';'.$PrixdProd.';'.$FPProd.';NEUF;0;'.$ImgProd.';'.$lienProd.';1;'.$EANProd.';'.$PromoProd.';1;'.$DeviseProd.';1000;'.$libLangue.';' ;
										  $FluxStr .= "\r\n";
							
								}
							}
							
							
							array_push($tabExportProd,$tabProduitCat['id_produit'][$i]);
					}
			}
		
		}
			
	}
	
 	
	/// fermeture du flux
 
 
 
 
 
 
 
 
 
 
 	//$exportBeezupLIVRE = ExportBeezupDetail("../../../export/","beezup-shop-detail-en-gbp", ".xml",$_POST['type_promotion'], $_POST['id_langue'], 'GBP', $tauxGBP);
 	//********************************
	//GBP EN 2
	//********************************	
	
	
	
	$_SESSION['id_langue'] =2;
	$langue =2;
	$devise='GBP';
	//$tauxConv = 1;
	
	$tauxGBP = 0;
	$strSqlTxGBP = "select * from catal_devise where id_devise = 6";
	$resultTxGBP = mysql_query($strSqlTxGBP) or die ("Erruer de lecture de la devise GBP");
	$rowTxGBP = mysql_fetch_array($resultTxGBP);
	$tauxConv =$rowTxGBP['taux_final'];
	
	$libLangue = "EN";
	
	$strSqlInfoBoutique = "select * from boutique_param order by id_boutique_param desc limit 0,1";
	$resultInfoBoutique = mysql_query($strSqlInfoBoutique) or die ("Erreur de lecture des infos de la boutique");
	if($num= mysql_num_rows($resultInfoBoutique) > 0){
		$rowInfoBoutique = mysql_fetch_array($resultInfoBoutique);
	}else{
		return false;
	}
	

	
	/// lecture des categories
	$strSqlCat = "select * from catal_categorie where suppr=0 and publier =1 order by ordre asc";
	$resultCat = mysql_query($strSqlCat) or die ("Erreur de lecture des catégories publiées");
	if($num = mysql_num_rows($resultCat) > 0){
		$tabProduitList = array();
		$tabExportProd = array();
		while($rowCat = mysql_fetch_array($resultCat)){
			
			$ariane ="";
			///arriane catégorie
			if(!empty($rowCat['id_pere_n1'])){
				$TabN1 = LinkCategorie($rowCat['id_pere_n1']);			 
				$ariane .= ($TabN1['libelle'])."  > "  ;
			}
			 
           
			if(!empty($rowCat['id_pere_n2'])){
				$TabN2 = LinkCategorie($rowCat['id_pere_n2']);		 
       		 	$ariane .= ($TabN2['libelle'])."  >  "  ;
             }
				 
                
             
			if(!empty($rowCat['id_pere_n3'])){
				$TabN3 = LinkCategorie($rowCat['id_pere_n3']);			 
				$ariane .= ($TabN3['libelle'])."  >  "   ;
			} 
			
			if($langue ==1){
				$ariane .= stripslashes($rowCat['libelle_fr']);
			}else{
				$ariane .= stripslashes($rowCat['libelle_en']);
			}
			$ariane = str_replace("€","euro",$ariane);
	
			
			$tabProduitCat = ListingCatalogue($rowCat['id_categorie']);
			
			
			
			for($i= 0; $i < count($tabProduitCat['id_produit']); $i++){
				
				
					if(!IsInArray($tabExportProd,$tabProduitCat['id_produit'][$i])){
						
							
							/// lecture marques produit
							/*
							$strSqlMark = "select catal_produit.id_marque, marques.* from catal_produit, marques ";
							$strSqlMark .= " where catal_produit.id_produit = ".$tabProduitCat['id_produit'][$i]." and catal_produit.id_marque = marques.id_marque";
							$resultMark = mysql_query($strSqlMark) or die ("Erreur lecture info mark : ".$strSqlMark."<hr>".mysql_error());
							$rowMark = mysql_fetch_array($resultMark);
					 		*/
					 
					 		
							
					 
					 
					 
							$MainPhotoProduit = MainPhotoProduit($tabProduitCat['id_produit'][$i],true);
							$PrixProduit = aPartirDe($tabProduitCat['id_produit'][$i]);
							
							$lienProduit = strtolower(UrlRewriter($tabProduitCat['libelle'][$i]))."-0-".$tabProduitCat['id_produit'][$i]."-".$tabProduitCat['id_categorie'][$i]."-".$tabProduitCat['n1'][$i]."-".$tabProduitCat['n1'][$i]."-".$tabProduitCat['n2'][$i]."-".$tabProduitCat['n3'][$i].".html";
							$lienProduit  = str_replace("--","-",$lienProduit);
							
							
							/// lecture des modele existants
							$strSqlSpec = "select * from catal_produit_spec where id_produit = ".$tabProduitCat['id_produit'][$i]." and nb_pieces > 0 and suppr =0 and publier = 1";
							$resultSpec = mysql_query($strSqlSpec) or die ("Erreur de lecture des spec produits : ".$strSqlSpec."<hr>".mysql_error());
							
							if($num= mysql_num_rows($resultSpec) > 0){
								
								while($rowSpec = mysql_fetch_array($resultSpec)){
									
										
										$critereLib = "";
										
										/// lecture des criteres
										if($rowSpec['id_critere1'] != 0 ){
											$strSqlLibC1 = "select libelle_fr,libelle_en from catal_critere   where id_critere = ".$rowSpec['id_critere1'];
											$resultLibC1 = mysql_query($strSqlLibC1) or die ("Erreur de lecture du lib C1 <hr>".mysql_error()."<hr>".$strSqlLibC1);
											$rowLibC1 = mysql_fetch_array($resultLibC1);
										 
											
											$strSqlValC1 = "select libelle_fr,libelle_en from catal_critere_val   where id_critere_val = ".$rowSpec['id_critere1_val'];
											$resultValC1 = mysql_query($strSqlValC1) or die ("Erreur de lecture du val C1 <hr>".mysql_error()."<hr>".$strSqlValC1);
											$rowValC1 = mysql_fetch_array($resultValC1);
											
											if($langue==1){
												$critereLib .= " | ".$rowValC1['libelle_fr'];
											}else{
												$critereLib .= " | ".$rowValC1['libelle_en'];
											}
										}
										
										
										if($rowSpec['id_critere2'] != 0 ){
											$strSqlLibC2 = "select libelle_fr,libelle_en from catal_critere   where id_critere = ".$rowSpec['id_critere2'];
											$resultLibC2 = mysql_query($strSqlLibC2) or die ("Erreur de lecture du lib C2 <hr>".mysql_error()."<hr>".$strSqlLibC2);
											$rowLibC2 = mysql_fetch_array($resultLibC2);
										 
											
											$strSqlValC2 = "select libelle_fr ,libelle_en from catal_critere_val   where id_critere_val = ".$rowSpec['id_critere2_val'];
											$resultValC2 = mysql_query($strSqlValC2) or die ("Erreur de lecture du val C2 <hr>".mysql_error()."<hr>".$strSqlValC2);
											$rowValC2 = mysql_fetch_array($resultValC2);
											
											if($langue==1){
												$critereLib .= " | ".$rowValC2['libelle_fr'];
											}else{
												$critereLib .= " | ".$rowValC2['libelle_en'];
											}
										}
										
										
										if($rowSpec['id_critere3'] != 0 ){
											$strSqlLibC3 = "select libelle_fr,libelle_en from catal_critere   where id_critere = ".$rowSpec['id_critere3'];
											$resultLibC3 = mysql_query($strSqlLibC3) or die ("Erreur de lecture du lib C3 <hr>".mysql_error()."<hr>".$strSqlLibC3);
											$rowLibC3 = mysql_fetch_array($resultLibC3);
										 
											
											$strSqlValC3 = "select libelle_fr,libelle_en from catal_critere_val   where id_critere_val = ".$rowSpec['id_critere3_val'];
											$resultValC3 = mysql_query($strSqlValC3) or die ("Erreur de lecture du val C3 <hr>".mysql_error()."<hr>".$strSqlValC3);
											$rowValC3 = mysql_fetch_array($resultValC3);
											
											if($langue==1){
												$critereLib .= " | ".$rowValC3['libelle_fr'];
											}else{
												$critereLib .= " | ".$rowValC3['libelle_en'];
											}
										}
										
										
									
 							
										///items avec spec
										if($langue ==1){
											$rep = "fr";	
										}else{
											$rep = "en";	
										}
										
										
										
											 
											$titreProd =utf8_encode(NoAccent($tabProduitCat['libelle'][$i]).$critereLib) ;
											$CatProd = utf8_encode(NoAccent($ariane));
											$SKUProd = 'LAG'.$tabProduitCat['id_produit'][$i]."-".$rowSpec['id_produit_spec']."-".$libLangue."-".$devise;
											$EANProd =  utf8_encode(($tabProduitCat['ean'][$i])) ;
											
											if($rowSpec['is_promo'] == 1){
												$PromoProd = 1;
												$DeviseProd = $devise;
												$PrixdProd =  number_format(TVA($rowSpec['prix_ht']*$tauxConv),2) ;
											}else{
												$PromoProd = 0;
												$DeviseProd = $devise;
												$PrixdProd =   number_format(TVA($rowSpec['prix_ht']*$tauxConv),2)  ;
											}
											
									 
										 	$lienProd = utf8_encode($rowInfoBoutique['url_boutique']."/".$rep."/".$lienProduit);
											
										 
										 
									 
											
											if($rowSpec['id_photo_produit'] != 0){
												
												$strSqlPhoto = "select img_list from catal_photo_produit where id_photo_produit = ".$rowSpec['id_photo_produit'];
												$resultPhoto = mysql_query($strSqlPhoto) or die ("Erreur de lecture de la photo specifique ");
												$rowPhoto = mysql_fetch_array($resultPhoto);
												if(is_file("../../../produit/".$rowPhoto['img_list'])){
													 
													$ImgProd =  $rowInfoBoutique['url_boutique']."/produit/".$rowPhoto['img_list'] ;
												}else{
													$ImgProd =   $rowInfoBoutique['url_boutique']."/produit/".$MainPhotoProduit['img_list'][0];
												}
											}else{
												$ImgProd =  $rowInfoBoutique['url_boutique']."/produit/".$MainPhotoProduit['img_list'][0] ;
											}
											
											$DescProd =  utf8_encode(strip_tags($tabProduitCat['descriptif'][$i])) ;
											$DescProd  = str_replace("\r\n"," ",$DescProd  );
											$DescProd  = html_entity_decode($DescProd,ENT_QUOTES);
											$DescProd  =str_replace("&rsquo;","'", 	$DescProd );
											$DescProd  =str_replace("&rdquo;","'", 	$DescProd );
											$DescProd  =str_replace("&ldquo;","'", 	$DescProd );
											$DescProd  =str_replace("&lsquo;","'", 	$DescProd );
											$DescProd  =str_replace("&ndash;","-", 	$DescProd );
											$DescProd  =str_replace(";",",", 	$DescProd );
											 
											
											$DescProd  =str_replace("&oelig;","oe", 	$DescProd );
											
																					
											$StockProd = 1;
											$DelaiProd = '2-3 jours';
											$FPProd = 0;
											$MarqueProd = 'LAGUIOLE';
									 
											
											 
											//$FluxStr .= 'SKU; Categorie; Libelle; Description courte; Marque; Prix; Frais de port; Etat produit; Ecotaxe; Image; Lien; Actif; EAN; Promo; Dispo; Devise; Stock; Poids;Delai\n' ;
										//$FluxStr .= 'SKU; Categorie; Libelle; Description; Marque; Prix; Frais de port; Etat produit; Ecotaxe; Image; Lien; Actif; EAN; Promo; Dispo; Devise; Stock; Langue;' ;	
										  $FluxStr .= $SKUProd.';'.$CatProd.';'.$titreProd.';'.str_replace("\r\n"," ",html_entity_decode($DescProd,ENT_QUOTES)).';'.$MarqueProd.';'.$PrixdProd.';'.$FPProd.';NEUF;0;'.$ImgProd.';'.$lienProd.';1;'.$EANProd.';'.$PromoProd.';1;'.$DeviseProd.';1000;'.$libLangue.';' ;
										  $FluxStr .= "\r\n";
							
								}
							}
							
							
							array_push($tabExportProd,$tabProduitCat['id_produit'][$i]);
					}
			}
		
		}
			
	}
	
 	
	/// fermeture du flux
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 	//$$exportBeezupDOLLAR = ExportBeezupDetail("../../../export/","beezup-shop-detail-en-usd", ".xml",$_POST['type_promotion'], $_POST['id_langue'], 'USD', $tauxUSD );
 	//********************************
	//USD EN 2
	//********************************	
	
	
	
	$_SESSION['id_langue'] =2;
	$langue =2;
	$devise='USD';
	//$tauxConv = 1;
	
	$tauxUSD = 0;
	$strSqlTxUSD = "select * from catal_devise where id_devise = 1";
	$resultTxUSD = mysql_query($strSqlTxUSD) or die ("Erruer de lecture de la devise USD");
	$rowTxUSD = mysql_fetch_array($resultTxUSD);
	$tauxConv =$rowTxUSD['taux_final'];
	
	$libLangue = "EN";
	
	$strSqlInfoBoutique = "select * from boutique_param order by id_boutique_param desc limit 0,1";
	$resultInfoBoutique = mysql_query($strSqlInfoBoutique) or die ("Erreur de lecture des infos de la boutique");
	if($num= mysql_num_rows($resultInfoBoutique) > 0){
		$rowInfoBoutique = mysql_fetch_array($resultInfoBoutique);
	}else{
		return false;
	}
	

	
	/// lecture des categories
	$strSqlCat = "select * from catal_categorie where suppr=0 and publier =1 order by ordre asc";
	$resultCat = mysql_query($strSqlCat) or die ("Erreur de lecture des catégories publiées");
	if($num = mysql_num_rows($resultCat) > 0){
		$tabProduitList = array();
		$tabExportProd = array();
		while($rowCat = mysql_fetch_array($resultCat)){
			
			$ariane ="";
			///arriane catégorie
			if(!empty($rowCat['id_pere_n1'])){
				$TabN1 = LinkCategorie($rowCat['id_pere_n1']);			 
				$ariane .= ($TabN1['libelle'])."  > "  ;
			}
			 
           
			if(!empty($rowCat['id_pere_n2'])){
				$TabN2 = LinkCategorie($rowCat['id_pere_n2']);		 
       		 	$ariane .= ($TabN2['libelle'])."  >  "  ;
             }
				 
                
             
			if(!empty($rowCat['id_pere_n3'])){
				$TabN3 = LinkCategorie($rowCat['id_pere_n3']);			 
				$ariane .= ($TabN3['libelle'])."  >  "   ;
			} 
			
			if($langue ==1){
				$ariane .= stripslashes($rowCat['libelle_fr']);
			}else{
				$ariane .= stripslashes($rowCat['libelle_en']);
			}
			$ariane = str_replace("€","euro",$ariane);
	
			
			$tabProduitCat = ListingCatalogue($rowCat['id_categorie']);
			
			
			
			for($i= 0; $i < count($tabProduitCat['id_produit']); $i++){
				
				
					if(!IsInArray($tabExportProd,$tabProduitCat['id_produit'][$i])){
						
							
							/// lecture marques produit
							/*
							$strSqlMark = "select catal_produit.id_marque, marques.* from catal_produit, marques ";
							$strSqlMark .= " where catal_produit.id_produit = ".$tabProduitCat['id_produit'][$i]." and catal_produit.id_marque = marques.id_marque";
							$resultMark = mysql_query($strSqlMark) or die ("Erreur lecture info mark : ".$strSqlMark."<hr>".mysql_error());
							$rowMark = mysql_fetch_array($resultMark);
					 		*/
					 
					 		
							
					 
					 
					 
							$MainPhotoProduit = MainPhotoProduit($tabProduitCat['id_produit'][$i],true);
							$PrixProduit = aPartirDe($tabProduitCat['id_produit'][$i]);
							
							$lienProduit = strtolower(UrlRewriter($tabProduitCat['libelle'][$i]))."-0-".$tabProduitCat['id_produit'][$i]."-".$tabProduitCat['id_categorie'][$i]."-".$tabProduitCat['n1'][$i]."-".$tabProduitCat['n1'][$i]."-".$tabProduitCat['n2'][$i]."-".$tabProduitCat['n3'][$i].".html";
							$lienProduit  = str_replace("--","-",$lienProduit);
							
							
							/// lecture des modele existants
							$strSqlSpec = "select * from catal_produit_spec where id_produit = ".$tabProduitCat['id_produit'][$i]." and nb_pieces > 0 and suppr =0 and publier = 1";
							$resultSpec = mysql_query($strSqlSpec) or die ("Erreur de lecture des spec produits : ".$strSqlSpec."<hr>".mysql_error());
							
							if($num= mysql_num_rows($resultSpec) > 0){
								
								while($rowSpec = mysql_fetch_array($resultSpec)){
									
										
										$critereLib = "";
										
										/// lecture des criteres
										if($rowSpec['id_critere1'] != 0 ){
											$strSqlLibC1 = "select libelle_fr,libelle_en from catal_critere   where id_critere = ".$rowSpec['id_critere1'];
											$resultLibC1 = mysql_query($strSqlLibC1) or die ("Erreur de lecture du lib C1 <hr>".mysql_error()."<hr>".$strSqlLibC1);
											$rowLibC1 = mysql_fetch_array($resultLibC1);
										 
											
											$strSqlValC1 = "select libelle_fr,libelle_en from catal_critere_val   where id_critere_val = ".$rowSpec['id_critere1_val'];
											$resultValC1 = mysql_query($strSqlValC1) or die ("Erreur de lecture du val C1 <hr>".mysql_error()."<hr>".$strSqlValC1);
											$rowValC1 = mysql_fetch_array($resultValC1);
											
											if($langue==1){
												$critereLib .= " | ".$rowValC1['libelle_fr'];
											}else{
												$critereLib .= " | ".$rowValC1['libelle_en'];
											}
										}
										
										
										if($rowSpec['id_critere2'] != 0 ){
											$strSqlLibC2 = "select libelle_fr,libelle_en from catal_critere   where id_critere = ".$rowSpec['id_critere2'];
											$resultLibC2 = mysql_query($strSqlLibC2) or die ("Erreur de lecture du lib C2 <hr>".mysql_error()."<hr>".$strSqlLibC2);
											$rowLibC2 = mysql_fetch_array($resultLibC2);
										 
											
											$strSqlValC2 = "select libelle_fr ,libelle_en from catal_critere_val   where id_critere_val = ".$rowSpec['id_critere2_val'];
											$resultValC2 = mysql_query($strSqlValC2) or die ("Erreur de lecture du val C2 <hr>".mysql_error()."<hr>".$strSqlValC2);
											$rowValC2 = mysql_fetch_array($resultValC2);
											
											if($langue==1){
												$critereLib .= " | ".$rowValC2['libelle_fr'];
											}else{
												$critereLib .= " | ".$rowValC2['libelle_en'];
											}
										}
										
										
										if($rowSpec['id_critere3'] != 0 ){
											$strSqlLibC3 = "select libelle_fr,libelle_en from catal_critere   where id_critere = ".$rowSpec['id_critere3'];
											$resultLibC3 = mysql_query($strSqlLibC3) or die ("Erreur de lecture du lib C3 <hr>".mysql_error()."<hr>".$strSqlLibC3);
											$rowLibC3 = mysql_fetch_array($resultLibC3);
										 
											
											$strSqlValC3 = "select libelle_fr,libelle_en from catal_critere_val   where id_critere_val = ".$rowSpec['id_critere3_val'];
											$resultValC3 = mysql_query($strSqlValC3) or die ("Erreur de lecture du val C3 <hr>".mysql_error()."<hr>".$strSqlValC3);
											$rowValC3 = mysql_fetch_array($resultValC3);
											
											if($langue==1){
												$critereLib .= " | ".$rowValC3['libelle_fr'];
											}else{
												$critereLib .= " | ".$rowValC3['libelle_en'];
											}
										}
										
										
									
 							
										///items avec spec
										if($langue ==1){
											$rep = "fr";	
										}else{
											$rep = "en";	
										}
										
										
										
											 
											$titreProd =utf8_encode(NoAccent($tabProduitCat['libelle'][$i]).$critereLib) ;
											$CatProd = utf8_encode(NoAccent($ariane));
											$SKUProd = 'LAG'.$tabProduitCat['id_produit'][$i]."-".$rowSpec['id_produit_spec']."-".$libLangue."-".$devise;
											$EANProd =  utf8_encode(($tabProduitCat['ean'][$i])) ;
											
											if($rowSpec['is_promo'] == 1){
												$PromoProd = 1;
												$DeviseProd = $devise;
												$PrixdProd =  number_format(TVA($rowSpec['prix_ht']*$tauxConv),2) ;
											}else{
												$PromoProd = 0;
												$DeviseProd = $devise;
												$PrixdProd =   number_format(TVA($rowSpec['prix_ht']*$tauxConv),2)  ;
											}
											
									 
										 	$lienProd = utf8_encode($rowInfoBoutique['url_boutique']."/".$rep."/".$lienProduit);
											
										 
										 
									 
											
											if($rowSpec['id_photo_produit'] != 0){
												
												$strSqlPhoto = "select img_list from catal_photo_produit where id_photo_produit = ".$rowSpec['id_photo_produit'];
												$resultPhoto = mysql_query($strSqlPhoto) or die ("Erreur de lecture de la photo specifique ");
												$rowPhoto = mysql_fetch_array($resultPhoto);
												if(is_file("../../../produit/".$rowPhoto['img_list'])){
													 
													$ImgProd =  $rowInfoBoutique['url_boutique']."/produit/".$rowPhoto['img_list'] ;
												}else{
													$ImgProd =   $rowInfoBoutique['url_boutique']."/produit/".$MainPhotoProduit['img_list'][0];
												}
											}else{
												$ImgProd =  $rowInfoBoutique['url_boutique']."/produit/".$MainPhotoProduit['img_list'][0] ;
											}
											
											$DescProd =  utf8_encode(strip_tags($tabProduitCat['descriptif'][$i])) ;
											$DescProd  = str_replace("\r\n"," ",$DescProd  );
											$DescProd  = html_entity_decode($DescProd,ENT_QUOTES);
											$DescProd  =str_replace("&rsquo;","'", 	$DescProd );
											$DescProd  =str_replace("&rdquo;","'", 	$DescProd );
											$DescProd  =str_replace("&ldquo;","'", 	$DescProd );
											$DescProd  =str_replace("&lsquo;","'", 	$DescProd );
											$DescProd  =str_replace("&ndash;","-", 	$DescProd );
											$DescProd  =str_replace(";",",", 	$DescProd );
											 
											
											$DescProd  =str_replace("&oelig;","oe", 	$DescProd );
											
																					
											$StockProd = 1;
											$DelaiProd = '2-3 jours';
											$FPProd = 0;
											$MarqueProd = 'LAGUIOLE';
									 
											
											 
											//$FluxStr .= 'SKU; Categorie; Libelle; Description courte; Marque; Prix; Frais de port; Etat produit; Ecotaxe; Image; Lien; Actif; EAN; Promo; Dispo; Devise; Stock; Poids;Delai\n' ;
										//$FluxStr .= 'SKU; Categorie; Libelle; Description; Marque; Prix; Frais de port; Etat produit; Ecotaxe; Image; Lien; Actif; EAN; Promo; Dispo; Devise; Stock; Langue;' ;	
										  $FluxStr .= $SKUProd.';'.$CatProd.';'.$titreProd.';'.str_replace("\r\n"," ",html_entity_decode($DescProd,ENT_QUOTES)).';'.$MarqueProd.';'.$PrixdProd.';'.$FPProd.';NEUF;0;'.$ImgProd.';'.$lienProd.';1;'.$EANProd.';'.$PromoProd.';1;'.$DeviseProd.';1000;'.$libLangue.';' ;
										  $FluxStr .= "\r\n";
							
								}
							}
							
							
							array_push($tabExportProd,$tabProduitCat['id_produit'][$i]);
					}
			}
		
		}
			
	}
	
 	
	/// fermeture du flux
 
 
 
 
 
 
 
 
 
 
 
 
	
	fputs($Flux,$FluxStr);
	
	fclose($Flux);

	return $nameFile.$ext;
	
}




?>