<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title><?php if(isset($_GET['projet']) && isset($_GET['task']) && isset($_GET['name'])) echo $_GET['name']." | ".$_GET['task']; else exit();?></title>
  <link rel="stylesheet" href="taskStyle.css">
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
      
      if (isset($_POST['delWorker'])){      
      //suppression des personnes cochés de la tâche
      foreach ($_POST['workerToDelete'] as $elt){
        $sqlWorkerDel="delete from personnetâche where employe = '".$elt."' and projet = ".$_GET['projet']." and tâche = ".$_GET['task'];
        $resWorkerDel=pg_query($sqlWorkerDel);
        //verification que la requete a fonctionnée
        if (!$resWorkerDel){
          echo "Probleme lors du lancement de la requête de suppression des personnes cochés";
          exit;
        }
      }
    } elseif(isset($_POST['addWorker'])){
      $isInList = False;     
      $sqlListPartnersMembers="select matricule from personne inner join partenaire on personne.partenaire = partenaire.raisonsoc where partenaire.raisonsoc in (select partenaire from partenaireprojet where projet=".$_GET['projet'].")";
      $resListPartnersMembers=pg_query($sqlListPartnersMembers);
      //verification que la requete a fonctionnée
      if (!$resListPartnersMembers){
        echo "Probleme lors du lancement de la requête de récupération de la liste des matricule des partenaires";
        exit;
      }
      $ligListPartnersMembers=pg_fetch_array($resListPartnersMembers);
      //utilisation lignes
      while ($ligListPartnersMembers && !$isInList){
	if($ligListPartnersMembers['matricule'] == $_POST['workerToAdd']){
	  $isInList = True;
	} 
        //ligne suivante
        $ligListPartnersMembers=pg_fetch_array($resListPartnersMembers);
      }
      if(!$isInList){
      	echo '<script language="javascript">';
        echo "alert(\"Auncun employé n'a ce matricule parmis ceux appartenant aux partenaires de ce projet\")";
        echo '</script>';
      }else{
        $sqlWorkerAdd="insert into personnetâche values ('".$_POST['workerToAdd']."', ".$_GET['task'].", ".$_GET['projet'].", '".$_POST['workerRole']."')";
        $resWorkerAdd=pg_query($sqlWorkerAdd);
        //verification que la requete a fonctionnée
        if (!$resWorkerAdd){
          echo "Probleme lors du lancement de la requête d'ajout d'une personne";
          exit;
        }
      }
    } elseif (isset($_POST['modifManager'])){ 
      $isInList = False;     
      $sqlListPartnersMembers="select matricule from personne inner join partenaire on personne.partenaire = partenaire.raisonsoc where partenaire.raisonsoc in (select partenaire from partenaireprojet where projet=".$_GET['projet'].")";
      $resListPartnersMembers=pg_query($sqlListPartnersMembers);
      //verification que la requete a fonctionnée
      if (!$resListPartnersMembers){
        echo "Probleme lors du lancement de la requête de récupération de la liste des matricule des partenaires";
        exit;
      }
      $ligListPartnersMembers=pg_fetch_array($resListPartnersMembers);
      //utilisation lignes
      while ($ligListPartnersMembers && !$isInList){
	if($ligListPartnersMembers['matricule'] == $_POST['managerMatricule']){
	  $isInList = True;
	} 
        //ligne suivante
        $ligListPartnersMembers=pg_fetch_array($resListPartnersMembers);
      }
      if(!$isInList){
      	echo '<script language="javascript">';
        echo "alert(\"Auncun employé n'a ce matricule parmis ceux appartenant aux partenaires de ce projet\")";
        echo '</script>';
      }else{
        $sqlModifManager="update tâche set responsable = ".$_POST['managerMatricule']." where projet = ".$_GET['projet']." and numero = ".$_GET['task'];
        $resModifManager=pg_query($sqlModifManager);
        //verification que la requete a fonctionnée
        if (!$resModifManager){
          echo "Probleme lors du lancement de la requête de récupération de modification du responsable de la tâche";
          exit;
        }
      }
    } else if(isset($_POST['modifState'])){
      if((($_POST['taskState'] == "terminée") || ($_POST['taskState'] == "arrêtée")) && (!$_POST['taskDateFin'])){
 	echo '<script language="javascript">';
       	echo "alert(\"Votre tâche possède un état qui nécessite une date de fin\")";
       	echo '</script>';
      }else{
        if($_POST['taskDateFin']){
          $sqlTaskModif="update tâche set (datefin, etat) = ('".$_POST['taskDateFin']."', '".$_POST['taskState']."') where projet = ".$_GET['projet']." and numero = ".$_GET['task'];
    	}else{
    	  $sqlTaskModif="update tâche set etat = ".$_POST['taskState']." where projet = ".$_GET['projet']." and numero = ".$_GET['task'];
    	}
    	$resTaskModif=pg_query($sqlTaskModif);
        //verification que la requete a fonctionnée
        if (!$resTaskModif){
          echo "Probleme lors du lancement de la requête de modification de l'état d'une tâche";
          exit;
        }
      }
    }
  ?>    

  <?php include 'header.php'; ?>
  
  <h2>
    <?php 
      if(isset($_GET['projet']) && isset($_GET['task']) && isset($_GET['name'])){
      	echo "<a href='project.php?projet=".$_GET['name']."&ID=".$_GET['projet']."'>▲</a>";
        echo $_GET['name']." | ".$_GET['task']; 
      }else exit();
      
    ?>
  </h2>
  
  
  <h3>Description</h3>
  <?php
    $sqlTaskDescription = "select description from tâche where projet = ".$_GET['projet']." and numero=".$_GET['task'];
    $resTaskDescription = pg_query( $sqlTaskDescription) ;
    if (!$resTaskDescription) { 
	echo "Probleme lors du lancement de la requète";
	exit;
    }
    $ligTaskDescription = pg_fetch_array ($resTaskDescription);
    echo "<p class='taskDescription'>".$ligTaskDescription['description']."</p>";
  ?>
  
  <h3>Gestion du responsable</h3>
  <div class="responsableTable">
    <p>Responsable actuel:</p>
    <table>
      <?php
        //lancement de la requête
        $sqlManager="select personne.partenaire, personne.matricule, personne.nom, personne.prenom, personne.numerotel, personne.email from personne inner join tâche on personne.matricule = tâche.responsable where tâche.projet = ".$_GET['projet']." and tâche.numero = ".$_GET['task'];
        $resManager=pg_query($sqlManager);
        //verification que la requete a fonctionnée
        if (!$resManager){
                echo "Probleme lors du lancement de la requête de récup du responsable";
                exit;
        }
        //recup ligne
        $ligManager=pg_fetch_array($resManager);
        //utilisation ligne
        if(!$ligManager){
        	echo "A définir";
        }else{
        	echo "<tr><td> ".$ligManager['partenaire']." </td><td> ".$ligManager['matricule']." </td><td> ".$ligManager['nom']." </td><td> ".$ligManager['prenom']." </td><td> 0".$ligManager['numerotel']." </td><td> ".$ligManager['email']." </td></tr>";
        }
      ?>
    </table>
  </div>
  <div class="modifManager">
    <form action="" method="post">
      <input type="hidden" name="modifManager">
      <label for="managerMatricule">Modifier le Responsable: </label>
      <input type="number" name="managerMatricule" placeholder="Matricule du nouveau responsable..."> 
      <input type="submit" value="Modifier">   
    </form>
  </div>
  
  <h3>Gestion des employés travaillant sur la tâche</h3>
  <div class="workerTable">
    <form action="" method="post">
      <input type="hidden" name="delWorker">
      <table>
      	<tr><td> <b>Employé</b> </td><td> <b>Etablissement</b> </td><td> <b>Rôle</b> </td></tr>
        <?php
          //lancement de la requête
          $sqlWorkerList="select employe, partenaire, role from personnetâche inner join personne on personnetâche.employe = personne.matricule where projet = ".$_GET['projet']." and tâche = ".$_GET['task']." order by employe";
          $resWorkerList=pg_query($sqlWorkerList);
            //verification que la requete a fonctionnée
              if (!$resWorkerList){
                  echo "Probleme lors du lancement de la requête de la liste des employés travaillant sur la tâche";
                  exit;
          }
          //recup ligne
          $ligWorkerList=pg_fetch_array($resWorkerList);
          //utilisation lignes
          while ($ligWorkerList){
                  //affichage
                  echo "<tr><td> ".$ligWorkerList['employe']." </td><td> ".$ligWorkerList['partenaire']." </td><td> ".$ligWorkerList['role']." </td><td> <input type='checkbox' name='workerToDelete[]' value=".$ligWorkerList['employe']."> </td></tr>";
	  
                  //ligne suivante
                  $ligWorkerList=pg_fetch_array($resWorkerList);
          }
        ?>
      </table>
      <input type="submit" value="Supprimer les personnes sélectionnés">
    </form>
  </div>
  
  <div class="addWorker">
    <form action="" method="post">
      <input type="hidden" name="addWorker">
      <label for="workerToAdd">Ajouter un employé: </label>
      <input type="number" name="workerToAdd" placeholder="matricule..." required>
      <input type="text" name="workerRole" placeholder="role..." required>
      <input type="submit" value="Ajouter">   
    </form>
  </div>
  
  <div class="modifState">
    <h3>Modifier l'état de la tâche</h3>
    <form action="" method="post">
      <input type="hidden" name="modifState">
      <input type="date" name="taskDateFin">
      <select name="taskState" required>
        <option value="" disabled selected>Etat...</option>
        <option>non démarrée</option>
        <option>en cours</option>
        <option>terminée</option>
        <option>arrêtée</option>
      </select>
      <input type="submit" value="Modifier">     
    </form>
  </div>
  
</body>
</html>
