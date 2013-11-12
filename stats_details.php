<!DOCTYPE html>
<?php
  require_once("include/utils.php");
  if ($gestion_stats == 0)
  {
    print "Statistiques non g&eacute;r&eacute;es sur cette instance.";
    exit;
  }
  $id = getParam("id");
  if ($id == "")
  {
    print "Manque l'id";
    exit;
  }
  
  $res = SQL("select * from ".RECORDS_TABLE." where id = $id");
  $row = mysql_fetch_assoc($res);
  $title = $row["title"];
  $identifier = $row["identifier"];
  $creator = $row["creator"];
  $date_soutenance = $row["date_soutenance"];
  
  $refresh_stats = getParam("refresh_stats");
  if ($refresh_stats == "ok")
  {
    // On va aller mettre à jour les stats depuis le serveur
    $ch = curl_init();
    $halsid = stats_init_session();
    $identifier = str_replace("oai:tel.archives-ouvertes.fr:", "", $identifier);
    stats_get_from_tel($identifier, $row["id"]);
    
    // Si on a fait les stats de la thèse, on va mettre à jour son last_checked
    SQL("update ".RECORDS_TABLE." set stats_total = (select sum(nb) from ".STATS_TABLE." where record_id = ".$id."), last_checked=now() where id = ".$id);
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
      
      .isbd
      {
	font-size:0.8em;
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
          ['Mois', 'Téléchargements'],
          <?php
            $total_fichiers_global = 0;
            $mois_courant = date("Y-m");
            $res = SQL("select mois, nb from ".STATS_TABLE." where record_id = $id order by mois;");
            while ($row = mysql_fetch_assoc($res))
            {
              print "['".$row["mois"]."', ".$row["nb"]."],\n";
              $total_fichiers_global += $row["nb"];
            }
          ?>
          ]);

        var options = {
          title: 'Téléchargement de fichiers de thèses, par mois',
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
      // print $total_fichiers_global;
      print "<div class='isbd'><i>".$title."</i> / ".$creator. " ($date_soutenance)</div>$total_fichiers_global téléchargements au total";
    ?>
    </div>
    <h1>Évolution des téléchargements</h1>
    <div id="chart_div"></div>
    <div style='text-align:center; font-size:0.9em'><a href='stats_details.php?id=<?php echo $id; ?>&refresh_stats=ok'>(mettre à jour les statistiques depuis le serveur)</a></div>
    <footer>
      <hr/>
      Retour au <a href='stats.php'>statistiques</a>.
    </footer>
  </body>
</html>