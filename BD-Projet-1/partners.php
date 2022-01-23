<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>TaskIt</title>
  <link rel="stylesheet" href="partnersStyle.css">
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
        //suppression des projets cochés
        foreach ($_POST['partnerToDelete'] as $elt){
          $isUndeletable = False;       
          $sqlListUndeletable="select distinct partenaire from partenaireprojet";
          $resListUndeletable=pg_query($sqlListUndeletable);
          //verification que la requete a fonctionnée
          if (!$resListUndeletable){
            echo "Probleme lors du lancement de la requête de récupération de la liste des partenaires qui sont dans des projets";
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
             echo "alert(\"Le partenaire ayant pour raison social ".$elt." ne peut pas être supprimée car il est attribué à un projet, retirez cette attribution ou supprimez le projet si vous souhaitez le supprimer\")";
             echo '</script>';
          }else{
            $sqlPartnerDel="delete from partenaire where raisonsoc = '".$elt."'";
            $resPartnerDel=pg_query($sqlPartnerDel);
            //verification que la requete a fonctionnée
            if (!$resPartnerDel){
              echo "Probleme lors du lancement de la requête de suppression des partenaires cochés";
              exit;
            }
         }
       }
    } else if(isset($_POST['addPartner'])){ 
      $sqlRaisonSocTest="select raisonsoc from partenaire";
      $resRaisonSocTest=pg_query($sqlRaisonSocTest);
      //verification que la requete a fonctionnée
      if (!$resRaisonSocTest){
        echo "Probleme lors du lancement de la requête de récupération des raison sociales existants";
        exit;
      }
      $isExistRaisonSoc = False;
      $ligRaisonSocTest=pg_fetch_array($resRaisonSocTest);
      while ($ligRaisonSocTest && !$isExistRaisonSoc){    
        if($ligRaisonSocTest['raisonsoc'] == $_POST['partnerSoc']){
	  $isExistRaisonSoc = True;
	} 
        //ligne suivante
        $ligRaisonSocTest=pg_fetch_array($resRaisonSocTest);
      }
      if($isExistRaisonSoc){
        echo '<script language="javascript">';
        echo "alert(\"Un partenaire possède déjà cette raison sociale\")";
        echo '</script>';
      }else{
        $adresse = addslashes($_POST['partnerAdress']);
    	$activite = addslashes($_POST['partnerActivity']);      
	$sqlPartnerAdd="insert into partenaire(raisonsoc, adresse, activite, type) values ('".$_POST['partnerSoc']."', '".$adresse."', '".$activite."', '".$_POST['partnerType']."')";
        $resPartnerAdd=pg_query($sqlPartnerAdd);
        //verification que la requete a fonctionnée
        if (!$resPartnerAdd){
          echo "Probleme lors du lancement de la requête d'ajout d'un partenaire";
          exit;
        }
      }
    }elseif (isset($_POST['modifContact'])){ 
      $isInList = False;     
      $sqlListContact="select matricule from personne where partenaire = '".$_POST['contactPartner']."'";
      $resListContact=pg_query($sqlListContact);
      //verification que la requete a fonctionnée
      if (!$resListContact){
        echo "Probleme lors du lancement de la requête de récupération de la liste des matricule d'un partenaire";
        exit;
      }
      $ligListContact=pg_fetch_array($resListContact);
      //utilisation lignes
      while ($ligListContact && !$isInList){
	if($ligListContact['matricule'] == $_POST['contactMatricule']){
	  $isInList = True;
	} 
        //ligne suivante
        $ligListContact=pg_fetch_array($resListContact);
      }
      if(!$isInList){
      	echo '<script language="javascript">';
        echo "alert(\"Auncun employé n'a ce matricule parmis ceux appartenant à ce partenaire\")";
        echo '</script>';
      }else{
        $sqlModifContact="update partenaire set contact = ".$_POST['contactMatricule']." where raisonsoc = '".$_POST['contactPartner']."'";
        $resModifContact=pg_query($sqlModifContact);
        //verification que la requete a fonctionnée
        if (!$resModifContact){
          echo "Probleme lors du lancement de la requête de récupération de la liste des matricule d'un partenaire";
          exit;
        }
      }
    }
  ?>    

  <?php include 'header.php'; ?>
  
  <h2>LISTE DES PARTENAIRES</h2>
  <div class="partnerTable">
    <form action="partners.php" method="post">
      <input type="hidden" name="delPartner">
      <table>
      	<tr><td> <b>Raison Sociale</b> </td><td> <b>Adresse</b> </td><td> <b>Type</b> </td><td> <b>Contact</b> </td></tr>
        <?php
          //lancement de la requête
          $sqlPartnerList="select raisonsoc, activite, adresse, type, contact from partenaire order by raisonsoc";
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
    <h3>Ajouter un partenaire</h3>
    <form action="partners.php" method="post">
      <input type="hidden" name="addPartner">
      <div class="formTop">
        <input type="text" name="partnerSoc" maxlength="30" placeholder="Raison Sociale..." required>
        <input type="text" name="partnerAdress" maxlength="50" placeholder="Adresse postale..." required>
      </div>
      <textarea name="partnerActivity" rows="5" cols="33" maxlength="155" placeholder="Activité..." required></textarea>
      <div class="formBottom">
      	<select name="partnerType" required>
      	  <option value="" disabled selected>Type...</option>
          <option>entreprise</option>
          <option>institution</option>
          <option>laboratoire</option>
        </select>
        <input type="submit" value="Ajouter">
      </div>      
    </form>
  </div>
  <div class="modifContact">
    <h3>Modifier un contact</h3>
    <form action="partners.php" method="post">
      <input type="hidden" name="modifContact">
      <select name="contactPartner" required>
        <option value="" disabled selected>Raison Sociale du partenaire...</option>
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
      <input type="number" name="contactMatricule" placeholder="Matricule du nouveau contact..."> 
      <input type="submit" value="Modifier">   
    </form>
  </div>
</body>
</html>
