<?php
  require_once dirname(__FILE__).'/config.php';
  if ( ($host == "") or ($user == "") or ($passe == "" ))
  {
    print "Informations de connexion à la base manquantes<br/>Éditez le fichier include/config.php";
    exit;
  }
  // Cette page gère la connexion à la base de données (et c'est tout !)
  mysql_connect($host,$user,$passe);
  mysql_select_db($base);
  mysql_query("SET NAMES utf8");
  
  // On va vérifier que la base de données est bien configurée
  $sql_test = "SELECT table_name FROM information_schema.tables WHERE table_schema = '$base' AND (table_name = '".STATS_TABLE."' or table_name = '".RECORDS_TABLE."')";
  $res = SQL($sql_test);
  if (mysql_numrows($res) == 0)
  {
    print "Base non initialis&eacute;e ? (charger db_structure.sql et v&eacute;rifier config.php)<br/>";
    exit;
  }
  elseif (mysql_numrows($res) != 2)
  {
    print "Problème de configuration de la base, manque une table (".STATS_TABLE." ou ".RECORDS_TABLE.")";
    exit;
  }
  

  
  // Pour ne pas avoir de warnings dans les dernières version de PHP
  date_default_timezone_set("Europe/Paris");
  

  function SQL($req)
  {
    $result = mysql_query($req);
    if (!$result) {
      echo "Impossible d'exécuter la requête ($req) dans la base : " . mysql_error();
      exit;
    }
    return $result;
  }

  function getParam($code)
  {
    if (isset($_GET[$code]))
    {
      return $_GET[$code];
    }
    elseif (isset($_POST[$code]))
    {
      return $_POST[$code];
    }
    else
    {
      return "";
    }
  }

  function stats_init_session()
  {
    global $ch;
    global $hal_username;
    global $hal_password;
    
    $url_tel = "http://tel.archives-ouvertes.fr/index.php";
    // 1. Récupération de la page principale
    $headers[] = "Accept: */*";
    $headers[] = "Connection: Keep-Alive";
    curl_setopt($ch, CURLOPT_HTTPHEADER,  $headers);
    curl_setopt($ch, CURLOPT_HEADER,  1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:25.0) Gecko/20100101 Firefox/25.0');
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
    curl_setopt($ch, CURLOPT_URL, $url_tel );
    $content = get_curl_local();
    // 2. Login
    $fields = getFormFields($content);
    $fields['uname'] = $hal_username;
    $fields['password'] = $hal_password;
    $fields["submit"] = "1";
    $fields["action_todo"] = "login";
    $POSTFIELDS = http_build_query($fields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($ch, CURLOPT_URL, $url_tel); 
    curl_setopt($ch, CURLOPT_POST, 1); 
    curl_setopt($ch, CURLOPT_POSTFIELDS, $POSTFIELDS);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    $content = curl_exec($ch);
    
    // TODO : vérifier que le login s'est bien déroulée
    return $fields["halsid"];
  }

  function stats_get_from_tel($identifier, $local_id)
  {
    global $ch;
    global $halsid;
    global $code_tampon;
    
    // On va chercher depuis quand reprendre les stats
    $sql2 = "select max(mois) as mois_max from ".STATS_TABLE." where record_id = ".$local_id;
    $res2 = SQL($sql2);
    $row2 = mysql_fetch_assoc($res2);
    $mois_min = $row2["mois_max"];
    
    if ($mois_min == "")
    {
      $mois_min = MOIS_DEBUT_STAR;
    }
    
    $url_stats = "http://tel.archives-ouvertes.fr/stat_2011/consultationArticles/graphiqueNbCons.php";
    $param_stats = array();
    $param_stats["dateD"] = $mois_min."-01";
    $param_stats["dateF"] = date("Y-m-d");
    $param_stats["halsid"] = $halsid;
    $param_stats["id"] = $identifier;
    $param_stats["pas"] = "mois";
    $param_stats["tampid"] = $code_tampon;
    $param_stats["typeForm"] = "1";
    $param_stats["types"] = "'F'";
    $param_stats["action"] = "";

    $post_stats = http_build_query($param_stats);

    curl_setopt($ch, CURLOPT_URL, $url_stats); 
    curl_setopt($ch, CURLOPT_POST, 1); 
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_stats);
    $content = curl_exec($ch);
    
    // On va analyser la chaîne content
    $stats_par_mois = array();
    if (preg_match_all("/<set label='([^']*)' value='([^']*)'/isU", $content, $matches))
    {
      foreach ($matches[1] as $key => $mois)
      {
        $stats_par_mois[$mois] = $matches[2][$key];
      }
    }

    // On va aller stocker tout ça dans la BDD
    foreach ($stats_par_mois as $mois => $nb)
    {
      $res2 = SQL("select id from ".STATS_TABLE." where record_id = $local_id and mois = '$mois'");
      if (mysql_numrows($res2) == 1)
      {
        $row2 = mysql_fetch_assoc($res2);
        $id = $row2["id"];
        SQL("UPDATE ".STATS_TABLE." set `nb`= $nb where id = $id");
      }
      else
      {
        SQL("insert into ".STATS_TABLE." (`record_id`,`mois`,`nb`) values ($local_id, '$mois', $nb)");
      }
    }
  }
 
  function getFormFields($data)
  {
      if (preg_match('/(<form.*?<\/form>)/is', $data, $matches)) {
          $inputs = getInputs($matches[1]);
  
          return $inputs;
      } else {
          die('didnt find login form');
      }
  }
  
  function getInputs($form)
  {
    $inputs = array();
    
    $elements = preg_match_all('/(<input[^>]+>)/is', $form, $matches);
    
    if ($elements > 0)
    {
      for($i = 0; $i < $elements; $i++)
      {
        $el = preg_replace('/\s{2,}/', ' ', $matches[1][$i]);
        
        if (preg_match('/name=(?:["\'])?([^"\'\s]*)/i', $el, $name))
        {
          $name  = $name[1];
          $value = '';
          
          if (preg_match('/value=(?:["\'])?([^"\'\s]*)/i', $el, $value))
          {
            $value = $value[1];
          }
          
          $inputs[$name] = $value;
        }
      }
    }
    return $inputs;
  }
  
  function get_curl_local()
  {
    global $ch;
    $content = curl_exec($ch);
    while (curl_error($ch))
    {
      print "Erreur : ".curl_errno($ch)." [".curl_error($ch)."]\n";
      $content = curl_exec($ch);
    }
    return $content;
  }


?>
