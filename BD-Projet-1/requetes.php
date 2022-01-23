<h2> Nom du projet </h2>
<?php
// description
//SELECT description FROM projet WHERE identifiant='IdProjet' ;


// Liste des partenaires associés (raison soc, adresse, type, contact)

$cmdePartenaire = "SELECT raisonsoc, adresse, type, contact FROM partenaire NATURAL JOIN partenaireprojet WHERE partenaireprojet.projet='IdProjet'" ;
$resulPartenaire = pg_query( $cmdePartenaire) ;

if (!$resulPartenaire) { 
	echo "Probleme lors du lancement de la requète";
	exit;

}
$lignePart = pg_fetch_array ($resulPartenaire);
while ($lignePart )
	echo "<tr> <td>".$lignePart['raisonsoc']."</td>
	   <td>".$lignePart['adresse']."</td>
	   <td>".$lignePart['type']."</td>
	   <td>".$lignePart['contact']."</td>
	  </tr>"
	  
// liste des tâches d'un projet ( numero, responsable, etat)

$cmdeListeTache = "SELECT numero, responsable, etat FROM tâche WHERE projet='IdProjet'" ;
$resulListeTache = pg_query( $cmdeListeTache) ;
if (!$resulListeTache) { 
	echo "Probleme lors du lancement de la requète";
	exit;
}

$ligneTache = pg_fetch_array ($resulListeTache);

while ($ligneTache )
	echo "<tr> <td>".$ligneTache['numero']."</td>
	   <td>".$ligneTache['responsable']."</td>
	   <td>".$ligneTache['etat']."</td>
	  </tr>"
	  
// description de la tâche

$cmdeDesTache ="SELECT description FROM tâche WHERE numero='IdTache'";
$resulDescrTache = pg_query( $cmdeDescrTache) ;
if (!$resulDescrTache) { 
	echo "Probleme lors du lancement de la requète";
	exit;
}


// Liste des intervenants pour une tâche ( matricule, role dans tâche, etablissement
$cmdeIntervenantTache = "SELECT DISTINCT employe, role, partenaire FROM personnetâche INNER JOIN personne ON personne.matricule=personnetâche.employe WHERE personnetâche.tâche='IdTache' ";
$resulIntervenantTache = pg_query( $cmdeIntervenantTache) ;
if (!$resulIntervenantTache) { 
	echo "Probleme lors du lancement de la requète";
	exit;
}
$ligneIntervenantTache = pg_fetch_array ($resulIntervenantTache) ;
while ($ligneIntervenantTache )
	echo "<tr> <td>".$ligneIntervenantTache['employe']."</td>
	   <td>".$ligneIntervenantTache['role']."</td>
	   <td>".$ligneIntervenantTache['partenaire']."</td>
	  </tr>"
?>



