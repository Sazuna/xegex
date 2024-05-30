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
   $tag_name = $_POST['tag_name'];
   $request = "SELECT regex, description FROM Regex INNER JOIN Tags ON Tags.id_tag = Regex.id_tag WHERE Tags.label = :tag_name";
   $stmt  = $sql->prepare($request);
   $stmt->bindParam(':tag_name', $tag_name);
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
  /// 1. ajouter la Regex sans la description si elle n'existe pas
  $regex = $_POST['regex'];
  $description = $_POST['description'];
  $projet_name = $_POST['projet_name'];
  $projet_name = str_replace('_', ' ', $projet_name);
  $tag_name = $_POST['tag_name'];
  $tag_name = str_replace('_', ' ', $tag_name);
 try {
  $request = "INSERT INTO Regex (id_tag, regex, description)
   SELECT id_tag, :regex, :description
   FROM Tags
   WHERE label = :tag_name
   AND LENGTH(:regex) > 0
   AND NOT EXISTS (
    SELECT id_tag
    FROM Regex
    WHERE Regex.id_tag = (SELECT id_tag FROM Tags WHERE Tags.label = :tag_name)
    AND Regex.regex = :regex
   )"; // AND NOT EXISTS: peut-être le supprimer pour ne pas permettre de ré-enregistrer la même regex. Cela provoquera une erreur et empêchera toutes les mises à jour (donc pas besoin de l'étape 2 et 4).
  $stmt = $sql->prepare($request);
  $stmt->bindParam(':tag_name', $tag_name);
  $stmt->bindParam(':regex', $regex);
  $stmt->bindParam(':description', $description);
  $stmt->execute();
 } catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(array("error" => "Erreur PDO: " . $e->getMessage()));
 } catch (Exception $e) {
  http_response_code(500);
  echo json_encode(array("error" => "Erreur: " . $e->getMessage()));
 }
  /// 2. Update la regex pour ajouter une description si la description n'est pas nulle
  // Cela permet de ne pas écraser une Regex identique qui a déjà été créée pour le même Tag par une description nulle.
  if ($description != "")
  {
   try {
    $request = "UPDATE Regex
     SET description=:description
     WHERE Regex=:regex
     AND id_tag = (SELECT id_tag FROM Tags WHERE Tags.label=:tag_name)";
    $stmt = $sql->prepare($request);
    $stmt->bindParam(':tag_name', $tag_name);
    $stmt->bindParam(':regex', $regex);
    $stmt->bindParam(':description', $description);
    $stmt->execute();
   } catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(array("error" => "Erreur PDO: " . $e->getMessage()));
   } catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array("error" => "Erreur: " . $e->getMessage()));
   }
  }
  /// 3. Modification de la date d'accès au projet
  try {
   $request = "UPDATE Projets SET last_modif = NOW() WHERE projet_name = :projet_name";
   $stmt = $sql->prepare($request);
   $stmt->bindParam(':projet_name', $projet_name);
   $stmt->execute();
  } catch (Exception $e) {
   $sql->rollback();
   http_response_code(500);
   //echo "Modification du projet non effectuée: ".$e;
   echo json_encode(array("error" => "Erreur: " . $e->getMessage()));
  }
  /// 4. Renvoie la nouvelle regex si elle n'a pas été modifiée
  // TODO si j'ai du temps
 }
 ////////////////////////////////// UPDATE //////////////////////////////
 elseif ($crud_method == "update") {
  try {
   $projet_name = $_POST['projet_name'];
   $projet_name = str_replace('_', ' ', $projet_name);
   $tag_name = $_POST['tag_name'];
   $tag_name = str_replace('_', ' ', $tag_name);
   $regex = $_POST['regex'];
   $old_regex = $_POST['old_regex'];
   $description = $_POST['description'];
   $old_description = $_POST['old_description'];
   $request = "UPDATE Regex SET regex=:regex, description=:description WHERE Regex.regex=:old_regex AND Regex.id_tag = (SELECT id_tag FROM Tags WHERE Tags.label=:tag_name)";
   //$sql->query($request); // J'ai essayé avec bindParam mais sans succès
   $stmt = $sql->prepare($request);
   $stmt->bindParam(':regex', $regex);
   $stmt->bindParam(':old_regex', $old_regex);
   $stmt->bindParam(':description', $description);
   $stmt->bindParam(':tag_name', $tag_name);
   $stmt->execute();
  } catch (Exception $e) {
   $sql->rollback();
   echo "Update non effectuée: ".$e;
  }
  // Màj de la date d'accès au projet
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
  try {
   $projet_name = $_POST['projet_name'];
   $projet_name = str_replace('_', ' ', $projet_name);
   $tag_name = $_POST['tag_name'];
   $tag_name = str_replace('_', ' ', $tag_name);
   $regex = $_POST['regex'];
   //$old_mot = $_POST['old_mot'];
   $request = "DELETE FROM Regex WHERE id_tag=(SELECT id_tag FROM Tags WHERE label = :tag_name) AND regex=:regex";
   //$sql->query($request); // J'ai essayé avec bindParam mais sans succès
   $stmt = $sql->prepare($request);
   $stmt->bindParam(':regex', $regex);
   $stmt->bindParam(':tag_name', $tag_name);
   $stmt->execute();
  } catch (Exception $e) {
   $sql->rollback();
   echo "Delete non effectué: ".$e;
  }
  // Màj de la date d'accès au projet
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
?>
