<!DOCTYPE html>
  <?php
    require_once("include/utils.php");
      // Avant d'afficher quoi que ce soit de dynamique, on va aller récupérer les résultats en fonction des paramètres
      $q            = getParam("q");
      $statut       = getParam("statut");
      $current_page = getParam("page");
      isset($_GET['limit']) ? ($limit = $_GET['limit']) : $limit = $par_page;
	  isset($_GET['sort']) ? ($sort = $_GET['sort']) : $sort = 'dateSoutenance+desc';
	  
	  $sortLabel = 'Par date de soutenance';
	  if($sort == 'dateSoutenance desc'){
		  $sortLabel = 'Par date de soutenance';
	  }
	  elseif($sort == 'titreTri asc'){
		  $sortLabel = 'Par titre';
	  }
	  elseif($sort == 'disciplineTri asc'){
		  $sortLabel = 'Par discipline';
	  }
      
      if (!$current_page){
        $current_page = 1;
      }
       $checkedFacetsObj = new stdClass();
	   $i = 0;
       $checkedFacetsObj->etablissement->$i = $etab_star;
       $checkedFacetsObj  = initObjWithVars('discipline',  $checkedFacetsObj);
       $checkedFacetsObj  = initObjWithVars('ecoleDoctorale',  $checkedFacetsObj);
       $checkedFacetsObj  = initObjWithVars('langueThese',  $checkedFacetsObj);
       $checkedFacetsObj  = initObjWithVars('directeurTheseNP',  $checkedFacetsObj); 

      $checkedFacets = "";
      foreach($checkedFacetsObj as $key => $value) {
		  foreach($checkedFacetsObj->$key as $v) {
		    $checkedFacets .= $key."=".urlencode($v).";";
		  }  
	  }
	 $current_year = date("Y");
	 $dateSoutenance ="[1965-01-01T23:59:59Z TO ".$current_year."-12-31T23:59:59Z]";	 
     $url = "http://www.theses.fr/?q=".urlencode($q)."&checkedfacets=".$checkedFacets."&maxnumber=".$limit."&sort=".$sort."&fq=dateSoutenance:".urlencode($dateSoutenance);
	 $stLabel = "";
      if ($statut == "encours")
      {
        $stLabel = "En préparation";
        $url = str_replace("www.theses.fr", "www.theses.fr/sujets", $url);
      }
      elseif ($statut == "fini")
      {
        $stLabel = "Soutenue";
        $url .= "&status=status:soutenue";
      }
      elseif ($statut == "finidispo")
      {
        $stLabel = "Soutenue et accessible en ligne";
        $url .= "&status=status:soutenue&access=accessible:oui";
      }
      
  
    $currentUrl = curPageURL();
	$urljson = $url ."&start=".(($current_page - 1)*$par_page)."&format=json";
	$json = getResponse($urljson, $proxy_server, $proxy_port); 
	$thesefr = json_decode($json);   
   
	if(is_object($thesefr)){
		$numFound = $thesefr->response->numFound;
	 }
	else  {
		$numFound = '';
	}    
         ?>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    
     <!-- Mobile viewport optimization h5bp.com/ad -->
    <meta name="HandheldFriendly" content="True">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">


    <title><?php echo $titre_page; ?></title>
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
      <!--
  <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css">
-->
	<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300,300italic,700,600,800' rel='stylesheet' type='text/css'>
	<link href="css/custom.css" rel="stylesheet">
	<link href="//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="js/html5shiv.js"></script>
    <![endif]-->
  </head>
  <body>
  
  
<!-- HEADER -->
<div id="header-navbar" class="navbar navbar-inverse navbar-static-top" role="navigation">
 <div class="container">
   <div class="navbar-header">
    <a href="<?php echo $base_url; ?>" class="navbar-brand"><i class="fa fa-mortar-board "></i> <?php echo $titre_page; ?></a>
    </div>
  </div>
</div>



<!-- SEARCH FORM -->
<div id="search-navbar" class="navbar navbar-default navbar-static-top " role="navigation">
  <div class="container">
     <form accept-charset="UTF-8" action="<?php echo $base_url; ?>" class="search-query-form form-inline clearfix navbar-form" method="get">
    <div class="input-group">
        <label for="q" class="sr-only">Votre recherche...</label>
		<input class="search_q q form-control" id="q" name='q' placeholder="Votre recherche..." type="text" autofocus="autofocus"  <?php if ($q) print "value='$q'"; ?>/>
      <span class="input-group-btn">
        <button type="submit" class="btn btn-primary search-btn" id="search">
          <span class="fa fa-search"></span>
        </button>
        </span>
      </div>
</form>
  </div>
</div>

<!-- CONTAINER -->
<div id="main-container" class="container">
    
<!-- SIDEBAR -->
<div class="row">
 <div id="sidebar" class="col-md-3">
  <div id="facets" class="facets sidenav">
  
<!-- FACETS -->  
  <div class="top-panel-heading panel-heading">
    <button type="button" class="facets-toggle" data-toggle="collapse" data-target="#facet-panel-collapse">
      <span class="sr-only">Déplier les facettes</span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
    </button>
   <h4>
  	Affiner les résultats
   </h4>
 </div>
 <div id="facet-panel-collapse" class="panel-group collapse" style="height: 0px;">
 	<div class="panel panel-default facet_limit theses-status">
     <div data-target="#facet-status" data-toggle="collapse" class="collapse-toggle panel-heading">
		<h5 class="panel-title"><a href="#" data-no-turbolink="true">Statut</a></h5>
	 </div>
	 <div class="panel-collapse facet-content collapse in" id="facet-status" style="height: auto;">
		 <div class="panel-body facet-panel">
			 <ul class="facet-values list-unstyled">
				 <?php echo facetteStatut();?>
			 </ul>
		 </div>
	</div>
   </div>
          <?php 
            echo getfacets($thesefr,'ecoleDoctorale', $checkedFacetsObj,$_GET);
			echo	getfacets($thesefr,'discipline', $checkedFacetsObj,$_GET);
			echo	getfacets($thesefr,'langueThese', $checkedFacetsObj,$_GET);
			echo	getfacets($thesefr,'directeurTheseNP', $checkedFacetsObj,$_GET); 
          ?>
</div>


<!-- ADS THESES.FR --> 
   <div class="well" style='background:white'>
            Consultez l'ensemble des thèses françaises depuis 1985, soutenues et en cours de préparation sur :<br/><br/>
            <a href='http://www.theses.fr'><img class="thesefr-img" src='img/logo_thesesfr.gif'/></a>
   </div>
 </div>
</div>

<div id="content" class="col-md-8">
<h2 class="sr-only top-content-title">Search</h2>

<?php
 if((isset($q) && !empty($q)) || (isset($checkedFacets) && ($checkedFacets != "etablissement=".urlencode($etab_star).";")) || (isset($statut) && !empty($statut))){
?>
<div id="appliedParams" class="clearfix constraints-container">
        <div class="pull-right">
          <a class="catalog_startOverLink btn btn-sm btn-text" href="<?php echo $base_url; ?>" id="startOverLink">Voir toutes les thèses</a>
        </div>
        <span class="constraints-label">Votre recherche :</span>
		<?php 
			$current_search = currentSearch($q,$checkedFacetsObj, $currentUrl, $stLabel); 
			foreach($current_search as $key => $value) {
				echo "$value";
			}
		?>
</div>
 <?php
 }
    $nb_pages = ceil($numFound/$limit);
	$currentUrlNoPage = preg_replace("/&page=\d*/", "", $currentUrl);
	if (!preg_match("/\?/", $currentUrlNoPage)){
		    $currentUrlNoPage .= "?";
	    }
	if(!empty($numFound) && $numFound != '0'){
					if ($current_page == 1 && $current_page != $nb_pages ){
						$current_results_first = "1";
						$current_results_last =  $limit;
						
					}
					elseif ($current_page == $nb_pages){
						$current_results_first = (($current_page-1)*$limit)+1;
						$current_results_last = $current_results_first + (($limit - 1) - (($nb_pages * $limit) - $numFound));
						
					}
					else {
						$current_results_first = (($current_page-1)* $limit)+1;
						$current_results_last = ($current_page* $limit);
						
						}                    
                    if ($current_page == 1) { $prev = ""; }
                    else { $prev = "<a href='$currentUrlNoPage&page=".($current_page - 1)."'> &laquo; Précédent</a>  |";}
                    
                    
                    if ($current_page == $nb_pages) {  $next = "";}
                    else { $next = "  | <a href='$currentUrlNoPage&page=".($current_page + 1)."'>Suivant &raquo;</a>";}
?>

<!-- SORTS --> 
<div id="sortAndPerPage" class="clearfix">
    <div class="page_links">
     <?php print $prev; ?> 
      <span class="page_entries">
        <strong><?php print $current_results_first; ?></strong> - <strong><?php print $current_results_last; ?></strong> sur <strong><?php print $numFound; ?></strong>
      </span>
      <?php print $next; ?>
    </div>
    <div class="search-widgets pull-right">
    <div class="btn-group" id="sort-dropdown">
  <button data-toggle="dropdown" class="btn btn-default dropdown-toggle" type="button">
      <a href="#"><?php print  $sortLabel; ?></a> <span class="caret"></span>
  </button>


  <ul role="menu" class="dropdown-menu">
  <?php
        print '<li><a href="'.removeqsvar($currentUrl, 'sort').'&sort=dateSoutenance+desc">Par date de soutenance</a></li>';
        print '<li><a href="'.removeqsvar($currentUrl, 'sort').'&sort=titreTri+asc">Par titre</a></li>';
        print '<li><a href="'.removeqsvar($currentUrl, 'sort').'&sort=disciplineTri+asc">Par discipline</a></li>';
  ?>
  </ul>
</div>

<!-- PER PAGE -->     
  <span class="sr-only">Nombre de résultats par page</span>
	<div class="btn-group" id="per_page-dropdown">
	  <button data-toggle="dropdown" class="btn btn-default dropdown-toggle" type="button"><a href="#"><?php print  $limit; ?> par page</a> <span class="caret"></span></button>
	  <ul role="menu" class="dropdown-menu">
	  <?php
	        print '<li><a href="'.removeqsvar($currentUrl, 'limit').'&limit=10">10<span class="sr-only"> par page</span></a></li>';
	        print '<li><a href="'.removeqsvar($currentUrl, 'limit').'&limit=20">20<span class="sr-only"> par page</span></a></li>';
	        print '<li><a href="'.removeqsvar($currentUrl, 'limit').'&limit=50">50<span class="sr-only"> par page</span></a></li>';
	  ?>
	  </ul>
	</div>
  </div>
 </div>  
 <?php  } 
	 elseif($numFound == '0') {
	?>
<!-- NO RESULTS -->  
	<h2>Aucun résultat trouvé</h2>
	<div class="noresults" id="documents">
  <h3>Essayez de modifier votre recherche</h3>
  <ul>
    <li>Utilisez moins de mots-clés  puis affinez les résultats à l'aide des liens disponibles sur la gauche de la page.</li>
  </ul>
</div> 
<?php	 
	 }
?>
<!-- RESULTS --> 
<h2 class="sr-only">Résultats</h2>
<div id="documents">
<?php
if(is_object($thesefr)){
	foreach ($thesefr->response->docs as $doc)
	{
		$urlRdf = 'http://www.theses.fr/'.$doc->num.'.rdf';
		$rdf = getResponse($urlRdf, $proxy_server, $proxy_port);
		$rdf = str_replace('rdf:', 'rdf_', $rdf);
		$rdf = str_replace('dc:', 'dc_', $rdf);  
		$rdf = str_replace('dcterms:', 'dcterms_', $rdf);
		$rdf = str_replace('skos:', 'skos_', $rdf);  
		$rdf = str_replace('isbd:', 'isbd_', $rdf);  
		$rdf = str_replace('marcrel:', 'marcrel_', $rdf);  
		$rdf = str_replace('foaf:', 'foaf_', $rdf);  
		$rdf = str_replace('bibo:', 'bibo_', $rdf);  
		$xml = simplexml_load_string($rdf); // load a SimpleXML object
		$jsonrdf = json_decode(json_encode($xml)); // use json to get all values into an array

		if ($doc->accessible == "oui"){$uri = 'http://www.theses.fr/'.$doc->num.'/document';}
			else{$uri = 'http://www.theses.fr/'.$doc->num;}

				print "<div class='document' id='$doc->num'>";
				print '<div class="documentHeader row">'."\n";

				if ($doc->status == "soutenue" ){ 
					print "<div class='nnt' style='display:none'>".$doc->num."</div>";
				}

				print "<h4 class='index_title titre_". $doc->num ." col-sm-10 col-lg-10'><a target='_blank' href='".$uri."'>".$doc->titre."</a></h4>";
				print "</div>";
				print '<div class="documentDetail row">'."\n";
				print '<div class="index-document-functions col-sm-10 col-lg-10">'."\n";
				print '<dl class="document-metadata dl-horizontal dl-invert">';
				print '<dt class="these-author_display">Auteur</dt>';
				print '<dd class="these-author_display">'.$doc->auteur.'</dd>';

				if(isset($jsonrdf->bibo_Thesis->dcterms_abstract)){
					if(is_array($jsonrdf->bibo_Thesis->dcterms_abstract)){
						print '<dt class="these-abstract_display">Résumé</dt>';
						print '<dd class="these-abstract_display"><a data-toggle="collapse" data-parent="#accordion" href="#collapseAbstract'. $doc->num .'">Lire le résumé</a></dd>';
						$first = true;
						foreach ($jsonrdf->bibo_Thesis->dcterms_abstract as $abstract){
							if ( $first ){
								print ' <dd id="collapseAbstract'. $doc->num .'" class="panel-collapse collapse"><div class="abstract">'.$abstract.'</div></dd>';
								$first = false;

							}
						}
					}
					else {
						if (is_string($jsonrdf->bibo_Thesis->dcterms_abstract)){
							print '<dt class="these-abstract_display">Résumé</dt>';
							print '<dd class="these-abstract_display"><a data-toggle="collapse" data-parent="#accordion" href="#collapseAbstract'. $doc->num .'">Lire le résumé</a></dd>';
							print ' <dd id="collapseAbstract'. $doc->num .'" class="panel-collapse collapse"><div class="abstract">'.$jsonrdf->bibo_Thesis->dcterms_abstract.'</div></dd>';
						}
					}
				}	
				print '<dt class="these-subject">Disicipline</dt>';
				print '<dd class="these-subject"><a href="'.$base_url.'?discipline[]='.urlencode($doc->discipline).'">'.$doc->discipline.'</a></dd>';
				print '<dt class="these-author_display">Date</dt>';
				if (isset($doc->dateSoutenance)){
					print "<dd class='these-date_display'>Soutenue le ".date('d/m/Y', strtotime($doc->dateSoutenance))."</dd>";
				}
				if (isset($doc->sujDatePremiereInscription)){
					print "<dd class='these-date_display'>En préparation depuis le ".date('d/m/Y', strtotime($doc->sujDatePremiereInscription))."</dd>";
				}
				if (isset($doc->sujDateSoutenancePrevue)){
					print "<dd class='these-date_display'>Soutenance prévue le ".date('d/m/Y', strtotime($doc->sujDateSoutenancePrevue))."</dd>";
				}
				print '<dt class="these-director_display">Sous la direction de</dt>';
				$i =0;
				foreach ($doc->directeurTheseNP as $directeurTheseNP){
					print '<dd class="these-director_display"><a href="'.$base_url.'?directeurTheseNP[]='.urlencode($directeurTheseNP).'">'.$doc->directeurThese[$i].'</a></dd>';
					$i++;
				}
				print '<dt class="these-org">Organisme</dt>';
				if(is_array($jsonrdf->bibo_Thesis->marcrel_dgg)){
					foreach ($jsonrdf->bibo_Thesis->marcrel_dgg as $org){
						print '<dd class="these-org">'.$org->foaf_Organization->foaf_name.'</dd>';
					}
				}
				else {
					print '<dd class="these-org">'.$jsonrdf->bibo_Thesis->marcrel_dgg->foaf_Organization->foaf_name.'</dd>';

				}
				if ($doc->status == "soutenue"){		
					print '<dt class="these-holdings_display">Disponibilité</dt>';

					print '<dd class="index-document-functions acces_'.$doc->num.'">'."\n";
					if ($doc->status == "soutenue"){

						if ($doc->accessible == "oui"){
							print '<div class="availability"><span class="label label-primary online">Web</span><a href="'.$uri.'" title="Disponible en ligne" target="_blank"> Accès libre en ligne</a></div>';
						}
						if ($doc->status == "soutenue"){
							print '<div class="spinner"><i class="fa fa-spinner fa-spin fa-2x"></i></div>';
						}
					}
					print '</dd>';
				}	
				print '</dl>';
				print '</div>';
				print '<div class="col-sm-1 col-lg-1">'."\n";
				if ($doc->status == "soutenue"){
					print "<img src='img/soutenue.png' alt='Thèse soutenue' title='Thèse soutenue'/>";
					if ($doc->accessible == "oui"){
					}
				}
				elseif ($doc->status  == "enCours"){
					print "<img src='img/preparation.png'  alt='Thèse en préparation' title='Thèse en préparation'/>"; 


				}
				print '</div>';	  
				print '</div>'; 
				print '</div>'; 
			}

		}
		else {
?>
<!-- ERROR RETRIEVING THESES.FR  --> 
			<div class="noresults" id="documents">
			  <h3>Le site <a href='theses.fr' target='_blank'>theses.fr</a> rencontre actuellement des difficultés</h3>
			  <ul>
			    <li>Merci de réessayer plus tard.</li>
			  </ul>
			</div> 	
			<?php	
			}
			?>
			</div>
			<!-- PAGINATION --> 
			<?php
			if(!empty($numFound) && $numFound != '0'){
			?>
			<div class="row record-padding">
			  <div class="col-md-9"> 
			<?php
			 	echo renderNavigation($cntAround = 2,$nb_pages,$current_page,$currentUrlNoPage) ;
			?>
			  </div>
			 </div>
			 <?php
			}
			?> 
			</div>
		</div>
	</div>
</div>
     <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
<script type="text/javascript" src="js/scripts.js"></script>

  </body>
</html>
