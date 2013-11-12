<!DOCTYPE html>
<?php
  require_once("include/utils.php");
  if ($gestion_stats == 0)
  {
    print "Statistiques non g&eacute;r&eacute;es sur cette instance.";
    exit;
  }
?>
<html lang="en">
  <head>
    <meta charset="utf-8">
        <title>Thèses Bordeaux 3</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <link href="css/bootstrap.css" rel="stylesheet">
    <style type="text/css">
      body {
        padding: 50px;
        
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
      
      .brand
      {
        color:#FF9900;
      }
      
      #chart_div
      {
        height:300px;
      }
      
      .jumbotron
      {
        text-align:center;
        font-size:2em;
        border:2px solid black;
      }
      
      .jumbotron .chiffre
      {
        font-weight:bold;
      }
      
      .jumbotron
      {
        margin-top:30px;
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

    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['Mois', 'Téléchargements pour le mois', 'Nombre de thèses en ligne'],
          <?php
	    // On va commencer par regarder le nombre de fichiers déposé
            $sql = "select substring(date_en_ligne, 1, 7) as mois, count(*) as nb from tel_records group by mois order by mois";
            $res = SQL($sql);
            
            $docs_en_ligne = array();
            $docs_total = 0;
            while ($row = mysql_fetch_assoc($res))
            {
              $docs_total += $row["nb"];
              $docs_en_ligne[$row["mois"]] = $docs_total;
            }
            
            $array_keys = array_keys($docs_en_ligne);
            $year_s = substr($array_keys[0], 0, 4);
            $year_e = substr($array_keys[sizeof($array_keys) - 1], 0, 4);

            // On va remplir les trous intermédiaires
            $total_courant = null;
            for ($year = $year_s; $year <= $year_e; $year++)
            {
              for ($month = 1; $month <= 12; $month++)
              {
                $date_construite = $year."-".sprintf("%02d", $month);
                if (!isset($docs_en_ligne[$date_construite]))
                {
                  if ($total_courant !== null)
                  {
                    $docs_en_ligne[$date_construite] = $total_courant;
                  }
                  else
                  {
                    // On est trop tôt dans la boucle, on n'a pas encore rencontré de valeur, on s'en moque
                  }
                }
                else
                {
                  $total_courant = $docs_en_ligne[$date_construite];  
                }
              }
            }
          
            $total_fichiers_global = 0;
            $mois_courant = date("Y-m");
            $sql = "select mois, sum(nb) as total, count(distinct record_id) as nb_doc from ".STATS_TABLE." where nb != 0 and mois not like '".$mois_courant."' group by mois order by mois;";
            $res = SQL($sql);
            while ($row = mysql_fetch_assoc($res))
            {
              print "['".$row["mois"]."', ".$row["total"].", ".$docs_en_ligne[$row["mois"]]."],\n";
              $total_fichiers_global += $row["total"];
            }
          ?>
          ]);

        var options = {
          title: 'Téléchargement de fichiers de thèses, par mois',
          legend: {position: 'in'},
	  vAxes: {
	    0: {logScale: false},
            1: {logScale: false}
	  },
	  series: {
	    0:{targetAxisIndex:0},
	    1:{targetAxisIndex:1},
	    2:{targetAxisIndex:1} }
	  };

        var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
        chart.draw(data, options);
      }
    </script>
    
    
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
    <div class="jumbotron"><span class='chiffre'><?php
      $total_fichiers_global = number_format($total_fichiers_global, 0, ',', ' ');
      print $total_fichiers_global;
    ?></span> thèses téléchargées à Bordeaux 3 depuis le passage à la thèse électronique le 1<sup>er</sup> janvier 2012</div>
    <h1>Mois par mois</h1>
    <div id="chart_div"></div>
    <h1>Thèses les plus consultées</h1>
    <?php
      $mois_precedent = date('Y-m', strtotime('-1 month'));
      print "<h2>Sur le mois passé ($mois_precedent)</h2>";
      $res = SQL("select * from ".STATS_TABLE.", ".RECORDS_TABLE." where ".STATS_TABLE.".record_id = ".RECORDS_TABLE.".id and mois = '$mois_precedent' order by nb desc limit 0,10");
      print "<table class='table'>";
      print "<tr><th>Thèse</th><th>Téléchargements</th></tr>";
      while ($row = mysql_fetch_assoc($res))
      {
        print "<tr>";
        $identifier = $row["identifier"];
        $identifier = str_replace("oai:tel.archives-ouvertes.fr:", "", $identifier);
        print "<td><a href='http://tel.archives-ouvertes.fr/".$identifier."'>".$row["title"]."</a> / ".$row["creator"]." (".$row["date_soutenance"].")</td>";
        print "<td><a href='stats_details.php?id=".$row["id"]."'>".$row["nb"]."</td>";
        print "</tr>";
      }
      print "</table>";
      
      print "<h2>Depuis le 1er janvier 2012</h2>";
      $res = SQL("select * from ".RECORDS_TABLE." order by stats_total desc limit 0,10");
      print "<table class='table'>";
      print "<tr><th>Thèse</th><th>Téléchargements</th></tr>";
      while ($row = mysql_fetch_assoc($res))
      {
        print "<tr>";
        $identifier = $row["identifier"];
        $identifier = str_replace("oai:tel.archives-ouvertes.fr:", "", $identifier);
        print "<td><a href='http://tel.archives-ouvertes.fr/".$identifier."'>".$row["title"]."</a> / ".$row["creator"]." (".$row["date_soutenance"].")</td>";
        print "<td><a href='stats_details.php?id=".$row["id"]."'>".$row["stats_total"]."</td>";
        print "</tr>";
      }
      print "</table>";
    ?>
    <footer>
      Retour au <a href='index.php'>catalogue des thèses</a>.
    </footer>
  </body>
</html>