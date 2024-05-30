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
   $request = "SELECT mot FROM Lexique INNER JOIN Tags ON Tags.id_tag = Lexique.id_tag WHERE Tags.label = :tag_name ORDER BY mot";
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


  // TODO récupérer l'id du tag

  //$id_tag = 1;
 try {
  $mots = $_POST['mots'];
  $projet_name = $_POST['projet_name'];
  $projet_name = str_replace('_', ' ', $projet_name);
  $tag_name = $_POST['tag_name'];
  $tag_name = str_replace('_', ' ', $tag_name);

 /*
  $request = "INSERT INTO Lexique (id_tag, mot) VALUES ((SELECT id_tag FROM Tags WHERE Tags.label=:tag_name), :mot) WHERE NOT EXISTS (SELECT id_tag FROM Lexique WHERE Lexique.id_tag=(SELECT id_tag FROM Tags WHERE Tags.label=:tag_name) AND Lexique.mot = :mot)";
 */
  $request = "INSERT INTO Lexique (id_tag, mot)
SELECT id_tag, :mot
FROM Tags
WHERE label = :tag_name
AND LENGTH(:mot) > 0
AND NOT EXISTS (
    SELECT id_tag
    FROM Lexique
    WHERE Lexique.id_tag = (SELECT id_tag FROM Tags WHERE Tags.label = :tag_name)
    AND Lexique.mot = :mot
)";
  $stmt = $sql->prepare($request);

  foreach ($mots as $mot) {
   $stmt->execute(array(':tag_name' => $tag_name, ':mot' => $mot));
  }
 } catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(array("error" => "Erreur PDO: " . $e->getMessage()));
 } catch (Exception $e) {
  http_response_code(500);
  echo json_encode(array("error" => "Erreur: " . $e->getMessage()));
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
  /// 4. Renvoie la nouvelle liste de mots du lexique
  /*
  try {
   $request = "SELECT mot FROM Lexique INNER JOIN Tags ON Tags.id_tag = Lexique.id_tag WHERE Tags.label = :tag_name ORDER BY mot";
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
  }*/
 }
 ////////////////////////////////// UPDATE //////////////////////////////
 elseif ($crud_method == "update") {
  try {
   $projet_name = $_POST['projet_name'];
   $projet_name = str_replace('_', ' ', $projet_name);
   $tag_name = $_POST['tag_name'];
   $tag_name = str_replace('_', ' ', $tag_name);
   $mot = $_POST['mot'];
   $old_mot = $_POST['old_mot'];
   $request = "UPDATE Lexique SET mot=:mot WHERE Lexique.mot=:old_mot AND Lexique.id_tag = (SELECT id_tag FROM Tags WHERE Tags.label=:tag_name)";
   //$sql->query($request); // J'ai essayé avec bindParam mais sans succès
   $stmt = $sql->prepare($request);
   $stmt->bindParam(':mot', $mot);
   $stmt->bindParam(':old_mot', $old_mot);
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
   $mot = $_POST['mot'];
   //$old_mot = $_POST['old_mot'];
   $request = "DELETE FROM Lexique WHERE id_tag=(SELECT id_tag FROM Tags WHERE label = :tag_name) AND mot=:mot";
   //$sql->query($request); // J'ai essayé avec bindParam mais sans succès
   $stmt = $sql->prepare($request);
   $stmt->bindParam(':mot', $mot);
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
