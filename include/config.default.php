<?php
	ini_set("display_errors", 1);
	$titre_page = "";
	$base_url   = "";
	$logo       = $base_url."img/logo.png";
	$etab_star  = "";
	$par_page   = 10;
	
	// Mettre 1 à gestion_stats pour afficher les statistiques
	$gestion_stats = 1;
	
	// Configuration HAL-TEL
	$code_tampon  = "";
	$nb_these_par_appel = 100;
	$sleep_entre_appels = 1; // Durée en seconde à attendre entre deux appels au serveur, ne pas mettre trop bas pour éviter surcharge serveur ?
	$hal_username = "";
	$hal_password = "";
	define("MOIS_DEBUT_STAR", "2012-01-01"); # Mois à partir duquel prendre en compte les stats dans STAR
	
	// Configuration base de données
	$host				  = "";
	$user				  = "";
	$passe			  = "";
	$base				  = "";
	
	// Configuration générale, ne pas toucher en règle générale
	define("RECORDS_TABLE", "tel_records");
	define("STATS_TABLE", "tel_records_stats");
?>