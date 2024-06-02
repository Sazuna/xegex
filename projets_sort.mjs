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
});
