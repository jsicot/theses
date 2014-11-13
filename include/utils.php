<?php
require_once dirname(__FILE__).'/config.php';
// Pour ne pas avoir de warnings dans les dernières version de PHP
date_default_timezone_set("Europe/Paris");

function getParam($code){
    if (isset($_GET[$code])){
      return $_GET[$code];
    }
    elseif (isset($_POST[$code])){
      return $_POST[$code];
    }
    else{
      return "";
    }
}
function facetteStatut(){
	$curUrl = curPageURL();
	$sortie = '';
	$statut_courant = preg_replace("/^.*statut=([^&]*).*$/", "$1", $curUrl);
	$curUrl = preg_replace("/&?statut=[^&]*/", "", $curUrl);
	if (!preg_match("/\?/", $curUrl)){
		$curUrl .= "?";
	}
	if ($statut_courant == "encours"){
		$sortie .= '<li><span class="facet-label"><span class="selected">En préparation</span>';
		$sortie .= '<a href="'.removeqsvar( $curUrl, 'statut').'" class="remove"><span class="fa fa-times-circle"></span><span class="sr-only">[remove]</span></a>';
		$sortie .= '</span></li>';  
	}
	else{
		$sortie .= '<li><span class="facet-label"><a href="'.$curUrl.'&statut=encours">En préparation</a></span></li>';  
	}
	if ($statut_courant == "fini"){
		$sortie .= '<li><span class="facet-label"><span class="selected">Soutenue</span>';
		$sortie .= '<a href="'.removeqsvar( $curUrl, 'statut').'" class="remove"><span class="fa fa-times-circle"></span><span class="sr-only">[remove]</span></a>';
		$sortie .= '</span></li>';  
	}
	else{
		$sortie .= '<li><span class="facet-label"><a href="'.$curUrl.'&statut=fini">Soutenue</a></span></li>';  
	}
	if ($statut_courant == "finidispo"){
		$sortie .= '<li><span class="facet-label"><span class="selected">Soutenue et accessible en ligne</span></span>';
		$sortie .= '<a href="'.removeqsvar( $curUrl, 'statut').'" class="remove"><span class="fa fa-times-circle"></span><span class="sr-only">[remove]</span></a>';
		$sortie .= '</span></li>';  
	}
	else{
		$sortie .= '<li><span class="facet-label"><a href="'.$curUrl.'&statut=finidispo">Soutenue et accessible en ligne</a></span></li>';  
	}
	return $sortie;
}
function curPageURL(){
	$pageURL = 'http://';
	$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	return $pageURL;
}
function currentSearch($q,$checkedFacetsObj, $currentUrl,$statut ){
	$current_search = array();
	if(!empty($q)){
		$kw='<span class="btn-group appliedFilter constraint filter filter-keywords">';
		$kw.='<a class="constraint-value btn btn-sm btn-default btn-disabled" href="">';
		$kw.='<span class="filterName">Mots-clés</span> ';
		$kw.=' <span class="filterValue">'. $q.'</span>';
		$kw.=' </a>';
		$kw.=' <a href="'.removeqsvar( $currentUrl, 'q').'" class="btn btn-default btn-sm remove dropdown-toggle"><span class="fa fa-times-circle"></span><span class="sr-only">Retirer</span></a>
		</span>';
		$current_search[] = $kw;
	}
	if(!empty($statut)){
		$st='<span class="btn-group appliedFilter constraint filter filter-status">';
		$st.='<a class="constraint-value btn btn-sm btn-default btn-disabled" href="">';
		$st.='<span class="filterName">Statut</span> ';
		$st.=' <span class="filterValue">'. $statut.'</span>';
		$st.=' </a>';
		$st.=' <a href="'.removeqsvar( $currentUrl, 'statut').'" class="btn btn-default btn-sm remove dropdown-toggle"><span class="fa fa-times-circle"></span><span class="sr-only">Retirer</span></a>
		</span>';
		$current_search[] = $st;
	}
	foreach($checkedFacetsObj as $key => $value) {
		foreach($checkedFacetsObj->$key as $v) {
			if($key != 'etablissement') {
				$index='<span class="btn-group appliedFilter constraint filter filter-'. $key.'">';
				$index.='<a class="constraint-value btn btn-sm btn-default btn-disabled" href="">';

				$index.='<span class="filterName">'. getFacetLabel($key).'</span> ';
				$index.=' <span class="filterValue">'. $v.'</span>';
				$index.=' </a>';
				$index.=' <a href="'.removefacetqsvar($currentUrl,$key,$checkedFacetsObj,$v).'" class="btn btn-default btn-sm remove dropdown-toggle"><span class="fa fa-times-circle"></span><span class="sr-only">Retirer</span></a>
				</span>';
				$current_search[] = $index;
			}
		}
	}
	return $current_search;
}
function getfacets($obj, $code, $checkedFacetsObj){
	$curUrl = curPageURL();
	$curUrl = removeqsvar($curUrl, 'page');
	$currentVal = preg_replace("/^.*".$code."=\[\]([^&]*).*$/", "$1", $curUrl);
	$cleanCurrentVal = urldecode($currentVal);
	$curUrl = preg_replace("/&?".$code."=[^&]*/", "", $curUrl);
	if (!preg_match("/\?/", $curUrl))
	{
		$curUrl .= "?";
	}
	if(is_object($obj)){
		if($obj->facet_counts->facet_fields->$code)  {   
			if(isset($checkedFacetsObj->$code)){
				$classFacet = "facet_limit-active";
				$collapse = "collapse in";
			}
			else {
				$classFacet = "";
				$collapse = "collapse";
			}
			$out ='';			
			$out .='<div class="panel panel-default facet_limit theses-'.$code.' '. $classFacet.'">';
			$out .='<div data-target="#facet-'.$code.'" data-toggle="collapse" class="collapse-toggle panel-heading collapsed ">';
			$out .='<h5 class="panel-title"><a href="#" data-no-turbolink="true">'. getFacetLabel($code).'</a></h5>';
			$out .='</div>';
			$out .='<div class="panel-collapse facet-content '. $collapse .'" id="facet-'.$code.'" style="height: auto;">';
			$out .='<div class="panel-body facet-panel">';
			$out .='<ul class="facet-values list-unstyled">';
			$facets = array();
			$n = 0; 
			foreach ($obj->facet_counts->facet_fields->$code as $facet){
				if($n%2 && !empty($value)){
					$o->count =  $facet;

					$facets[]= $o;
					unset($value);

				}
				elseif(!empty($facet) && !is_int($facet)) {

					$value = $facet ;
					$o = $n;
					$o =  new stdClass();
					$o->label =  $facet;
					if ((isset($_GET[$code])) && in_array($facet, $_GET[$code])) {$o->ischecked = 'y';}
					else{$o->ischecked = 'n';}
				}
				$n++;
			}
			sort_obj($facets,array("ischecked","count"));
			foreach ($facets as $facet){
				$label = $facet->label;
				if($code == 'langueThese'){$label = convertLangISO($label);}
				$out .= "<li><span class='facet-label'>";

				if($facet->ischecked == "y") {
					$out .= "<span class='selected'>".	$label ."</span>";
					$out .= '<a href="'.removefacetqsvar($curUrl, $code,$checkedFacetsObj,$facet->label ).'" class="remove"><span class="fa fa-times-circle"></span><span class="sr-only">[remove]</span></a>';
					$class = 'selected';
				}
				else {
					$class = '';
					$out .= "<a href='".addfacetqsvar($curUrl, $code,$checkedFacetsObj,$facet->label )."' class='facet_select'>".	$label ."</a>";
				}
				$out .= "</span>";

				$out .= "<span class='".$class." facet-count'>".$facet->count."</span>";
				$out .= "</li>";
			}
			$out .= "</ul>";
			$out .= "</div>";
			$out .= "</div>";
			$out .= "</div>";
			return $out;

		}
	}
}
function getFacetLabel($index) {
	if (!isset($facetLabel)) 
		$facetLabel	= new stdClass();
	$facetLabel->etablissement->label = "Établissements";
	$facetLabel->ecoleDoctorale->label = "Écoles Doctorales";
	$facetLabel->discipline->label = "Disciplines";
	$facetLabel->langueThese->label = "Langues";
	$facetLabel->directeurTheseNP->label = "Directeurs de thèse";
	return $facetLabel->$index->label ;
}
function removeqsvar($url, $varname) {
	list($urlpart, $qspart) = array_pad(explode('?', $url), 2, '');
	parse_str($qspart, $qsvars);
	unset($qsvars[$varname]);
	$newqs = http_build_query($qsvars);
	return $urlpart . '?' . $newqs;
}
function addfacetqsvar($url, $varname, $obj, $value) {
	list($urlpart, $qspart) = array_pad(explode('?', $url), 2, '');
	parse_str($qspart, $qsvars);
	unset($qsvars[$varname]);
	$newqs = http_build_query($qsvars);
	$url = $urlpart . '?' . $newqs;

	$q= "";

	if(isset($obj->$varname)) {
		foreach($obj->$varname as $v) {
			if ($v != $value){
				$q  .= "&".$varname."[]=".urlencode($v);
			}
		}
	} 
	if(isset($value )){$q .= "&".$varname."[]=".urlencode($value);}
	return  $url.$q;
}
function removefacetqsvar($url, $varname, $obj, $value) {
	list($urlpart, $qspart) = array_pad(explode('?', $url), 2, '');
	parse_str($qspart, $qsvars);
	unset($qsvars[$varname]);
	$newqs = http_build_query($qsvars);
	$url = $urlpart . '?' . $newqs;

	$q= "";

	if(isset($obj->$varname)) {
		foreach($obj->$varname as $v) {
			if ($v != $value){
				$q  .= "&".$varname."[]=".urlencode($v);
			}
		}
	} 
	return  $url.$q;
}
function convertLangISO($code){
	$COUNTRY = array(
		"fr" => "Français",
		"br" => "Breton",
		"en" => "Anglais",
		"pt" => "Portugais",
		"de" => "Allemand",
		"es" => "Espagnol",
		"it" => "Italien",
		"ar" => "Arabe"
		);
	return $COUNTRY[$code];
}
function removeqsvarbyregex($url, $varname) {
	return preg_replace('/([?&])'.$varname.'=[^&]+(&|$)/','$1',$url);
}
function renderNavigation($cntAround = 4,$cntPages,$current,$currentUrlNoPage) {
	$out      = '';
	$isGap    = false; // A "gap" is the pages to skip
	for ($i = 0; $i < $cntPages; $i++) { // Run through pages
		$isGap = false;

		// Are we at a gap?
		if ($cntAround >= 0 && $i > 0 && $i < $cntPages - 1 && abs($i - $current) > $cntAround) { // If beyond "cntAround" and not first or last.
			$isGap    = true;

			// Skip to next linked item (or last if we've already run past the current page)
			$i = ($i < $current ? $current - $cntAround : $cntPages - 1) - 1;
		}
		$lnk = ($isGap ? '...' : ($i + 1)); // If gap, write ellipsis, else page number
		if ($i != ($current-1) && !$isGap) { // Do not link gaps and current
			$lnk = '<a href="'.$currentUrlNoPage.'&page=' . ($i + 1) . '">' . $lnk . '</a>';
		}
		if ($i == ($current-1) || $isGap) { // Do not link gaps and current
			$out .= "\t<li class='disabled'><a href='#'>" . $lnk . "</a></li>\n"; // Wrap in list items
		}
		else {
			$out .= "\t<li>" . $lnk . "</li>\n"; // Wrap in list items
		}
	}
	return "<div class='pagination'>\n<ul class='pagination'>\n" . $out . '</ul>'; // Wrap in list
}
function sort_obj(&$obj, $props){
	usort($obj, function($a, $b) use ($props) {
		if($a->$props[0] == $b->$props[0])
			return $a->$props[1] < $b->$props[1] ? 1 : -1;
		return $a->$props[0] < $b->$props[0] ? 1 : -1;
	});
}
function initObjWithVars($var, $obj){
	if(isset($_GET[$var])){
		$i = 0; 
		foreach($_GET[$var] as $v){
			$obj->$var->$i = $v;
			$i++; 
		}
	} 
	return $obj;
}
function getResponse($url, $proxy_server,$proxy_port ){
	if (extension_loaded('curl'))
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_PROXY, $proxy_server .':' . $proxy_port);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		$response = curl_exec($ch);
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$header = substr($response, 0, $header_size);
		$body = substr($response, $header_size);

		$error_number = curl_errno($ch);
		$error_message = curl_error($ch);

		if($error_number > 0)
		{
			echo 'Method failed:'.$error_message;
		}
		curl_close($ch);
	}
	return $body;   
}
?>
