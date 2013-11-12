<?php
  require_once dirname(__FILE__).'/../include/utils.php';
  // ********************************************************************************************** //
  // Ce script va aller interroger le serveur OAI de HAL pour récupérer les métadonnées des thèses  //
  // ********************************************************************************************** //

  // Variable à mettre à 1 pour le chargement initial
  $get_all = 1;
  
  $datestamp = "";
  // On va commencer par regarder la thèse la plus récente dans la base
  if ($get_all == 0)
  {
    $res = SQL("select max(datestamp) as max from ".RECORDS_TABLE);
    $row = mysql_fetch_assoc($res);
    $datestamp = $row["max"];
    print "Import delta : récupération après $datestamp\n";
  }
  
  // On va aller appeler la page
  $resumptionToken = "-1";
  $nb = 1;
  
  while ($resumptionToken != "")
  {
    $url_oai = "";
    if ($resumptionToken == "-1")
    {
      if ($datestamp != "")
      {
        $url_oai = "http://tel.archives-ouvertes.fr/oai/oai.php?verb=ListRecords&set=".$code_tampon."&metadataPrefix=oai_tel&from=$datestamp"; 
      }
      else
      {
        $url_oai = "http://tel.archives-ouvertes.fr/oai/oai.php?verb=ListRecords&set=".$code_tampon."&metadataPrefix=oai_tel";        

      }
    }
    else
    {
      $url_oai = "http://tel.archives-ouvertes.fr/oai/oai.php?verb=ListRecords&resumptionToken=".$resumptionToken;
    }

    // On utilise curl avec un timeout de 60 secondes pour éviter les réponses lentes du serveur
    $ch = curl_init($url_oai);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    $xml_oai_s = curl_exec($ch);
    
    
    if (!curl_error($ch))
    {
      $xml_oai_s = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2_$3", $xml_oai_s);
      $xml_oai = simplexml_load_string($xml_oai_s);
      
      foreach ($xml_oai->{'ListRecords'}->{'record'} as $record)
      {
        $identifier = $record->{'header'}->{'identifier'};
        $datestamp = $record->{'header'}->{'datestamp'};
        $title = $record->{'metadata'}->{'oai_tel_tel'}->{'tel_titre'};
        
        $tel_authors = $record->{'metadata'}->{'oai_tel_tel'}->{'tel_authors'};
        if (sizeof($tel_authors) != 1)
        {
          print "Trop d'auteurs ???";
          exit;
        }
        else
        {
          $creator = $tel_authors->{'tel_author'}->{'tel_lastname'}.", ".$tel_authors->{'tel_author'}->{'tel_firstname'};
        }

        $date_soutenance = $record->{'metadata'}->{'oai_tel_tel'}->{'tel_defencedate'};
        $date_en_ligne = $record->{'metadata'}->{'oai_tel_tel'}->{'tel_submission_date'};
          
        // On va stocker la thèse dans la base de données si elle n'existe pas
        $res_exists = SQL("select * from ".RECORDS_TABLE." where identifier = '$identifier';");
        if (mysql_numrows($res_exists) == 0)
        {
          // On va stocker la notice
          $sql = "insert into ".RECORDS_TABLE." (`identifier`,`title`,`creator`,`date_soutenance`,`date_en_ligne`,`datestamp`,`xml`) values (";
          $sql .= "'".addslashes($identifier)."', ";
          $sql .= "'".addslashes($title)."', ";
          $sql .= "'".addslashes($creator)."', ";
          $sql .= "'".addslashes($date_soutenance)."', ";
          $sql .= "'".addslashes($date_en_ligne)."', ";
          $sql .= "'".addslashes($datestamp)."', ";
          $sql .= "'".addslashes($record->asXML())."');";
          SQL($sql);
          print "#$nb : $identifier [".substr($title, 0, 30)."] :: AJOUT\n";
        }
        else
        {
          print "#$nb : $identifier [".substr($title, 0, 30)."] :: DEJA PRESENT\n";
        }
        $nb++;
      }

      $resumptionToken = $xml_oai->{'ListRecords'}->{'resumptionToken'};      
    }
    else
    {
      print "Erreur : ".curl_errno($ch)." [".curl_error($ch)."]\n";
    }
  }
?>
