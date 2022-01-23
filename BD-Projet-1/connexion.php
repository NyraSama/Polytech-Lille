<?php
	function connect(){
		$con=pg_connect("host=serveur-etu.polytech-lille.fr user=rdebouvr port=5432 password=postgres dbname=rdebouvrcbaldacctaskit") ;
		return $con;
	}
?>
