<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>TaskIt</title>
  <link rel="stylesheet" href="indexStyle.css">
</head>
<body>
  <?php 
    include("connexion.php");
    $con=connect();
    //(toujours) verifier que la connexion est etablie
    if (!$con){
      echo "Probleme connexion à la base";
      exit;
    }

    if (isset($_POST['delProject'])){      
      //suppression des projets cochés
      foreach ($_POST['projectToDelete'] as $elt){
        $sqlProjectDel="delete from projet where identifiant = '".$elt."'";
        $resProjectDel=pg_query($sqlProjectDel);
        //verification que la requete a fonctionnée
        if (!$resProjectDel){
          echo "Probleme lors du lancement de la requête de suppression des projets cochés";
          exit;
        }
      }
    } else if(isset($_POST['addProject'])){
    	$sqlProjectAddID="select max(identifiant) as id from projet";
    	$resProjectAddID=pg_query($sqlProjectAddID);
        //verification que la requete a fonctionnée
        if (!$resProjectAddID){
          echo "Probleme lors du lancement de la requête de récupération de l'identifiant max des projets";
          exit;
        }
        $ligProjectAddID=pg_fetch_array($resProjectAddID);
        $ID = $ligProjectAddID['id']+1;
        
	$sqlProjectAdd="insert into projet(identifiant, acronyme, nom, description, datedeb) values (".$ID.", '".$_POST['projectAcronyme']."', '".$_POST['projectName']."', '".$_POST['projectDescription']."', '".$_POST['projectDebDate']."')";
        $resProjectAdd=pg_query($sqlProjectAdd);
        //verification que la requete a fonctionnée
        if (!$resProjectAdd){
          echo "Probleme lors du lancement de la requête d'ajout d'un projet";
          exit;
        }
    }
  ?>

  <?php include 'header.php'; ?>

  <h2>LISTE DES PROJETS</h2>
  <div class="projectTable">
    <form action="index.php" method="post">
      <input type="hidden" name="delProject">
      <table>
      	<tr><td> <b>Nom du projet</b> </td><td> <b>Responsable</b> </td><td> <b>Date de début</b> </td><td> <b>Date de fin</b> </td><td> <b>Etat</b> </td></tr>
        <?php
          //lancement de la requête
          $sqlProjectList="select identifiant, nom, responsable, datedeb, datefin from projet order by nom";
          $resProjectList=pg_query($sqlProjectList);
            //verification que la requete a fonctionnée
              if (!$resProjectList){
                  echo "Probleme lors du lancement de la requête de la liste de projets";
                  exit;
          }
          //recup ligne
          $ligProjectList=pg_fetch_array($resProjectList);
          //utilisation lignes
          while ($ligProjectList){
                  //affichage
                  if(!$ligProjectList['datefin']) $projectState = 'En cours';
                  else $projectState = 'Terminé';
                  echo "<tr><td> <a href='project.php?projet=".$ligProjectList['nom']."&ID=".$ligProjectList['identifiant']."'>".$ligProjectList['nom']."</a> </td><td> ".$ligProjectList['responsable']." </td><td> ".$ligProjectList['datedeb']." </td><td> ".$ligProjectList['datefin']." </td><td> ".$projectState." </td><td> <input type='checkbox' name='projectToDelete[]' value='".$ligProjectList['identifiant']."'> </td></tr>";
                  //ligne suivante
                  $ligProjectList=pg_fetch_array($resProjectList);
          }
        ?>
      </table>
      <input type="submit" value="Supprimer les projets sélectionnés">
    </form>
  </div>
  
  <div class="addProject">
    <h3>Ajouter un projet</h3>
    <form action="index.php" method="post">
      <input type="hidden" name="addProject">
      <div class="formTop">
        <input type="text" name="projectAcronyme" maxlength="10" placeholder="Acronyme du projet..." required>
        <input type="text" name="projectName" maxlength="30" placeholder="Nom du projet..." required>
      </div>
      <textarea name="projectDescription" rows="5" cols="33" maxlength="155" placeholder="Description du projet..." required></textarea>
      <div class="formBottom">
        <div>
          <label for="projectDebDate">Date de départ du projet :</label>
          <input type="date" name="projectDebDate" required>
        </div>
        <input type="submit" value="Ajouter">
      </div>      
    </form>
  </div>
</body>
</html>
