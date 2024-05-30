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
 -->
 <script type="module" src="projets.mjs"></script>
 <script type="module" src="tags.mjs"></script>
 <script type="module" src="regex.mjs"></script>
 <script type="module" src="lexique.mjs"></script>
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

<main class="container">
    <div id="confirmation-del-projet" class="confirmation-dialog" class="hidden">
        <div class="dialog-content">
            <p class="m-2" id="message-suppression">message de supression</p>
            <button class="btn btn-danger m-2" id="confirm-del-projet-btn">Confirmer</button>
            <button class="btn btn-outline-secondary m-2" id="cancel-del-projet-btn">Annuler</button>
        </div>
    </div>
    <div id="confirmation-del-tag" class="confirmation-dialog" class="hidden">
        <div class="dialog-content">
            <p class="m-2" id="message-suppression-tag">message de supression</p>
            <button class="btn btn-danger m-2" id="confirm-del-tag-btn">Confirmer</button>
            <button class="btn btn-outline-secondary m-2" id="cancel-del-tag-btn">Annuler</button>
        </div>
    </div>
    <div id="confirmation-del-lexique" class="confirmation-dialog" class="hidden">
        <div class="dialog-content">
            <p class="m-2" id="message-suppression-lexique">Voulez-vous supprimer tout le lexique de ce tag ?</p>
            <button class="btn btn-danger m-2" id="confirm-del-lexique-btn">Confirmer</button>
            <button class="btn btn-outline-secondary m-2" id="cancel-del-lexique-btn">Annuler</button>
        </div>
    </div>
    <div id="confirmation-del-regex" class="confirmation-dialog" class="hidden">
        <div class="dialog-content">
            <p class="m-2" id="message-suppression-regex">Voulez-vous supprimer toutes les regex de ce tag ?</p>
            <button class="btn btn-danger m-2" id="confirm-del-regex-btn">Confirmer</button>
            <button class="btn btn-outline-secondary m-2" id="cancel-del-regex-btn">Annuler</button>
        </div>
    </div>

  <fieldset class="mx-auto border p-3 active">
   <div class='svg-container row'>
    <legend class='w-auto px-2 col-10'>Choix du projet</legend>
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-info-circle col-2 info-icon" viewBox="0 0 16 16" id="projet-help-btn">
    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
    <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
    </svg>
    <div id="projet-help" style="display:none" class="bg-light help">
    <ul>
     <li><b>Ajouter un nouveau projet:</b> Cliquez sur l'icône (+), saisissez le nom du nouveau projet et appuyez sur la touche ENTREE pour confirmer la création du projet.</li>
     <li><b>Modifier un nom de projet:</b> Double-cliquez sur un projet, saisissez son nouveau nom et appuyez sur la touche ENTREE pour confirmer la modification du nom du projet. <i>Note: il est impossible de renommer un projet avec le nom d'un autre projet existant. L'action sera bloquée.</i></li>
     <li><b>Annuler la modification:</b> Double-cliquez sur le champ de saisie de la modification en cours et appuyez sur la touche ENTREE.</li>
     <li><b>Supprimer un projet:</b> Sélectionnez le projet à supprimer et cliquez sur l'icône de suppression, ou double-cliquez sur le projet et videz le champ de saisie, puis appuyez sur la touche ENTREE.</li>
    </ul>
    </div>
   </div>
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
  echo "<input type='radio' class='btn-check' name='projets' id='$projet_id' autocomplete='off'>";
  echo "<label class='btn btn-secondary projet-btn' for='$projet_id'><span>".$projet->projet_name."</span><input type='text' maxlength='20' style='display:none'></label> ";
 }
?>

    <button class="btn btn-secondary rounded-circle mx-2 plus-btn" id="add_projet_btn" href="">+</button>
    <button type="button" class="btn btn-outline-danger rounded-circle pt-2 pb-2" style="display:none" id="del_projet_btn">
     <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16">
     <path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5m-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5M4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06m6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528M8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5"></path></svg> </button>
   </div>
  </fieldset>

  <fieldset id="tags-list" class="mx-auto border p-3">
   <div class='svg-container row'>
    <legend class='w-auto px-2'>Liste des tags</legend>
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-info-circle col-2 info-icon" viewBox="0 0 16 16" id="tag-help-btn">
    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
    <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
    </svg>
    <div id="tag-help" style="display:none" class="bg-light help">
    <ul>
     <li><b>Créer un nouveau tag:</b> Cliquez sur l'icône (+), saisissez le nom du nouveau tag et appuyez sur la touche ENTREE pour confirmer la création du tag. Vous pouvez sélectionner un tag existant dans le menu déroulant ou créer un nouveau tag.</li>
     <li><b>Modifier un tag:</b> Double-cliquez sur un tag, saisissez son nouveau nom et/ou modifiez sa couleur, puis appuyez sur la touche ENTREE pour confirmer la modification du tag. <i>Note: il est impossible de renommer un tag avec le nom d'un autre tag existant. L'action sera bloquée.</i></li>
     <li><b>Annuler la modification:</b> Double-cliquez sur le champ de saisie de la modification en cours et appuyez sur la touche ENTREE. <i>Note: la modification de la couleur n'est pas annulable avec cette méthode.</i></li>
     <li><b>Détacher un tag du projet courant:</b> Sélectionnez le tag à détacher et cliquez sur l'icône de suppression, ou double-cliquez sur le tag et videz le champ de saisie, puis appuyez sur la touche ENTREE.</li>
    </ul>
    </div>
   </div>
   <div class='form-group' id='tags'>
    <button class="btn btn-secondary rounded-circle plus-btn mx-2" id="add_tag_btn" href="">+</button>
    <button type="button" class="btn btn-outline-danger rounded-circle pt-2 pb-2" id="del_tag_btn" style="display:none">
     <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16">
     <path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5m-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5M4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06m6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528M8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5"></path></svg> </button>
   </div>
  </fieldset>


 <div class="row mx-auto" id="tag-reg-lex" style="display:none">
  <fieldset class="col-6 border p-3 active">
   <div class='svg-container row'>
    <legend class='w-auto px-2'>Liste des regex</legend>
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-info-circle col-2 info-icon" viewBox="0 0 16 16" id="regex-help-btn">
    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
    <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
    </svg>
    <div id="regex-help" style="display:none" class="bg-light help">
    <ul>
     <li><b>Créer une nouvelle regex:</b> Cliquez sur l'icône (+), saisissez la regex et sa description (facultatif) et appuyez sur la touche ENTREE pour confirmer la création de la regex.</li>
     <li><b>Modifier une regex:</b> Double-cliquez sur la regex, saisissez sa nouvelle valeur et description, puis appuyez sur la touche ENTREE pour confirmer la modification de la regex.</li>
     <li><b>Supprimer une ou plusieurs regex:</b> Sélectionnez toutes les regex à supprimer et cliquez sur l'icône de suppression, ou cliquez sur une regex et videz le premier champ de saisie, puis appuyez sur la touche ENTREE.</li>
     <li><b>Supprimer toutes les regex:</b> Double-cliquez sur l'icône de suppression.</li>
    </ul>
    </div>
   </div>
   <div class="form-group text-center mb-3">
    <div id="regex-form-group" style="display: none">
     <input type="text" id="regex-input" class="w-100" placeholder="Nouvelle regex" min-height="10vh" height="10vh">
     <input type="text" class="w-100 mt-2" id="regex-desc-input" placeholder="Description" min-height="10vh" height="10vh"></input>
     </div>
     <button class="btn btn-secondary rounded-circle mt-2 mx-2 plus-btn" id="add_regex_btn" href="">+</button>
     <button type="button" class="btn btn-outline-danger rounded-circle mt-2 pt-2 pb-2" id="del_regex_btn">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16">
      <path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5m-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5M4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06m6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528M8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5"></path></svg></button>
    </div>
     <!--<p>\d+km/h</p>-->
     <!-- version modifiable -->
    <div id='regex'>
     <div class='regex-entry'>
      <div class="bg-light w-100 hover-me mb-0 amt-1"><p>\d+km/h</p><input type="text" placeholder="Modifier la regex" value="" style="width: -moz-available; display: none"></div>
      <small class="show-me mb-0 ml-4">Vitesse en km/hblablabla blablabla blablabla</small>
      <input class="show-me mb-0 ml-4" type="text" placeholder="Description de la regex" style="width: -moz-available; display:none">
     </div>
    </div>
    <!-- Version d'origine
     <p class="bg-light w-100 hover-me mb-0 mt-1">\d+km/h</p>
     <small class="show-me mb-0 ml-4">Vitesse en km/h</small>
    -->
  </fieldset>
  <div class="col-1"></div> <!-- div d'espacement -->
  <fieldset class="col-5 border p-3 active">
   <div class='svg-container row'>
    <legend class='w-auto px-2'>Lexique</legend>
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-info-circle col-2 info-icon" viewBox="0 0 16 16" id="lexique-help-btn">
    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
    <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
    </svg>
    <div id="lexique-help" style="display:none" class="bg-light">
    <ul>
     <li><b>Ajouter du lexique:</b> Cliquez sur l'icône (+), saisissez les entrées du lexique (une entrée par ligne) et cliquez sur l'icône d'envoi pour confirmer l'ajout des nouvelles entrées au lexique.</li>
     <li><b>Modifier une entrée du lexique:</b> Double-cliquez sur une entrée, saisissez sa nouvelle valeur, puis appuyez sur la touche ENTREE pour confirmer la modification de l'entrée du lexique. <i>Note: il est impossible de modifier une entrée du lexique avec la valeur d'une autre entrée existante. L'action sera bloquée.</i></li>
     <li><b>Supprimer une ou plusieurs entrées du lexique:</b> Sélectionnez toutes les entrées du lexique à supprimer et cliquez sur l'icône de suppression, ou cliquez sur une entrée et videz le champ de saisie, puis appuyez sur la touche ENTREE.</li>
     <li><b>Supprimer tout le lexique:</b> Double-cliquez sur l'icône de suppression.</li>
    </ul>
    </div>
   </div>
   <div class="form-group text-center mb-3">
    <div class="superposable" id="lexique-form-group" style="display:none">
     <textarea class="w-100" id="lexique-input" placeholder="Ajouter des mots au lexique (un mot par ligne)" height="10vh"></textarea>
    <!-- icone upload -->
     <svg xmlns="http://www.w3.org/2000/svg" id="lexique-upload-btn" width="16" height="16" fill="currentColor" class="bi bi-upload objet-superpose" viewBox="0 0 16 16">
       <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>
       <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708z"/>
     </svg>
    </div><!-- fin de superposable -->
    <button class="btn btn-secondary rounded-circle mt-2 mx-2 plus-btn" id="add_lexique_btn" href="">+</button>
    <button type="button" class="btn btn-outline-danger rounded-circle mt-2 pt-2 pb-2" id="del_lexique_btn">
     <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16">
     <path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5m-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5M4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06m6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528M8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5"></path></svg></button>
   </div>
   <div class="form-group row mt-2" id="lexique">
     <div class="col-12 col-md-6 col-lg-4 col-xl-3 lexique-entry"><span class='lexique-text' style="display:none">Example</span><input type="text" class="lexique-input" value="au revoir" style="width:-moz-available"></div>
   </div>
  </fieldset>
 </div>


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
