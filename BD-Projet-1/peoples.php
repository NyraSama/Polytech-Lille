<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>TaskIt</title>
  <link rel="stylesheet" href="peoplesStyle.css">
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
      
      if (isset($_POST['delPeople'])){            
        //suppression des projets cochés
        foreach ($_POST['peopleToDelete'] as $elt){
          $isUndeletable = False;       
          $sqlListUndeletable="select projet.responsable from projet union select tâche.responsable from tâche union select partenaire.contact from partenaire";
          $resListUndeletable=pg_query($sqlListUndeletable);
          //verification que la requete a fonctionnée
          if (!$resListUndeletable){
            echo "Probleme lors du lancement de la requête de récupération de la liste des matricule qui sont contact ou responsable";
            exit;
          }
          $ligListUndeletable=pg_fetch_array($resListUndeletable);
          //utilisation lignes
          while ($ligListUndeletable && !$isUndeletable){
	    if($ligListUndeletable['responsable'] == $elt){
	      $isUndeletable = True;
	    } 
            //ligne suivante
            $ligListUndeletable=pg_fetch_array($resListUndeletable);
          }
          if($isUndeletable){
             echo '<script language="javascript">';
             echo "alert(\"La personne ayant le matricule ".$elt." ne peut pas être supprimée car elle possède un rôle de responsable ou de contact, attribuez une autre personne à ce rôle si vous souhaitez la supprimer\")";
             echo '</script>';
             //echo "La personne ayant le matricule ".$elt." ne peut pas être supprimée car elle est contact d'un partenaire, modifier ce contact si vous souhaitez la supprimer";
          }else{
            $sqlPeopleDel="delete from personne where matricule = '".$elt."'";
            $resPeopleDel=pg_query($sqlPeopleDel);
            //verification que la requete a fonctionnée
            if (!$resPeopleDel){
              echo "Probleme lors du lancement de la requête de suppression des personnes cochés";
              exit;
            }
          }
        }
      } else if(isset($_POST['addPeople'])){ 
      	$sqlMatriculeTest="select matricule from personne";
    	$resMatriculeTest=pg_query($sqlMatriculeTest);
        //verification que la requete a fonctionnée
        if (!$resMatriculeTest){
          echo "Probleme lors du lancement de la requête de récupération des matricules existants";
          exit;
        }
        $isExistMatricule = False;
        $ligMatriculeTest=pg_fetch_array($resMatriculeTest);
        while ($ligMatriculeTest && !$isExistMatricule){    
          if($ligMatriculeTest['matricule'] == $_POST['peopleMatricule']){
	    $isExistMatricule = True;
	  } 
          //ligne suivante
          $ligMatriculeTest=pg_fetch_array($resMatriculeTest);
        }
        if($isExistMatricule){
           echo '<script language="javascript">';
           echo "alert(\"Une personne possède déjà ce matricule\")";
           echo '</script>';
        }else{
          $nom = addslashes($_POST['peopleName']);
    	  $prenom = addslashes($_POST['peopleFirstName']);      
	  $sqlPeopleAdd="insert into personne values (".$_POST['peopleMatricule'].", '".$prenom."', '".$nom."', ".$_POST['peopleTel'].", '".$_POST['peopleEmail']."', '".$_POST['peoplePartner']."')";
          $resPeopleAdd=pg_query($sqlPeopleAdd);
          //verification que la requete a fonctionnée
          if (!$resPeopleAdd){
            echo "Probleme lors du lancement de la requête d'ajout d'une personne";
            exit;
          }
        }
    } else if(isset($_POST['modifPeople'])){ 
      	$sqlMatriculeTest="select matricule from personne";
    	$resMatriculeTest=pg_query($sqlMatriculeTest);
        //verification que la requete a fonctionnée
        if (!$resMatriculeTest){
          echo "Probleme lors du lancement de la requête de récupération des matricules existants";
          exit;
        }
        $isExistMatricule = False;
        $ligMatriculeTest=pg_fetch_array($resMatriculeTest);
        while ($ligMatriculeTest && !$isExistMatricule){    
          if($ligMatriculeTest['matricule'] == $_POST['peopleMatricule2']){
	    $isExistMatricule = True;
	  } 
          //ligne suivante
          $ligMatriculeTest=pg_fetch_array($resMatriculeTest);
        }
        if(!$isExistMatricule){
           echo '<script language="javascript">';
           echo "alert(\"Aucune personne ne possède ce matricule, peut être vous êtes vous trompé ou alors vous voulez ajouter une personne\")";
           echo '</script>';
        }else{
          $sqlPeopleModifPart1 = "update personne set (";
          $sqlPeopleModifPart2 = ") = (";
          $sqlPeopleModifPart3 = ") where matricule = ".$_POST['peopleMatricule2'];
          if($_POST['peopleName2']){
            $nom = addslashes($_POST['peopleName2']);
            $sqlPeopleModifPart1.= "nom, ";
            $sqlPeopleModifPart2.= "'".$_POST['peopleName2']."', ";
          }
          if($_POST['peopleFirstName2']){
            $prenom = addslashes($_POST['peopleFirstName2']);
            $sqlPeopleModifPart1.= "prenom, ";
            $sqlPeopleModifPart2.= "'".$_POST['peopleFirstName2']."', ";
          }
          if($_POST['peopleTel2']){
            $sqlPeopleModifPart1.= "numerotel, ";
            $sqlPeopleModifPart2.= $_POST['peopleTel2'].", ";
          }
          if($_POST['peopleEmail2']){
            $sqlPeopleModifPart1.= "email, ";
            $sqlPeopleModifPart2.= "'".$_POST['peopleEmail2']."', ";
          }
          if(isset($_POST['peoplePartner2'])){
            $sqlPeopleModifPart1.= "partenaire, ";
            $sqlPeopleModifPart2.= "'".$_POST['peoplePartner2']."', ";
          }   
	  $sqlPeopleModif = substr($sqlPeopleModifPart1, 0, -2).substr($sqlPeopleModifPart2, 0, -2).$sqlPeopleModifPart3;
          $resPeopleModif = pg_query($sqlPeopleModif);
          //verification que la requete a fonctionnée
          if (!$resPeopleModif){
            echo "Probleme lors du lancement de la requête de modification d'une personne";
            exit;
          }
        }
    }
  ?>    

  <?php include 'header.php'; ?>
  
  <h2>LISTE DES PERSONNES</h2>
  <div class="peopleTable">
    <form action="peoples.php" method="post">
      <input type="hidden" name="delPeople">
      <table>
      	<tr><td> <b>Etablissement</b> </td><td> <b>Matricule</b> </td><td> <b>Nom</b> </td><td> <b>Prénom</b> </td><td> <b>Téléphone</b> </td><td> <b>E-Mail</b> </td></tr>
        <?php
          //lancement de la requête
          $sqlPeopleList="select matricule, nom, prenom, numerotel, email, partenaire from personne order by partenaire, matricule";
          $resPeopleList=pg_query($sqlPeopleList);
            //verification que la requete a fonctionnée
              if (!$resPeopleList){
                  echo "Probleme lors du lancement de la requête de la liste des personnes";
                  exit;
          }
          //recup ligne
          $ligPeopleList=pg_fetch_array($resPeopleList);
          //utilisation lignes
          while ($ligPeopleList){
            //affichage
            echo "<tr><td> ".$ligPeopleList['partenaire']." </td><td> ".$ligPeopleList['matricule']." </td><td> ".$ligPeopleList['nom']." </td><td> ".$ligPeopleList['prenom']." </td><td> 0".$ligPeopleList['numerotel']." </td><td> ".$ligPeopleList['email']." </td><td> <input type='checkbox' name='peopleToDelete[]' value=".$ligPeopleList['matricule']."> </td></tr>";
	  
            //ligne suivante
            $ligPeopleList=pg_fetch_array($resPeopleList);
          }
        ?>
      </table>
      <input type="submit" value="Supprimer les personnes sélectionnés">
    </form>
  </div>
  <div class="addPeople">
    <h3>Ajouter une personne</h3>
    <form action="peoples.php" method="post">
      <input type="hidden" name="addPeople">
      <div class="formTop">
        <input type="text" name="peopleFirstName" maxlength="30" placeholder="Prénom..." required>
        <input type="text" name="peopleName" maxlength="30" placeholder="Nom..." required>
      </div>
      <div class="formMiddle">
        <input type="text" name="peopleEmail" maxlength="50" placeholder="E-Mail..." required>
        <input type="number" name="peopleTel" placeholder="Numéro de Téléphone..." required>
      </div>
      <div class="formBottom">
        <input type="number" name="peopleMatricule" placeholder="Matricule..." required>
        <div>
          <select name="peoplePartner" required>
            <option value="" disabled selected>Partenaire...</option>
            <?php
              //lancement de la requête
              $sqlPartnerList2="select raisonsoc from partenaire order by raisonsoc";
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
        </div>
      </div>      
    </form>
  </div>
  <div class="modifPeople">
    <h3>Modifier une personne</h3>
    <form action="peoples.php" method="post">
      <input type="hidden" name="modifPeople">
      <div class="formTop">
        <input type="text" name="peopleFirstName2" maxlength="30" placeholder="Prénom...">
        <input type="text" name="peopleName2" maxlength="30" placeholder="Nom...">
      </div>
      <div class="formMiddle">
        <input type="text" name="peopleEmail2" maxlength="50" placeholder="E-Mail...">
        <input type="number" name="peopleTel2" placeholder="Numéro de Téléphone...">
      </div>
      <div class="formBottom">
        <input type="number" name="peopleMatricule2" placeholder="Matricule..." required>
        <div>
          <select name="peoplePartner2">
            <option value="" disabled selected>Partenaire...</option>
            <?php
              //lancement de la requête
              $sqlPartnerList2="select raisonsoc from partenaire order by raisonsoc";
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
          <input type="submit" value="Modifier">
        </div>
      </div>      
    </form>
  </div>
</body>
</html>
