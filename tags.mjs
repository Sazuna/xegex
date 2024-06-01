// Définition de fonctions exportables avant $(document).ready

import {getAllLexique} from "./lexique.mjs";
import {getAllRegex} from "./regex.mjs";

// import {selected_projet_name} from "./projects.mjs";

let selected_tag_name = ''; // Valeur initiale (aucun tag sélectionné) 

export {selected_tag_name};

export function createNewTagElements(label, color, enregistre, is_new_tag) {
 var label_id = label.replace(/\s/g, "_");
 var placeholderInput = "Nouveau tag";
 if (!is_new_tag) {
  placeholderInput = "Renommer le tag " + label;
 }
 var inputElement = $("<input>").attr({
  type: "text",
  id: "tag-input-" + label_id,
  placeholder: placeholderInput,
  list: "existing-tags",
 });


 if (is_new_tag) {
  var datalistElement = $("<datalist>").attr({
   id: "existing-tags"});
  // Récupération des tags existants pour les mettre dans le menu déroulant
  var project_name = $('input[name="projets"]:checked');
  if (project_name != undefined) {
   project_name = project_name.attr('id');
   project_name = project_name.replace(/_/g, " ");
  }
  else {
   project_name = selected_projet_name;
  }

  var otherTags = [];

  $.ajax({
   url: "tags.php",
   type: "post",
   datatype: "json",
   data: {"crud_method":"select_not",
          "projet_name": project_name,
   },
   success: function(tagsList) {
    datalistElement.empty();
    console.log(tagsList);
    tagsList.forEach(function(tag) {
     var option = $("<option>").attr({
      value: tag["label"],
      text: tag["label"]});
      otherTags.push(tag["label"]); // Enregistrement des tags dans une variable
     datalistElement.append(option);
    })
   }
  });
 }

 var colorElement = $("<input>").attr({
  type: "color",
  id: "new_tag_color",
  value: color
 }).css({"margin-bottom":"3px"
  });

 var editDivElement = $("<div>").append(inputElement).append(colorElement);
 if (is_new_tag) {
  editDivElement.append(datalistElement);
 }

 var textElement = $("<span>").text(label);

 // Variables pour l'update
 var old_tag_name = label;

 var labelElement = $("<label>").addClass("btn btn-secondary tag-btn")
                                   .attr("for", "tag-" + label_id)
                                   .append(editDivElement).append(textElement)
                                   .css("background-color", color)
                                   .css("border-color", color);

 var radioButton = $("<input>").addClass("btn-check")
                                  .attr({
                                      type: "radio",
                                      name: "tags",
                                      id: "tag-" + label_id,
                                      autocomplete: "off"
                                  });

 inputElement.on('keypress', function(e) {
  /* Validation du nom de tag */
  if (e.keyCode == 13) {
   var tag_name = inputElement.val();
   var tag_color = colorElement.val();
   var project_name = $('input[name="projets"]:checked').attr('id'); // TODO récupérer le projet qui est checked
   project_name = project_name.replace("_", " ");
   if (tag_name.length > 0) {
    if (!enregistre) {
     // Enregistrement du nom de tag
     $.ajax({
      url: "tags.php",
      type: "post",
      datatype: "json",
      data: {"crud_method":"insert",
             "projet_name": project_name,
             "tag_name": tag_name,
             "tag_color": tag_color},
      success: function(response) {
       // Si le tag existait déjà, récupère sa couleur
       if(response["tag_color"]) {
        tag_color = response["tag_color"];
        colorElement.val(tag_color);
       }
       textElement.text(tag_name);
       var tag_id = tag_name.replace(/\s/g, "_");
       radioButton.attr("id", tag_id);
       labelElement.attr("for", tag_id);

       // labelElement.remove(inputElement);
       inputElement.css("display", "none");
       colorElement.css("display", "none");
       $("#add_tag_btn").css("display", "inline");
       labelElement.css("background-color", colorElement.val());
       labelElement.css("border-color", colorElement.val());
       enregistre = true;
       old_tag_name = tag_name;
       inputElement.attr({'id':"tag-"+tag_name});
       placeholderInput = "Renommer le tag " + label;
       inputElement.attr({'placeholder':placeholderInput});
       if (is_new_tag) {
        editDivElement.children('datalist').remove();//datalistElement);
        inputElement.removeAttr('list'); // Supprime la dataliste du tag
       }
      }
     });
    }
    else {
    // Il s'agit d'une modification
     $.ajax({
      url: "tags.php",
      type: "post",
      datatype: "json",
      data: {"crud_method":"update",
             "old_tag_name": old_tag_name,
             "tag_name": tag_name,
             "tag_color": tag_color},
      success: function(response) {
       inputElement.css("display", "none");
       colorElement.css("display", "none");
       textElement.css("display", "inline");
       textElement.text(tag_name);
       labelElement.css("background-color", colorElement.val());
       labelElement.css("border-color", colorElement.val());
       enregistre = true;
       placeholderInput = "Renommer le tag " + label;
       inputElement.attr({'placeholder':placeholderInput});

       $("#tag-" + old_tag_name).attr({'id': "tag-"+tag_name});
       $("label[for='tag-"+old_tag_name+"']").attr({'for': "tag-"+tag_name});
       radioButton.attr("id", "tag-"+tag_name);
       labelElement.attr("for", "tag-"+tag_name);
       old_tag_name = tag_name;
      }
     });
    }
   }
   else // Suppression
   {
    var message="Voulez-vous vraiment détacher le tag " + old_tag_name + " du projet ? Les tags détachés existent toujours mais ne sont plus liés au projet.";
    $("#message-suppression-tag").text(message);
    $("#confirmation-del-tag").fadeIn();
   }
  }
 });

 labelElement.on('dblclick', function() {
  if (!enregistre) return;
  inputElement.val(textElement.text());
  inputElement.css("display", "inline");
  colorElement.css("display", "inline");
  textElement.css("display", "none");
 });

 labelElement.on('click', function() {
  if (!enregistre) return;
  selected_tag_name = labelElement.text();
  $("#tag-reg-lex").css("display", "flex");
  $("#del_tag_btn").css("display", "inline");
  getAllLexique();
  getAllRegex();
 });

 return {
  inputElement: inputElement,
  colorElement: colorElement,
  labelElement: labelElement,
  radioButton: radioButton
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
///////////////////////////////////////// CRUD TAGS ////////////////////////////////////

// Affichage et récupération des tags du projet
// Récupération des tags d'un projet existant
$('.projet-btn').click(function () {
 $("#tags-list").addClass("active"); 
 $("#tags").find("label").remove();
 $("#tags").find("input").remove();
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
   $("#add_tag_btn").before(tagElements.radioButton, tagElements.labelElement);
   tagElements.inputElement.css("display", "none");
   tagElements.colorElement.css("display", "none");
  });
 });
});

// Ajoute le nouveau bouton tag
$('#add_tag_btn').click(function (e) {
 e.preventDefault(); // Pas le bouton d'envoi du formulaire
 var tagElements = createNewTagElements("", "#6c757d", false, true);

 // Insère les éléments dans le DOM
 $(this).before(tagElements.radioButton, tagElements.labelElement);
 $('#add_tag_btn').css("display","none");
});

$('.tag-btn').dblclick(function(e) {
 var text = $(this).find('span');
 text.css("display", "none");
 var input = $(this).children('input').first();
 var color = $(this).children('input').last();
 input.css("display", "inline");
 input.val(text.text());
 color.css("display", "inline");
 // Validation de la modification
 input.on('keypress', function(e) {
  if (e.keyCode == 13) {
   var old_tag_name = text.text();
   var tag_name = input.val();
   if (tag_name.length > 0) {
    $.ajax({
     url: "projets.php",
     type: "post",
     datatype: "json",
     data: {"crud_method":"update",
            "old_tag_name": old_tag_name,
            "tag_name": tag_name},
     success: function(response) {
      input.css("display", "none");
      text.css("display", "inline");
      text.text(tag_name);
      input.attr({'id': "tag-input-"+tag_name});
      $("#tag-" + old_tag_name).attr({'id': "tag-"+tag_name});
      $("label[for='tag-"+old_tag_name+"']").attr({'for': "tag-"+tag_name});
     }
    });
   }
   else {
    var message="Voulez-vous vraiment détacher le tag " + old_tag_name + " du projet ? Les tags détachés existent toujours mais ne sont plus liés au projet.";
    $("#message-suppression-tag").text(message);
    $("#confirmation-del-tag").fadeIn();
   }
  }
 });
});

////////////////////// Suppression //////////////////////////
$("#del_tag_btn").click(function() {
 var tag_to_del = $('input[name="tags"]:checked');
 if (tag_to_del != undefined) {
  tag_to_del = tag_to_del.attr('id');
  tag_to_del = tag_to_del.replace(/_/g, " ").substring(4);
  //tag_to_del = tag_to_del.parent().next().text();
 }
 else {
  tag_to_del = selected_tag_name;
 }
 if (tag_to_del != undefined) {
  var message="Voulez-vous vraiment détacher le tag " + tag_to_del + " du projet ? Les tags détachés existent toujours mais ne sont plus liés au projet.";
  $("#message-suppression-tag").text(message);
  $("#confirmation-del-tag").fadeIn();
 }
});

$('#cancel-del-tag-btn').click(function() {
 $('#confirmation-del-tag').fadeOut();
});

$('#confirm-del-tag-btn').click(function() {
 // Ajax suppression projet
 var tag_to_del = $('input[name="tags"]:checked');
 if (tag_to_del != undefined) {
  tag_to_del = tag_to_del.attr('id');
  tag_to_del = tag_to_del.replace(/_/g, " ").substring(4);
  //tag_to_del = tag_to_del.parent().next().text();
 }
 else {
  tag_to_del = selected_tag_name;
 }
 var project_name = $('input[name="projets"]:checked');
 if (project_name != undefined) {
  project_name = project_name.attr('id');
  project_name = project_name.replace(/_/g, " ");
 }
 else {
  project_name = selected_projet_name;
 }
 $.ajax({
 url: "tags.php",
 type: "post",
 datatype: "json",
 data: {"crud_method":"delete",
        "projet_name": project_name, // Pour la mise à jour de la date de modif du projet
        "tag_name": tag_to_del},
 success: function(response) {
  $('input[name="tags"]:checked').next().remove();
  $('input[name="tags"]:checked').remove();
  $("#del_tag_btn").css("display", "none");
  $("#tag-reg-lex").css("display", "none");
 }
 });
 $('#confirmation-del-tag').fadeOut();

});
////////////////// INFO ////////////////
$("#tag-help-btn").click(function() {
 $("#tag-help").toggle();
});


});
