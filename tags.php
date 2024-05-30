<?php
 // Connexion à MySQL
 $serveur = "localhost";
 $bd = "xegex";
 include "connexion.php";
 try {
  $sql = new PDO('mysql:host='.$serveur.';dbname='.$bd, $login, $mdp, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
 } catch(PDOException $e) {
   http_response_code(500);
   echo json_encode(array("error" => "Erreur PDO: " . $e->getMessage()));
   //echo "Erreur de connexion à la BDD xegex:\n".$e->getMessage();
  die();
 }

 $crud_method = $_POST['crud_method'];
 ////////////////////////////////// SELECT //////////////////////////////
 if ($crud_method == "select") {
  try {
   $projet_name = $_POST['projet_name'];
   $projet_name = str_replace('_', ' ', $projet_name);
   $request = "SELECT label, color FROM Tags INNER JOIN Tag_Projet ON Tags.id_tag = Tag_Projet.id_tag INNER JOIN Projets ON Projets.id_projet = Tag_Projet.id_projet WHERE Projets.projet_name = :projet_name ORDER BY label";
   $stmt  = $sql->prepare($request);
   $stmt->bindParam(':projet_name', $projet_name);
   $stmt->execute();

   $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

   $json_response = json_encode($results);

   header('Content-Type: application/json');
   echo $json_response;
  }
  catch (PDOException $e) {
   http_response_code(500);
   echo json_encode(array("error" => "Erreur PDO: " . $e->getMessage()));
  } catch (Exception $e) {
   http_response_code(500);
   echo json_encode(array("error" => "Erreur: " . $e->getMessage()));
  }
 }

 ////////////////////////////////// INSERT //////////////////////////////
 elseif ($crud_method == "insert") {
   /// 0. Vérifier si le nom de Tag existe ou non
  $EXISTS = 0;
  try {
   $tag_name = $_POST['tag_name'];
   $projet_name = $_POST['projet_name'];
   $projet_name = str_replace('_', ' ', $projet_name);
   $tag_color = $_POST['tag_color'];
   // Si il existe il faudra return la valeur de couleur de ce tag
   $request = "SELECT id_tag, color FROM Tags WHERE Tags.label = :tag_name";
   $stmt = $sql->prepare($request);
   $stmt->bindParam(':tag_name', $tag_name);
   $stmt->execute();
   $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
   if ($results) {
    $id_tag = $results[0]['id_tag'];
    $tag_color = $results[0]['color']; // Remplacement de la couleur
    $EXISTS = 1;
   }
   // Si il y a un resultat, récupérer label et color
  } catch (Exception $e) {
   $sql->rollback();
   //echo "Select non effectué: ".$e;
   http_response_code(500);
   echo json_encode(array("error" => "Erreur PDO: " . $e->getMessage()));
  }
  // Si le nom de tag n'existe pas, l"insère dans la table Tags
  if (! $EXISTS) {
   try {
    /// 1. Insertion du nouveau Tag SI le tag n'existait pas
    $request = "INSERT INTO Tags (label, color) VALUES('$tag_name', '$tag_color')";
    $sql->query($request); // J'ai essayé avec bindParam mais sans succès
   } catch (Exception $e) {
    // Le nom de tag existait déjà.
    $sql->rollback();
    echo "Insertion non effectuée: ".$e;
   }
  }


   /// 2. liaison du Tag au Projet
   //$request = "INSERT INTO Tag_Projet (id_tag, id_projet) VALUES(SELECT id_tag FROM Tags WHERE Tags.label = $tag_name, SELECT id_projet FROM Projets WHERE Projets.id_projet = $projet_name)";
   //$sql->query($request); // J'ai essayé avec bindParam mais sans succès

  try {
   $request = "INSERT INTO Tag_Projet (id_tag, id_projet) 
            VALUES (
                (SELECT id_tag FROM Tags WHERE Tags.label = :tag_name),
                (SELECT id_projet FROM Projets WHERE Projets.projet_name = :projet_name)
            )";

   $stmt = $sql->prepare($request);
   $stmt->bindParam(':tag_name', $tag_name);
   $stmt->bindParam(':projet_name', $projet_name);
   $stmt->execute();
  } catch (Exception $e) {
   $sql->rollback();
   echo "Insertion non effectuée: ".$e;
  }
  /// 3. Modification de la date d'accès au projet
  try {
   $request = "UPDATE Projets SET last_modif = NOW() WHERE projet_name = :projet_name";
   $stmt = $sql->prepare($request);
   $stmt->bindParam(':projet_name', $projet_name);
   $stmt->execute();
  } catch (Exception $e) {
   $sql->rollback();
   echo "Modification du projet non effectuée: ".$e;
  }

  /// 4. Renvoi de la couleur
  if ($EXISTS)
  {
   header('Content-Type: application/json');
   echo json_encode(array("tag_color" => $tag_color));
  }
 }
 ////////////////////////////////// UPDATE //////////////////////////////
 elseif ($crud_method == "update") {
  try {
   $projet_name = $_POST['projet_name'];
   $projet_name = str_replace('_', ' ', $projet_name);
   $tag_name = $_POST['tag_name'];
   $tag_color = $_POST['tag_color'];
   $old_tag_name = $_POST['old_tag_name'];
   $request = "UPDATE Tags SET label = '$tag_name', color = '$tag_color' WHERE label = '$old_tag_name'";
   $sql->query($request); // J'ai essayé avec bindParam mais sans succès
  } catch (Exception $e) {
   $sql->rollback();
   echo "Update non effectuée: ".$e;
  }
  /// Modification de la date d'accès au projet
  try {
   $request = "UPDATE Projets SET last_modif = NOW() WHERE projet_name = :projet_name";
   $stmt = $sql->prepare($request);
   $stmt->bindParam(':projet_name', $projet_name);
   $stmt->execute();
  } catch (Exception $e) {
   $sql->rollback();
   echo "Modification du projet non effectuée: ".$e;
  }
 }
 ////////////////////////////////// DELETE //////////////////////////////
 elseif ($crud_method == "delete") {
  $projet_name = $_POST['projet_name'];
  $projet_name = str_replace('_', ' ', $projet_name);
  $tag_name = $_POST['tag_name'];
  $tag_name = str_replace('_', ' ', $tag_name);
  try {
   $request = "DELETE FROM Tag_Projet WHERE id_tag = (SELECT id_tag FROM Tags WHERE label = :tag_name) AND id_projet = (SELECT id_projet FROM Projets WHERE projet_name = :projet_name)";
   $stmt = $sql->prepare($request);
   $stmt->bindParam(':projet_name', $projet_name);
   $stmt->bindParam(':tag_name', $tag_name);
   $stmt->execute();
  } catch (Exception $e) {
   $sql->rollback();
   echo "Délétion non effectuée: ".$e;
  }

  /// Modification de la date d'accès au projet
  try {
   $request = "UPDATE Projets SET last_modif = NOW() WHERE projet_name = :projet_name";
   $stmt = $sql->prepare($request);
   $stmt->bindParam(':projet_name', $projet_name);
   $stmt->execute();
  } catch (Exception $e) {
   $sql->rollback();
   echo "Modification du projet non effectuée: ".$e;
  }
  /// Modification de la date d'accès au projet
  try {
   $request = "UPDATE Projets SET last_modif = NOW() WHERE projet_name = :projet_name";
   $stmt = $sql->prepare($request);
   $stmt->bindParam(':projet_name', $projet_name);
   $stmt->execute();
  } catch (Exception $e) {
   $sql->rollback();
   echo "Modification du projet non effectuée: ".$e;
  }
 }
 ////////////////////////////// SELECT NOT ////////////////////////////////
 // Récupération des Tags qui ne sont pas liés au projet courant
 elseif ($crud_method == "select_not") {
  $projet_name = $_POST['projet_name'];
  $projet_name = str_replace('_', ' ', $projet_name);
  //$tag_name = $_POST['tag_name'];
  //$tag_name = str_replace('_', ' ', $tag_name);
  try {
   $request = "SELECT label FROM Tags WHERE id_tag != ALL (SELECT id_tag FROM Tag_Projet WHERE id_projet = (SELECT id_projet FROM Projets WHERE projet_name = :projet_name))";
   $stmt = $sql->prepare($request);
   $stmt->bindParam(':projet_name', $projet_name);
   //$stmt->bindParam(':tag_name', $tag_name);
   $stmt->execute();
   $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
   $json_response = json_encode($results);
   header('Content-Type: application/json');
   echo $json_response;
  } catch (Exception $e) {
   $sql->rollback();
   echo "Select not non effectué: ".$e;
  }
 }
?>
