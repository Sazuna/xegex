<!DOCTYPE html>
<html>
<head>
 <title>xegex</title>
 <meta charset='utf-8'>
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel='stylesheet'>
 <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
 <!--
 <script type='text/javascript' src="projets.js"></script>
 <script type='text/javascript' src="tags.js"></script>
 <script type="module" src="projets_sort.mjs"></script>
 <script type="module" src="tags_sort.mjs"></script>
 -->
 <script type="text/javascript" src="download.js"></script>
 <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js"></script><!-- pour la fonction sortable() -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>

 <link rel='stylesheet' href='style.css'>

</head>
<body class="d-flex flex-column min-vh-100">
 <header>
  <nav class="navbar navbar-expand-lg navbar-light bg-light mb-3">
   <div class="container">
     <!-- Logo -->
     <a class="navbar-brand" href="#">
      <img src="xegex.png" alt="Logo" width="80" height="80" class="d-inline-block align-top">
     </a>
     <!-- Bouton pour toggle la navbar sur les petits écrans -->
     <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
     </button>

     <!-- Contenu de la navbar -->
     <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav mr-auto">
       <li class="nav-item">
        <a class="nav-link" href="home.html">HOME</a>
       </li>
       <li class="nav-item">
        <a class="nav-link" href="annotateur.php">Annotation de corpus</a>
       </li>
      </ul>
      <!-- alignés à droite -->
      <ul class="navbar-nav ml-auto" style="margin-left: auto;">
       <li class="nav-item">
        <a class="nav-link mr-2" href="projets_manager.php">Mes projets</a>
       </li>
      </ul>
     </div>
   </div>
  </nav>
 </header>
 <main class='container'>
  <h1>Résultats de l'analyse</h1>
  <fieldset class='mx-auto border p-3 active' id='corpus-informations'>
   <legend>Informations</legend>
   <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
     // Affichage du résumé de la requête sous forme de tableau
     echo "<table class='mx-auto'><tbody>";
     if (!empty($_POST['projets'])) {
      echo "<tr><td class='pr-3'><b>Projet:</b></td><td> ".$_POST['projets']."</td></tr>";
     } else {
      echo "<tr><td class='pr-3'><b>Projet:</b></td><td> Aucun projet sélectionné</td></tr>";
     }
     // Affichage des tags dans l'ordre
     if (!empty($_POST['tags'])) {
      echo "<tr><td class='pr-3' style='vertical-align: top'><b>Tags sélectionnés: </b></td>";
      echo '<td>';
      foreach ($_POST['tags'] as $tag) {
       echo htmlspecialchars($tag) . "<br>";
      }
      echo '</td></tr>';
      } else {
       echo "<tr><td class='pr-3'><b>Tags:</b></td><td> Aucun tag sélectionné</td></tr>";
      }
      if (!empty($_FILES['formFile']['name'])) {
       echo "<tr><td class='pr-3'><b>Nom du fichier: </b></td><td id='titre'>" . htmlspecialchars($_FILES['formFile']['name']) . '</td></tr>';
       echo "<tr><td class='pr-3'><b>Taille du fichier: </b></td><td>" . htmlspecialchars($_FILES['formFile']['size']) . ' bytes</td></tr>';
       echo '</tbody></table>';
       echo '<div class="d-flex justify-content-center">';
       echo '<button class="btn btn-secondary mx-3" id="download-html">Télécharger au format HTML</button>';
       echo '<button class="btn btn-secondary mx-3" id="download-xml">Télécharger au format XML</button>';
       echo '</div>';
       $fileContent = file_get_contents($_FILES['formFile']['tmp_name']);
       $regex = "/\n/i";
       $fileContent = preg_replace($regex, "<br>", $fileContent);
       echo "</fieldset>";

       // récupérer pour chaque tag de $_POST['tags'], la liste des regex et du lexique et appliquer preg_replace en remplaçant background-color par couleur et name par label.
       echo "<fieldset class='mx-auto border p-3 active' id='corpus-annote'>";
       echo "<legend>Corpus annoté</legend>";
       echo "<div class='mx-4 my-4' id='corpus'>";
       ///////// CONNEXION A LA BDD //////////
       $serveur = "localhost";
       $bd = "xegex";
       include "connexion.php";
       try {
        $sql = new PDO('mysql:host='.$serveur.';dbname='.$bd, $login, $mdp, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
       } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(array("error" => "Erreur PDO: " . $e->getMessage()));
        die();
       }
       // Pour chaque tag, qui sont triés selon l'ordre après l'arrangement des tags
       foreach ($_POST['tags'] as $tag_name) {
        try {
         // D'abord le Lexique
         $request = "SELECT mot, Tags.color FROM Lexique INNER JOIN Tags ON Tags.id_tag = Lexique.id_tag WHERE Tags.label = :tag_name ORDER BY mot";
         $stmt  = $sql->prepare($request);
         $stmt->bindParam(':tag_name', $tag_name);
         $stmt->execute();
         $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
         $regex = "";
         foreach ($results as $res) {
          $mot = $res['mot'];
          $color = $res['color'];
          // Factorisation de regex pour moins d'opérations sur tout le document
          $regex = $regex.$mot."|";
         }
         if (strlen($regex) > 0) {
          $regex = rtrim($regex, '|');
          $regex = "/\b(".$regex.")\b/iu"; // Frontières de mots \b, i ignorecase, u unicode
          $fileContent = preg_replace($regex, "<span class='btn' style='background-color:$color; color:white' name='_".$tag_name."_' method='lexique'&GT$0&LT/span>", $fileContent);
         }

         // Puis les regex
         $request = "SELECT regex, Tags.color FROM Regex INNER JOIN Tags ON Tags.id_tag = Regex.id_tag WHERE Tags.label = :tag_name";
         $stmt  = $sql->prepare($request);
         $stmt->bindParam(':tag_name', $tag_name);
         $stmt->execute();

         $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
         $regex = "";
         foreach ($results as $res) {
          $reg = $res['regex'];
          $color = $res['color'];
          $regex = $regex.$reg."|";
         }
         if (strlen($regex) > 0) {
          $regex = rtrim($regex, '|');
          $regex = "/\b(".$regex.")\b/iu";
          $fileContent = preg_replace($regex, "<span class='btn' style='background-color:$color; color:white' name='_".$tag_name."_' method='regex'&GT$0&LT/span>", $fileContent);
         }
        } catch (PDOException $e) {
         http_response_code(500);
         echo json_encode(array("error" => "Erreur PDO: " . $e->getMessage()));
        } catch (Exception $e) {
         http_response_code(500);
         echo json_encode(array("error" => "Erreur: " . $e->getMessage()));
        }
       }

       // Afin d'éviter que des tokens ne soient réannotés par une opération ultérieure, comme < et > sont considérés comme des frontières de mots, on les a remplacés momentanément par &LT et &GT (sans ; et en majuscule afin de ne pas être confondus avec les chevrons présents dans le corpus original, qui sont écrits &lt; et $gt; au moment du chargement du contenu du fichier).
       // Une fois les boucles sur les tags, le lexique et les regex terminées, nous remettons les chevrons :
       $fileContent = preg_replace("/&LT/", "<", $fileContent);
       $fileContent = preg_replace("/&GT/", ">", $fileContent);
       // Même problème. On a ajouté _ autour du tag-name pour que le nom du tag ne soit pas reconnu par les regex.
       $fileContent = preg_replace("/_/", "", $fileContent);
       echo $fileContent;
	
      } else {
       echo '<tr><td class="pr-3" colspan="2"><b>Aucun fichier importé.</b></td></tr>';
       echo '</tbody></table>';
      }
     } else {
      echo '<div><b>Erreur:</b>Aucun formulaire soumis.</div>';
     }
     echo "</div></fieldset>";
    ?>
   </div>
  </main>
 <footer class='mt-auto bg-body-tertiary text-center text-lg-start'>
  <div class="text-center p-3 bg-light">
   By <a href="https://github.com/Sazuna">@Liza Fretel</a>
   <br><a href="https://www.inalco.fr">Inalco</a>
  </div>
 </footer>
</body>
</html>

