<!DOCTYPE html>
  <?php
    require_once("include/utils.php");
  ?>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title><?php echo $titre_page; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <link href="css/bootstrap.css" rel="stylesheet">
    <style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
	padding-left:10px;
	padding-right:10px;
      }
      .sidebar-nav {
        padding: 9px 0;
      }

      @media (max-width: 980px) {
        /* Enable use of floated navbar text */
        .navbar-text.pull-right {
          float: none;
          padding-left: 5px;
          padding-right: 5px;
        }
      }
      
      .notice
      {
        border-top:1px solid #CCC;
        padding:10px;
      }
      
      .notice h2
      {
        font-size:1.4em;
        margin:0px;
        padding:0px;
        line-height:1.5em;
        font-weight:normal;
      }

      .navbar .brand {
	display: block;
	float: left;
	padding: 10px 20px 10px;
	margin-left: -20px;
	font-size: 20px;
	font-weight: 200;
	color: #CCC;
      }
      .nav-list {
	padding-right: 15px;
	padding-left: 15px;
	margin-bottom: 0;
      }
      
      .nav-list > li > a,
      .nav-list .nav-header {
	margin-right: -15px;
	margin-left: -15px;
	text-shadow: 0 1px 0 rgba(255, 255, 255, 0.5);
      }
      
      .nav-list > li > a {
	padding: 3px 15px;
      }
      
      .nav-list > .active > a,
      .nav-list > .active > a:hover,
      .nav-list > .active > a:focus {
	color: #ffffff;
	text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.2);
	background-color: #0088cc;
      }

    </style>
    <link href="css/bootstrap-responsive.css" rel="stylesheet">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="js/html5shiv.js"></script>
    <![endif]-->
  </head>
  <body>
    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container-fluid">
          <a class="brand" href="<?php echo $base_url; ?>"><?php echo $titre_page; ?> [interface en test]</a>
        </div>
      </div>
    </div>

    <?php
      // Avant d'afficher quoi que ce soit de dynamique, on va aller récupérer les résultats en fonction des paramètres
      $q            = getParam("q");
      $statut       = getParam("statut");
      $current_page = getParam("page");
      
      if (!$current_page)
      {
        $current_page = 1;
      }
      
      $url = "http://www.theses.fr/?q=".urlencode($q)."&checkedfacets=etablissement=".urlencode($etab_star)."&format=xml&maxnumber=10&sort=dateSoutenance+desc";

      if ($statut == "encours")
      {
        $url = str_replace("www.theses.fr", "www.theses.fr/sujets", $url);
      }
      elseif ($statut == "fini")
      {
        $url .= "&status=status:soutenue";
      }
      elseif ($statut == "finidispo")
      {
        $url .= "&status=status:soutenue&access=accessible:oui";
      }
      
      // On va ajouter le numéro de page
      $url .= "&start=".(($current_page - 1)*$par_page);

      $currentUrl = curPageURL();
      $xml = simplexml_load_file($url);
      
      $numFound = $xml->{'result'}['numFound'];
      
      $cpt = 0;
      $results = array();
      foreach ($xml->{'result'}->{'doc'} as $doc)
      {
        if ($cpt < $par_page)
        {
          $these = array();
          $these["titre"]           = (string) current($doc->xpath("str[@name='titre']"));
          $these["auteur"]          = (string) current($doc->xpath("str[@name='auteur']"));
          $these["status"]          = (string) current($doc->xpath("str[@name='status']"));
          $these["accessible"]      = (string) current($doc->xpath("str[@name='accessible']"));
          $these["directeur"]       = (string) current(current($doc->xpath("arr[@name='directeurThese']"))->{'str'});
          $these["id"]              = (string) current($doc->xpath("str[@name='num']"));
          if ($these["dateSoutenance"]  = $doc->xpath("date[@name='dateSoutenance']"))
          {
            $these["dateSoutenance"] = current($these["dateSoutenance"]);
            $these["dateSoutenance"] = preg_replace("/^(\d{4})-(\d{2})-(\d{2}).*$/", "$3/$2/$1", $these["dateSoutenance"]);
          }
          
          array_push($results, $these);
        }

        $cpt++;
      }
    ?>
    
    <div class="container-fluid">
      <div class="row-fluid">
        <div class="span3">
          <div>
            <form class="form-search" method="get" action="<?php echo $base_url; ?>">
              <input type='text' name='q' <?php if ($q) print "value='$q'"; ?>/>
              <button type="submit" class="btn">Chercher</button>
            </form>
          </div>
          <div class="well sidebar-nav">
            <ul class="nav nav-list">
              <?php
                echo facetteStatut();
              ?><!--
              <li class="nav-header">Directeur de thèse</li>
              <li>À venir</li>
              <li class="nav-header">Discipline</li>
              <li>À venir</li> -->
            </ul>
          </div><!--/.well -->
	  <?php
	    if ($gestion_stats == 1)
	    {
	      // On va récupérer le nombre total de consultation
	      $mois_courant = date("Y-m");
	      $res = SQL("select sum(nb) as total from ".STATS_TABLE." where mois not like '".$mois_courant."';");
	      $row = mysql_fetch_assoc($res);
	      $stats_total = number_format($row["total"], 0, ',', ' ');
	      print '<div class="well">';
	      print "<div style='font-size:3em; text-align:center'><a href='stats.php'>".$stats_total."</a></div>";
	      print "thèses téléchargées depuis le 1er janvier 2012. Voir <a href='stats.php'>plus de statistiques</a> (thèses les plus téléchargées ...)";
	      print '</div>';
	    }
	  ?>
          <div class="well" style='background:white'>
            Consultez l'ensemble des thèses françaises depuis 1985, soutenues et en cours de préparation sur :<br/><br/>
            <a href='http://www.theses.fr'><img src='img/logo_thesesfr.gif'/></a>
          </div>
        </div><!--/span-->
        <div class="span9">
          <div class="row-fluid">
            <div class="span12">
              <p><?php print $numFound ?> thèses répondent à vos critères de recherches.</p>
            </div>
          </div>
          
          <?php
            foreach ($results as $these)
            {
              print '<div class="row-fluid notice">'."\n";
              print '<div class="span10">'."\n";
              print "<h2><a target='_blank' href='http://www.theses.fr/".$these["id"]."'>".$these["titre"]."</a></h2>";
              print "<p>par ".$these["auteur"]." sous la direction de ".$these["directeur"]."</p>";
              print "</div>";
              print "<div class='span2 text-center'>";
              if ($these["status"] == "soutenue")
              {
                print "<img src='img/soutenue.png' alt='Thèse soutenue' title='Thèse soutenue'/>";
                print "<br/><small>soutenue le ".$these["dateSoutenance"]."</small>";
                if ($these["accessible"] == "oui")
                {
                  print "<br/><small>Consultable en ligne</small>";
                }
              }
              elseif ($these["status"] == "enCours")
              {
                print "<img src='img/preparation.png'  alt='Thèse en préparation' title='Thèse en préparation'/>";  
                if ($these["dateSoutenance"] != "")
                {
                	print "<br/><small>soutenue le ".$these["dateSoutenance"]."</small><br/><small>traitement en cours</small>";
                }
              }
              print "</div>";
              print "</div>";
            }
          ?>
          <div class="row-fluid">
            <div class="span12 text-center">
              <div class="pagination">
                <ul>
                  <?php
                    $nb_pages = ceil($numFound/$par_page);
                    $currentUrlNoPage = preg_replace("/&page=\d*/", "", $currentUrl);

										if (!preg_match("/\?/", $currentUrlNoPage))
										{
											$currentUrlNoPage .= "?";
										}

                    print "<li ";
                    if ($current_page == 1) { print "class='disabled'"; }
                    print "><a href='$currentUrlNoPage&page=".($current_page - 1)."'>Précédente</a></li>";
                    
                    print "<li class='disabled'><a href='#'>Page $current_page / $nb_pages</a></li>";
                    
                    print "<li ";
                    if ($current_page == $nb_pages) { print "class='disabled'"; }
                    print "><a href='$currentUrlNoPage&page=".($current_page + 1)."'>Suivante</a></li>";
                  ?>
                </ul>
              </div>
            </div>
          </div>
        </div><!--/span-->
      </div><!--/row-->
      

      <footer>
        <p>&copy; Université Bordeaux 3, 2013</p>
      </footer>

    </div><!--/.fluid-container-->

    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="js/jquery-1.9.1.min.js"></script>
    <script src="js/bootstrap.js"></script>
  </body>
</html>
<?php
  // Ici on met les fonctions utilisées ailleurs dans le document :
  function facetteStatut()
  {
    $curUrl = curPageURL();
    $sortie = '<li class="nav-header">Statut de la thèse</li>'."\n";
    
    // On va gérer les trois status possibles : "En préparation", "Soutenue", "Soutenue et accessible en ligne";
    
    // On récupère le statut courant s'il existe
    $statut_courant = preg_replace("/^.*statut=([^&]*).*$/", "$1", $curUrl);
    $curUrl = preg_replace("/&?statut=[^&]*/", "", $curUrl);
    
    if (!preg_match("/\?/", $curUrl))
    {
      $curUrl .= "?";
    }
    
    if ($statut_courant == "encours")
    {
      $sortie .= '<li>En préparation</li>';  
    }
    else
    {
      $sortie .= '<li><a href="'.$curUrl.'&statut=encours">En préparation</a></li>';  
    }
    
    if ($statut_courant == "fini")
    {
      $sortie .= '<li>Soutenue</li>';
    }
    else
    {
      $sortie .= '<li><a href="'.$curUrl.'&statut=fini">Soutenue</a></li>';
    }
    
    if ($statut_courant == "finidispo")
    {
      $sortie .= '<li>Soutenue et accesible en ligne</li>';
    }
    else
    {
      $sortie .= '<li><a href="'.$curUrl.'&statut=finidispo">Soutenue et accesible en ligne</a></li>';  
    }
    return $sortie;
  }
  
  function curPageURL()
  {
    $pageURL = 'http://';
    $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
    return $pageURL;
  }
  
?>
