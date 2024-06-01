<!DOCTYPE html>
<html>
<head>
 <title>xegex</title>
 <meta charset='utf-8'>
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel='stylesheet'>
 <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
 <script type="module" src="projets_sort.mjs"></script>
 <script type="module" src="tags_sort.mjs"></script>
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
  <form action='analyse.php' method='post' enctype="multipart/form-data"> <!-- Formulaire de choix et ajustements du projet + import fichier -->
   <fieldset class="mx-auto border p-3 active">
    <legend class='w-auto px-2 col-10'>Choix du projet</legend>
    <div class='form-group'>

    <!-- Récupération de la liste des projets existants -->

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
     $request = $sql->query("SELECT * FROM Projets ORDER BY last_modif DESC");
     while ($projet = $request->fetch(PDO::FETCH_OBJ)) {
      $projet_id = str_replace(' ', '_', $projet->projet_name);
      echo "<input type='radio' class='btn-check' name='projets' id='$projet_id' value='$projet_id' autocomplete='off'>";
      echo "<label class='btn btn-secondary projet-btn' for='$projet_id'><span>".$projet->projet_name."</span><input type='text' maxlength='20' style='display:none'></label> ";
     }
    ?>


    </div>
   </fieldset>


   <fieldset id="tags-list" class="mx-auto border p-3">
    <div class='svg-container row'>
     <legend class='w-auto px-2'>Arrangement des tags</legend>
     <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-info-circle col-2 info-icon" viewBox="0 0 16 16" id="tag-help-btn">
      <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
     <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
     </svg>
     <div id="tag-help" style="display:none" class="bg-light help">
      <p>L'ordonnancement des tags sert à savoir quel tag associer en priorité en cas de match multiple.</p>
      <ul>
       <li><b>Arranger l'ordre de priorité: </b>glissez-déposez les tags pour les mettre dans un ordre qui vous convient. Le tag le plus prioritaire est celui le plus à gauche.</li>
       <li><b>Désactiver un tag: </b>cliquez sur le tag de façon à décocher la case à cocher. De cette façon, le tag ne sera pas utilisé lors de l'annotation.</li>
      </ul>
     </div>
    </div>
    <div class='form-group' id='tags'>
     <div id='sortable'>
      <!-- Les tags seront ajoutés ici après la sélection d'un projet. -->
     </div>
    </div>
   </fieldset>


   <fieldset id="file-import" class="mx-auto border p-3">
    <legend class='w-auto px-2'>Importer un corpus</legend>
    <div class='form-group'>
     <div class="m-3">
      <input class="form-control" type="file" accept=".txt" id="formFile" name="formFile" required>
     </div>


     <div class="text-center">
      <input type='submit' class='btn btn-secondary' id='valider' value='Envoyer'>
     </div>
    </div>
   </fieldset>
  </form>

 </main>

 <footer class='mt-auto bg-body-tertiary text-center text-lg-start'>
  <div class="text-center p-3 bg-light">
   By <a href="https://github.com/Sazuna">@Liza Fretel</a>
   <br><a href="https://www.inalco.fr">Inalco</a>
  </div>
 </footer>

 <!-- Scripts Bootstrap -->
 <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
 <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
 <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
