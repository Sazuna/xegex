import { getTagsForProjet, createNewTagElements } from "./tags_sort.mjs";

let selected_projet_name = "";

export {selected_projet_name};

$(document).ready(function() {
 // const tags_js = require("./tags.js");

 /////////////////////////////////////// CRUD PROJET /////////////////////////////////////////
 // Créez une fonction pour créer les éléments du projet
 function createNewProjectElements() {
  var inputElement = $("<input>").attr({
   type: "text",
   id: "new_project_input",
   placeholder: "Nouveau projet"
  });

  var textElement = $("<span>");

  // Variables pour l'update
  var enregistre = false;
  var old_project_name = "";

  var labelElement = $("<label>").addClass("btn btn-secondary new-projet-btn")
    .attr("for", "new-projet")
    .append(inputElement).append(textElement);

  var radioButton = $("<input>").addClass("btn-check")
   .attr({
    type: "radio",
    name: "projets",
    id: "new-projet",
    autocomplete: "off"
   });

  labelElement.on('click', function() {
   /* Sélection du nouveau projet */
   if (!enregistre) return;
   $("#tags-list").addClass("active");
   $("#tags").find("label").remove();
   $("#tags").find("input").remove();
   $("#del_projet_btn").css("display", "inline");
   $("#del_tag_btn").css("display", "none");
   $("#tag-reg-lex").css("display", "none");
   var projetName = inputElement.val();
   selected_projet_name = projetName; // variable globale exportée
   getTagsForProjet(projetName)
   .then(function(tagsList) {
    tagsList.forEach(function(tag) {
     var tagElements = createNewTagElements(tag['label'], tag['color'], true, false);
     $("#add_tag_btn").before(tagElements.radioButton, tagElements.labelElement);
     tagElements.inputElement.css("display", "none");
     tagElements.colorElement.css("display", "none");
    });
   });
  });

  return {
   labelElement: labelElement,
   radioButton: radioButton
  };
 }

 $('#add_projet_btn').click(function (e) {
  e.preventDefault(); // Pas le bouton d'envoi du formulaire
  var projectElements = createNewProjectElements();

  // Insère les éléments dans le DOM
  $(this).before(projectElements.radioButton, projectElements.labelElement);
  $('#add_projet_btn').css("display","none");
 });


 ////////////////////// Suppression //////////////////////////
 $("#del_projet_btn").click(function() {
  var project_to_del = $('input[name="projets"]:checked');
  if (project_to_del != undefined) {
   project_to_del = project_to_del.attr('id');
   project_to_del = project_to_del.replace(/_/g, " ");
  }
  else {
   project_to_del = selected_projet_name;
  }
  if (project_to_del != undefined) {
   var message="Voulez-vous vraiment supprimer le projet " + project_to_del + " ? Les tags associés ne seront pas supprimés.";
   $("#message-suppression").text(message);
   $("#confirmation-del-projet").fadeIn();
  }
 });

 $('#cancel-del-projet-btn').click(function() {
  $('#confirmation-del-projet').fadeOut();
 });

 $('#confirm-del-projet-btn').click(function() {
  // Ajax suppression projet
  var project_to_del = $('input[name="projets"]:checked');
  if (project_to_del != undefined) {
   project_to_del = project_to_del.attr('id');
   project_to_del = project_to_del.replace(/_/g, " ");
  }
  else {
   project_to_del = selected_projet_name;
  }
  $.ajax({
  url: "projets.php",
  type: "post",
  datatype: "json",
  data: {"crud_method":"delete",
         "projet_name": project_to_del},
  success: function(response) {
   console.log("projet supprimé");
   $('input[name="projets"]:checked').next().remove();
   $('input[name="projets"]:checked').remove();
   $("#del_projet_btn").css("display", "none");
   $("#tag-reg-lex").css("display", "none");
   $("#tags-list").removeClass("active");
  
  }
 });
 $('#confirmation-del-projet').fadeOut();

});

 ////////////////// INFO ////////////////
 $("#projet-help-btn").click(function() {
  $("#projet-help").toggle();
 });

});
