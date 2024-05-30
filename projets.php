<?php
 // Connexion à MySQL
 $serveur = "localhost";
 $bd = "xegex";
 include "connexion.php";
 try {
  $sql = new PDO('mysql:host='.$serveur.';dbname='.$bd, $login, $mdp, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
 } catch(PDOException $e) {
  echo "Erreur de connexion à la BDD xegex:\n".$e->getMessage();
  die();
 }
 $projet_name = $_POST['projet_name'];
 $projet_name = str_replace('_', ' ', $projet_name);
 $crud_method = $_POST['crud_method'];

 ////////////////////////////////// INSERT //////////////////////////////
 if ($crud_method == "insert") {
  // ajoute un projet dans la bdd
  try {
   $request = "INSERT INTO Projets (projet_name, last_modif) VALUES(:projet_name, NOW())";
   $stmt = $sql->prepare($request);
   $stmt->bindParam(':projet_name', $projet_name);
   $stmt->execute();
   //$sql->query($request); // J'ai essayé avec bindParam mais sans succès
  } catch (Exception $e) {
   $sql->rollback();
   echo "Insertion non effectuée:".$e;
  }
 }
 ////////////////////////////////// UPDATE //////////////////////////////
 elseif ($crud_method == "update") {
  try {
   $old_projet_name = $_POST['old_projet_name'];
   $request = "UPDATE Projets SET projet_name = :projet_name, last_modif = NOW() WHERE projet_name = :old_projet_name";
   $stmt = $sql->prepare($request);
   $stmt->bindParam(':projet_name', $projet_name);
   $stmt->bindParam(':old_projet_name', $old_projet_name);
   $stmt->execute();
   //$sql->query($request); // J'ai essayé avec bindParam mais sans succès
  } catch (Exception $e) {
   $sql->rollback();
   echo "Update non effectuée:".$e;
  }
 }
 elseif ($crud_method == "delete") {
  try {
   $projet_name = $_POST['projet_name'];
   $request = "DELETE FROM Projets WHERE projet_name = :projet_name";
   // Pas besoin de supprimer de la table Tag_Projets car ON DELETE CASCADE
   $stmt = $sql->prepare($request);
   $stmt->bindParam(':projet_name', $projet_name);
   $stmt->execute();
 } catch (Exception $e) {
  $sql->rollback();
  echo "Délétion non effectuée: ".$e;
 }
}
?>
