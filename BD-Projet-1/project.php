<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title><?php if(isset($_GET['projet']) && isset($_GET['ID'])) echo $_GET['projet']; else exit();?></title>
  <link rel="stylesheet" href="projectStyle.css">
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
      
      if (isset($_POST['delPartner'])){      
        //suppression des partenaires cochés du projet
        foreach ($_POST['partnerToDelete'] as $elt){
          $isUndeletable = False;       
          $sqlListUndeletable="select distinct partenaire from partenaireprojet natural join personne where personne.matricule in (select responsable from projet union select responsable from tâche)";
          $resListUndeletable=pg_query($sqlListUndeletable);
          //verification que la requete a fonctionnée
          if (!$resListUndeletable){
            echo "Probleme lors du lancement de la requête de récupération de la liste des partenaires qui ont des employés à responsabilités";
            exit;
          }
          $ligListUndeletable=pg_fetch_array($resListUndeletable);
          //utilisation lignes
          while ($ligListUndeletable && !$isUndeletable){
	    if($ligListUndeletable['partenaire'] == $elt){
	      $isUndeletable = True;
	    } 
            //ligne suivante
            $ligListUndeletable=pg_fetch_array($resListUndeletable);
          }
          if($isUndeletable){
             echo '<script language="javascript">';
             echo "alert(\"Le partenaire ayant pour raison social ".$elt." ne peut pas être retiré de ce projet car certains de ses employés ont des responsabilités dans le projet, attribuez ces responsabilités à d'autres personnes si vous souhaitez le retirer\")";
             echo '</script>';
          }else{
            $sqlPartnerDel="delete from partenaireprojet where partenaire = '".$elt."' and projet = ".$_GET['ID'];
            $resPartnerDel=pg_query($sqlPartnerDel);
            //verification que la requete a fonctionnée
            if (!$resPartnerDel){
              echo "Probleme lors du lancement de la requête de suppression des partenaires cochés";
              exit;
            }
          } 
        }
      } else if(isset($_POST['addPartner'])){  
	$sqlPartnerAdd="insert into partenaireprojet values ('".$_POST['partnerToAdd']."', ".$_GET['ID'].")";
        $resPartnerAdd=pg_query($sqlPartnerAdd);
        //verification que la requete a fonctionnée
        if (!$resPartnerAdd){
          echo "Probleme lors du lancement de la requête d'ajout d'un partenaire";
          exit;
        }
    } elseif (isset($_POST['modifManager'])){ 
      $isInList = False;     
      $sqlListPartnersMembers="select matricule from personne inner join partenaire on personne.partenaire = partenaire.raisonsoc where partenaire.raisonsoc in (select partenaire from partenaireprojet where projet=".$_GET['ID'].")";
      $resListPartnersMembers=pg_query($sqlListPartnersMembers);
      //verification que la requete a fonctionnée
      if (!$resListPartnersMembers){
        echo "Probleme lors du lancement de la requête de récupération de la liste des matricule d'un partenaire";
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
        $sqlModifManager="update projet set responsable = ".$_POST['managerMatricule']." where identifiant = ".$_GET['ID'];
        $resModifManager=pg_query($sqlModifManager);
        //verification que la requete a fonctionnée
        if (!$resModifManager){
          echo "Probleme lors du lancement de la requête de récupération de la liste des matricule d'un partenaire";
          exit;
        }
      }
    } else if(isset($_POST['addTask'])){ 
      $sqlNumTaskTest="select numero from tâche where projet = ".$_GET['ID'];
      $resNumTaskTest=pg_query($sqlNumTaskTest);
      //verification que la requete a fonctionnée
      if (!$resNumTaskTest){
        echo "Probleme lors du lancement de la requête de récupération des numéros de tâches existants";
        exit;
      }
      $isExistNumTask = False;
      $ligNumTaskTest=pg_fetch_array($resNumTaskTest);
      while ($ligNumTaskTest && !$isExistNumTask){    
        if($ligNumTaskTest['numero'] == $_POST['taskNum']){
	  $isExistNumTask = True;
	} 
        //ligne suivante
        $ligNumTaskTest=pg_fetch_array($resNumTaskTest);
      }
      if($isExistNumTask){
        echo '<script language="javascript">';
        echo "alert(\"Une tâche de ce projet possède déjà ce numéro\")";
        echo '</script>';
      }else{
        if((($_POST['taskState'] == "terminée") || ($_POST['taskState'] == "arrêtée")) && (!$_POST['taskDateFin'])){
    		echo '<script language="javascript">';
        	echo "alert(\"Votre tâche possède un état qui nécessite une date de fin\")";
        	echo '</script>';
    	}else{
    	  if($_POST['taskDateFin']){
    	    $sqlTaskAdd="insert into tâche (numero, description, datedeb, datefin, etat, projet) values (".$_POST['taskNum'].", '".$_POST['taskDescription']."', '".$_POST['taskDateDeb']."', '".$_POST['taskDateFin']."', '".$_POST['taskState']."', ".$_GET['ID'].")";
    	  }else{
    	    $sqlTaskAdd="insert into tâche (numero, description, datedeb, etat, projet)  values (".$_POST['taskNum'].", '".$_POST['taskDescription']."', '".$_POST['taskDateDeb']."', '".$_POST['taskState']."', ".$_GET['ID'].")";
    	  }
    	  $resTaskAdd=pg_query($sqlTaskAdd);
          //verification que la requete a fonctionnée
          if (!$resTaskAdd){
            echo "Probleme lors du lancement de la requête d'ajout d'une tâche";
            exit;
          }
    	}
      }        
    } else if(isset($_POST['delTask'])){ 
      //suppression des partenaires cochés du projet
      foreach ($_POST['taskToDelete'] as $elt){
        $sqlTaskDel="delete from tâche where numero = ".$elt." and projet = ".$_GET['ID'];
        $resTaskDel=pg_query($sqlTaskDel);
        //verification que la requete a fonctionnée
        if (!$resTaskDel){
          echo "Probleme lors du lancement de la requête de suppression des tâches cochés";
          exit;
        }
      }
    } else if(isset($_POST['defEnd'])){ 
      //modif
      $sqlDefEnd="update projet set datefin = '".$_POST['dateFin']."' where identifiant = ".$_GET['ID'];
      $resDefEnd=pg_query($sqlDefEnd);
      //verification que la requete a fonctionnée
      if (!$resDefEnd){
        echo "Probleme lors du lancement de la requête d'ajout de la date de fin";
        exit;
      }
    }
  ?>    

  <?php include 'header.php'; ?>
  
  <h2><?php if(isset($_GET['projet']) && isset($_GET['ID'])) echo $_GET['projet'];?></h2>
  
  
  <h3>Description</h3>
  <?php
    $sqlProjectDescription = "select description from projet where identifiant=".$_GET['ID'];
    $resProjectDescription = pg_query( $sqlProjectDescription) ;
    if (!$resProjectDescription) { 
	echo "Probleme lors du lancement de la requète";
	exit;
    }
    $ligProjectDescription = pg_fetch_array ($resProjectDescription);
    echo "<p class='projectDescription'>".$ligProjectDescription['description']."</p>";
  ?>
  
  <h3>Gestion du responsable</h3>
  <div class="responsableTable">
    <p>Responsable actuel:</p>
    <table>
      <?php
        //lancement de la requête
        $sqlManager="select personne.partenaire, personne.matricule, personne.nom, personne.prenom, personne.numerotel, personne.email from personne inner join projet on personne.matricule = projet.responsable where projet.identifiant = ".$_GET['ID'];
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
  
  <h3>Gestion des partenaires</h3>
  <div class="partnerTable">
    <form action="" method="post">
      <input type="hidden" name="delPartner">
      <table>
      	<tr><td> <b>Raison Sociale</b> </td><td> <b>Adresse</b> </td><td> <b>Type</b> </td><td> <b>Contact</b> </td></tr>
        <?php
          //lancement de la requête
          $sqlPartnerList="select raisonsoc, activite, adresse, type, contact from partenaire inner join partenaireprojet on partenaire.raisonsoc = partenaireprojet.partenaire where projet = ".$_GET['ID']." order by raisonsoc";
          $resPartnerList=pg_query($sqlPartnerList);
            //verification que la requete a fonctionnée
              if (!$resPartnerList){
                  echo "Probleme lors du lancement de la requête de la liste des partenaires";
                  exit;
          }
          //recup ligne
          $ligPartnerList=pg_fetch_array($resPartnerList);
          //utilisation lignes
          while ($ligPartnerList){
                  //affichage
                  echo "<tr><td title='".$ligPartnerList['activite']."'> ".$ligPartnerList['raisonsoc']." </td><td> ".$ligPartnerList['adresse']." </td><td> ".$ligPartnerList['type']." </td><td> ".$ligPartnerList['contact']." </td><td> <input type='checkbox' name='partnerToDelete[]' value='".$ligPartnerList['raisonsoc']."'> </td></tr>";
	  
                  //ligne suivante
                  $ligPartnerList=pg_fetch_array($resPartnerList);
          }
        ?>
      </table>
      <input type="submit" value="Supprimer les partenaires sélectionnés">
    </form>
  </div>
  
  <div class="addPartner">
    <form action="" method="post">
      <input type="hidden" name="addPartner">
      <label for="partnerToAdd">Ajouter un partenaire: </label>
      <select name="partnerToAdd" required>
        <option value="" disabled selected>Raison Sociale du partenaire...</option>
        <?php
          //lancement de la requête
          $sqlPartnerList2="select raisonsoc from partenaire where raisonsoc not in (select partenaire from partenaireprojet where projet=".$_GET['ID'].") order by raisonsoc";
          $resPartnerList2=pg_query($sqlPartnerList2);
          //verification que la requete a fonctionnée
          if (!$resPartnerList2){
            echo "Probleme lors du lancement de la requête de la liste des partenaires pour modifier le contact";
            exit;
          }
          //recup ligne
          $ligPartnerList2=pg_fetch_array($resPartnerList2);
          //utilisation lignes
          while ($ligPartnerList2){
                  //affichage
                  echo "<option>".$ligPartnerList2['raisonsoc']."</option>";
	  
                  //ligne suivante
                  $ligPartnerList2=pg_fetch_array($resPartnerList2);
          }
        ?>
      </select>
      <input type="submit" value="Ajouter">   
    </form>
  </div>
  
  <h3>Gestion des tâches</h3>
  <div class="taskTable">
    <form action="" method="post">
      <input type="hidden" name="delTask">
      <table>
      	<tr><td> <b>Numéro</b> </td><td> <b>Responsable</b> </td><td> <b>Date de début</b> </td><td> <b>Date de fin</b> </td><td> <b>Etat</b> </td></tr>
        <?php
          //lancement de la requête
          $sqlTaskList="select numero, responsable, datedeb, datefin, etat from tâche where projet = ".$_GET['ID']." order by numero";
          $resTaskList=pg_query($sqlTaskList);
            //verification que la requete a fonctionnée
              if (!$resTaskList){
                  echo "Probleme lors du lancement de la requête de la liste des partenaires";
                  exit;
          }
          //recup ligne
          $ligTaskList=pg_fetch_array($resTaskList);
          //utilisation lignes
          while ($ligTaskList){
                  //affichage
                  echo "<tr><td> <a href='task.php?task=".$ligTaskList['numero']."&projet=".$_GET['ID']."&name=".$_GET['projet']."'>".$ligTaskList['numero']."</a> </td><td> ".$ligTaskList['responsable']." </td><td> ".$ligTaskList['datedeb']." </td><td> ".$ligTaskList['datefin']." </td><td> ".$ligTaskList['etat']." </td><td> <input type='checkbox' name='taskToDelete[]' value='".$ligTaskList['numero']."'> </td></tr>";
	  
                  //ligne suivante
                  $ligTaskList=pg_fetch_array($resTaskList);
          }
        ?>
      </table>
      <input type="submit" value="Supprimer les tâches sélectionnés">
    </form>
  </div>
  
  <div class="addTask">
    <p>Ajouter une tâche:</p>
    <form action="" method="post">
      <input type="hidden" name="addTask">
      <div class="formTop">
        <input type="number" name="taskNum" placeholder="Numéro..." required>
        <div>
      	  <label for="taskDateDeb">Date de début:</label>
      	  <input type="date" name="taskDateDeb" required>
      	</div>
      </div>
      <textarea name="taskDescription" rows="5" cols="33" maxlength="155" placeholder="Description..." required></textarea>
      <div class="formBottom">
      	<div>
      	  <label for="taskDateFin">Date de fin:</label>
      	  <input type="date" name="taskDateFin">
      	</div>
      	<select name="taskState" required>
      	  <option value="" disabled selected>Etat...</option>
          <option>non démarrée</option>
          <option>en cours</option>
          <option>terminée</option>
          <option>arrêtée</option>
        </select>
        <input type="submit" value="Ajouter">
      </div>      
    </form>
  </div>
  
  <div class="defEnd">
    <h3>Déclarer la fin du projet</h3>
    <form action="" method="post">
      <input type="hidden" name="defEnd">
      <input type="date" name="dateFin" required>
      <input type="submit" value="Déclarer">     
    </form>
  </div>
</body>
</html>
