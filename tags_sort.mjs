// Définition de fonctions exportables avant $(document).ready

import {selected_projet_name} from "./projets_sort.mjs";

let selected_tag_name = ''; 

export {selected_tag_name};

export function createNewTagElements(label, color, enregistre, is_new_tag) {
 var label_id = label.replace(/\s/g, "_");

 var radioButton = $("<input>").addClass("form-check-input")
      .attr({
       type: "checkbox",
       name: "tags[]", // Pour récupérer la liste des tags dans le php après submit
       id: "tag-" + label_id,
       autocomplete: "off",
       value: label_id,
       checked: "true"
      })
      .css('background-color', color)
      .css('border-color', color); // ajout des couleurs

 var textElement = $("<span>").text(label);

 var labelElement = $("<label>").addClass("btn btn-secondary tag-btn form-check-label")
      .attr("for", "tag-" + label_id)
      .append(textElement)
      .css("background-color", color)
      .css("border-color", color);

 var divElement = $("<div>").addClass("btn btn-secondary tag-btn")
      .css("background-color", color)
      .css("border-color", color)
      .css("margin", "0.5em")
      .css("padding", "1em")
      .css("padding-top", "0")
      .css("padding-bottom", "0")
      .css("display", "inline-flex")
      .css("align-items", "center")
      .css("justify-content", "center")
      .append(radioButton).append(labelElement);


 return {
  labelElement: labelElement,
  textElement: textElement,
  radioButton: radioButton,
  divElement: divElement
 };
}

// Fonction exportable
export function getTagsForProjet(projetName) {
 // Efface les tags
 return new Promise(function(resolve, reject) {
  $.ajax({
   url:"tags.php",
   type:"post",
   datatype:"json",
   data:{"crud_method":"select",
    "projet_name":projetName},
   success: function(response) {
    resolve(response);
   },
   error: function(xhr, status, error) {
    console.log("Erreur lors de la récupération des tags:", status, error);
    reject(error);
   }
  });
 });
}


$(document).ready(function() {
 // Affichage et récupération des tags du projet
 // Récupération des tags d'un projet existant
 $('.projet-btn').click(function () {
  $("#tags-list").addClass("active"); 
  $("#file-import").addClass("active"); 
  //$("#tags").find("label").remove();
  //$("#tags").find("input").remove();
  //$("#tags").find("div").remove();
  $("#sortable").empty();
  $("#tag-reg-lex").css("display", "none");
  $("#del_projet_btn").css("display", "inline");
  $("#del_tag_btn").css("display", "none");
  var projetName = $(this).text();
  if (projetName == "") { // projet nouvellement créé
   projetName = selected_projet_name;
  }
  getTagsForProjet(projetName)
  .then(function(tagsList) {
   tagsList.forEach(function(tag) {
    var tagElements = createNewTagElements(tag['label'], tag['color'], true, false);
    // Insère les éléments dans le DOM
    //$("#add_tag_btn").before(tagElements.divElement);//tagElements.radioButton, tagElements.labelElement);
    $("#sortable").append(tagElements.divElement);//tagElements.radioButton, tagElements.labelElement);
   });
  });
  /////////////////// TRI/////////////////
  var sortableList = document.getElementById('sortable');
  new Sortable(sortableList, {
   animation: 150,
   ghostClass: 'sortable-ghost',
   onEnd: function (evt) {
    //console.log('Element déplacé de l\'index', evt.oldIndex, 'à l\'index', evt.newIndex);
   }
  });
 });

 ////////////////// INFO ////////////////
 $("#tag-help-btn").click(function() {
  $("#tag-help").toggle();
 });
});
