<?php
  require_once("../include/utils.php");
  
  // ************************************************************************ //
  // Ce script va aller chercher les statistiques de consultation de n thèses //
  // ************************************************************************ //

  $ch = curl_init();
  $halsid = stats_init_session();
  $res = SQL("select * from ".RECORDS_TABLE." order by last_checked asc limit 0,$nb_these_par_appel");
  // $res = SQL("select * from ".RECORDS_TABLE." where id = 102");
  while ($row = mysql_fetch_assoc($res))
  {
    $identifier = $row["identifier"];
    $identifier = str_replace("oai:tel.archives-ouvertes.fr:", "", $identifier);
    
    // On va regarder le mois le plus récent dans la base des stats
    print "Récupération stats de $identifier\n";
    
    // On va récupérer les stats de l'enregistrement et les stocker dans la base de données
    stats_get_from_tel($identifier, $row["id"]);
    
    // Si on a fait les stats de la thèse, on va mettre à jour son last_checked
    SQL("update ".RECORDS_TABLE." set stats_total = (select sum(nb) from ".STATS_TABLE." where record_id = ".$row["id"]."), last_checked=now() where id = ".$row["id"]);
    sleep($sleep_entre_appels); // On laisse n secondes entre deux interrogations
  }
 
?>
