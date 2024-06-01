import {selected_tag_name} from './tags.mjs';
import {selected_projet_name} from './projets.mjs';

export function createNewRegexElements(regex, description) {
 var divElement = $("<div>").addClass('regex-entry'); // ajouter regexElement; descElement et descInputElement
 var regexElement = $("<div>").addClass('bg-light')
  .addClass('w-100')
  .addClass('mb-0')
  .addClass('amt-1')
  .addClass('hover-me'); // Ajouter regexInputElement et regexDivElement
 var regexTextElement = $("<p>").text(regex).addClass('px-2');
 var regexInputElement = $("<input>").attr({
  type: "text",
  placeholder: "Modifier la regex",
  value: regex,
 }).css({"width":"-moz-available",
  "display":"none"})
  .addClass('regex-input');
 /*css({"width":"100%",
  "width":"-moz-available", // Mozilla
  "width":"-webkit-fill-available",
  "width":"fill-available",
  "display":"none"}); */

 var descriptionTextElement = $("<small>")
  .addClass("show-me")
  .addClass("mb-0")
  .addClass("ml-4")
  .html(description);

 // Bouton d'ajout de description si la description est vide
 //var addDescriptionButton = $("<button>")
 //   .addClass("btn");

 var descriptionInputElement = $("<input>")
  .addClass("show-me")
  .addClass("mb-0")
  .addClass("ml-4")
  .attr({
   type: "text",
   placeholder: "Description de la regex",
   value: description,
  }).css({"display": "none"});
  //.css({"width": "-moz-available",
  //      "display":"none"});

 divElement.append(regexElement);
 divElement.append(descriptionTextElement);
 divElement.append(descriptionInputElement);
 regexElement.append(regexTextElement);
 regexElement.append(regexInputElement);

 // Variables pour l'update
 var old_regex = regex;
 var old_description = description;

 // ajout de l'événement update sur keypress (entry)
 regexInputElement.on('keypress', function(e) {update(e);});
 descriptionInputElement.on('keypress', function(e) {update(e);});
 function update(e) {
  if (e.keyCode == 13) {
   var regex = regexInputElement.val();
   var description = descriptionInputElement.val();
   var project_name = $('input[name="projets"]:checked');
   if (project_name != undefined) {
    project_name = project_name.attr('id');
    project_name = project_name.replace("_", " ");
   }
   else { // undefined
    project_name = selected_project_name;
   }
   var tag_name = selected_tag_name;
   if (regex.length > 0) {
    if (regex != old_regex || description != old_description)
    // Modification dans l'input
    {
     $.ajax({
      url: "regex.php",
      type: "post",
      datatype: "json",
      data: {"crud_method":"update",
             "old_regex": old_regex,
             "regex": regex,
             "old_description": old_description,
             "description": description,
             "tag_name": tag_name,
             "projet_name": project_name},
       // si projet_name est vide, pas grave car il s'agit d'un nouveau projet, sa date de création est quasi identique.
      success: function(response) {
       regexInputElement.css("display", "none");
       regexTextElement.css("display", "block");
       regexTextElement.text(regex);
       descriptionInputElement.css("display", "none");
       descriptionTextElement.css("display", "block");
       descriptionTextElement.text(description);
       old_regex = regex;
       old_description = description;
      }
     });
    }
    else {
     // Pas d'enregistrement de nouvelle regex
     // car regex inchangée
     regexInputElement.css("display", "none");
     regexTextElement.css("display", "block");
     descriptionInputElement.css("display", "none");
     descriptionTextElement.css("display", "block");
    }
   } else { // Suppression
    $.ajax({
     url: "regex.php",
     type: "post",
     datatype: "json",
     data: {"crud_method":"delete",
      "regex":old_regex,
      "tag_name":tag_name,
      "projet_name":project_name
     }, success: function() {
      // Sélection de la balise parent (.regex-entry)
      //var div_to_del = $('.regex-entry').has('p:contains('+regex+')'); 
      var div_to_del = $('.regex-entry').filter(function() {
       return $(this).find('p').text() === old_regex;
      });
      // Suppression de cette balise et de ses enfants
      div_to_del.empty();
      div_to_del.remove();
     }
    });
    //delRegex(regex);
   }
  }
 } // fin de la fonction update

 regexTextElement.on('click', function() {editMode();});
 descriptionTextElement.on('click', function() {editMode();});
 function editMode() {
  descriptionInputElement.val(descriptionTextElement.text());
  descriptionInputElement.css("display", "inline");
  descriptionTextElement.css("display", "none");
  regexInputElement.val(regexTextElement.text());
  regexInputElement.css("display", "block");
  regexTextElement.css("display", "none");
  }

 return {
  regexInputElement: regexInputElement,
  regexTextElement: regexTextElement,
  descriptionInputElement: descriptionInputElement,
  descriptionTextElement: descriptionTextElement,
  regexElement: regexElement,
  divElement: divElement
 };
}

// Récupération des Regex
export function getRegexForTag(tagName) {
 // Efface les tags
 return new Promise(function(resolve, reject) {
  $.ajax({
   url:"regex.php",
   type:"post",
   datatype:"json",
   data:{"crud_method":"select",
    "tag_name":tagName},
   success: function(response) {
    resolve(response);
   },
   error: function(xhr, status, error) {
    console.log("Erreur lors de la récupération des regex:", status, error);
    reject(error);
   }
  });
 });
}

// Affichage et récupération des Regex
export function getAllRegex() {
 $("#tag-reg-lex").css("display", "flex"); 
 // supprimer les toutes les regex-entry
 $("#regex").find(".regex-entry").remove();
 getRegexForTag(selected_tag_name)
 .then(function(regexList) {
  regexList.forEach(function(regex) {
   var regexElements = createNewRegexElements(regex['regex'], regex['description']);
   // Insère les éléments dans le DOM
   $("#regex").append(regexElements.divElement);
   //regexElements.inputElement.css("display", "none");
  });
 });
}

$(document).ready(function() {
 let lexique_input = $("#lexique-input");

 // Apparition du textearea
 $("#add_regex_btn").on('click', function() {
  $("#regex-form-group").css('display', 'block');
  $("#add_regex_btn").css('display', 'none');
 });

 $("#regex-form-group").on('keypress', function(e) {
  /* Validation du nom de tag */
  if (e.keyCode == 13) {
   var regex = $("#regex-input").val();
   var description = $("#regex-desc-input").val();
   if (regex.length == 0) { return; }
   var project_name = $('input[name="projets"]:checked').attr('id');
   var tag_name = $('input[name="tags"]:checked');
   if (tag_name != undefined) {
    tag_name = tag_name.attr('id').substring(4);
    tag_name = tag_name.replace("_", " ");
   }
   else {
    // Cela veut dire que le tag vient d'être ajouté dans Tags.js
    // Il n'est alors pas connu du DOM.
    tag_name = selected_tag_name;
   }
   // Nouvelle regex
   $.ajax({
    url: "regex.php",
    type: "post",
    datatype: "json",
    data: {"crud_method":"insert",
     "regex": regex,
     "description": description,
     "projet_name": project_name,
     "tag_name": tag_name},
   // si projet_name est vide, pas grave car il s'agit d'un nouveau projet, sa date de création est quasi identique.
    success: function(response) {
     $("#regex-input").val("");
     $("#regex-desc-input").val("");
     $("#regex-form-group").css('display', 'none');
     $("#add_regex_btn").css('display', 'inline');

     // ajouter la regex en haut de la balise
     var regexElements = createNewRegexElements(regex, description);
     $("#regex").prepend(regexElements.divElement); // prepend ajoute comme 1er enfant du noeud
    }
   });
  
  }
 });

 ////////// Suppression /////////////
 $("#del_regex_btn").click(function() {
  // Suppression des regex qui sont sélectionnées
  var selected = $(".regex-input").filter(function() { return $(this).css("display") === "block";});
  selected.each(function() {
   delRegex($(this).val());
  });
 });

 function delRegex(regex) {
  var tag_name = selected_tag_name;
  var project_name = $('input[name="projets"]:checked').attr('id');
  if (project_name == undefined) {
   project_name = selected_project_name;
  }
  if (project_name != undefined) {
   project_name = project_name.replace("_", " ");
  }
  $.ajax({
   url: "regex.php",
   type: "post",
   datatype: "json",
   data: {"crud_method":"delete",
    "regex":regex,
    "tag_name":tag_name,
    "projet_name":project_name
   }, success: function() {
    // Sélection de la balise parent (.regex-entry)
    var div_to_del = $('.regex-entry').filter(function() {
     return $(this).find('p').text() === regex;
    });
    //var div_to_del = $('.regex-entry').has('p:contains('+regex+')'); 
    // Suppression de cette balise et de ses enfants
    div_to_del.empty();
    div_to_del.remove();
   }
  });
 }

 $("#del_regex_btn").on('dblclick', function() {
  $("#confirmation-del-regex").fadeIn();
 });

 $("#confirm-del-regex-btn").click(function() {
  $("#confirmation-del-regex").fadeOut();
  var selected = $(".regex-input");
  selected.each(function() {
   delRegex($(this).val());
  });
 });

 $("#cancel-del-regex-btn").click(function() {
  $("#confirmation-del-regex").fadeOut();
 });

 ////////////////// INFO ////////////////
 $("#regex-help-btn").click(function() {
  $("#regex-help").toggle();
 });

});
