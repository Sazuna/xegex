import {selected_tag_name} from './tags.mjs';
import {selected_projet_name} from './projets.mjs'; //TODO

export function createNewLexiqueElements(mot, enregistre) {
 var inputElement = $("<input>").attr({
  type: "text",
  placeholder: "Modifier le mot",
  value: mot,
 }).css({"width":"-moz-available",
  "display":"none"})
 .addClass("lexique-input");
 /*css({"width":"100%",
  "width":"-moz-available", // Mozilla
  "width":"-webkit-fill-available",
  "width":"fill-available",
  "display":"none"}); */

 var textElement = $("<span>").text(mot).addClass("lexique-text");

 var divElement = $("<div>").addClass("col-12")
    .addClass("col-md-12")
    .addClass("col-lg-6")
    .addClass("col-xl-4")
    .addClass("lexique-entry")
    .append(inputElement)
    .append(textElement);

 // Variables pour l'update
 var old_mot = mot;

 inputElement.on('keypress', function(e) {
  /* Validation du nom de tag */
  if (e.keyCode == 13) {
   var mot = inputElement.val();
   var project_name = $('input[name="projets"]:checked').attr('id'); // TODO récupérer le projet qui est checked // TODO exporter la variable projet choisi
   // var project_name = selected_projet_name;
   var tag_name = selected_tag_name;
   if (project_name == undefined) {
    project_name = selected_project_name;
   }
   if (project_name != undefined) {
    project_name = project_name.replace("_", " ");
   }
   if (mot.length > 0) {
    if (mot != old_mot)
    // Modification dans l'input
    {
     $.ajax({
      url: "lexique.php",
      type: "post",
      datatype: "json",
      data: {"crud_method":"update",
             "old_mot": old_mot,
             "mot": mot,
             "tag_name": tag_name,
             "projet_name": project_name},
       // si projet_name est vide, pas grave car il s'agit d'un nouveau projet, sa date de création est quasi identique.
      success: function(response) {
       inputElement.css("display", "none");
       textElement.css("display", "inline");
       textElement.text(mot);
       old_mot = mot;
      }
     });
    }
    else {
     // Pas d'enregistrement de nouveau mot
     // car mot inchangé
     inputElement.css("display", "none");
     textElement.css("display", "inline");
    }
   }
   else { // Suppression
    $.ajax({
     url: "lexique.php",
     type: "post",
     datatype: "json",
     data: {"crud_method":"delete",
            "mot":old_mot,
            "tag_name":tag_name,
            "projet_name":project_name
     }, success: function() {
      // Sélection de la balise parent (lexique-entry)
      //var div_to_del = $('.lexique-entry').has('.lexique-text:contains("'+old_mot+'")'); 
      var div_to_del = $('.lexique-entry').filter(function() {
       return $(this).find('.lexique-text').text() === old_mot;
      });
      // Suppression de cette balise et de ses enfants
      div_to_del.empty();
      div_to_del.remove();
     }
    });
    //delLexique(mot);
   }
  }
 });

 // Pas de double click pour les entrées du lexique
 divElement.on('click', function() {
  if (!enregistre) return;
  //enregistre = false;
  inputElement.val(textElement.text());
  inputElement.css("display", "inline");
  textElement.css("display", "none");
 });

 return {
  inputElement: inputElement,
  textElement: textElement,
  divElement: divElement
 };
}

// Récupération du lexique
export function getLexiqueForTag(tagName) {
 // Efface les tags
 return new Promise(function(resolve, reject) {
  $.ajax({
   url:"lexique.php",
   type:"post",
   datatype:"json",
   data:{"crud_method":"select",
    "tag_name":tagName},
   success: function(response) {
    resolve(response);
   },
   error: function(xhr, status, error) {
    console.log("Erreur lors de la récupération du lexique:", status, error);
    reject(error);
   }
  });
 });
}

// Affichage et récupération du lexique
export function getAllLexique() {
 $("#tag-reg-lex").css("display", "flex"); 
 // TODO supprimer les toutes les entry
 $("#lexique").find(".lexique-entry").remove();
 getLexiqueForTag(selected_tag_name)
 .then(function(lexiqueList) {
  lexiqueList.forEach(function(lexique) {
   var lexiqueElements = createNewLexiqueElements(lexique['mot'], true);
   // Insère les éléments dans le DOM
   $("#lexique").append(lexiqueElements.divElement);
   lexiqueElements.inputElement.css("display", "none");
  });
 });
}

$(document).ready(function() {
 let lexique_input = $("#lexique-input");

 // Apparition du textearea
 $("#add_lexique_btn").on('click', function() {
  $("#lexique-form-group").css('display', 'block');
  $("#add_lexique_btn").css('display', 'none');
  $("#del_lexique_btn").css('display', 'none');
 });

 $("#lexique-upload-btn").on('click', function() {
  var new_mots = lexique_input.val();
  if (new_mots.length == 0) { return; }
  //new_mots = new_mots.split('\n');
  new_mots = new_mots.split('\n').map(function(mot) {
   // Supprime les espaces en début et fin de chaque mot
   return mot.trim();
  }).filter(function(mot) {
   // Retourne true pour conserver les mots non vides
   return mot !== '';
  });
  var project_name = $('input[name="projets"]:checked');
  if (project_name != undefined) {
   project_name = project_name.attr('id');
  }
  else
  {
   project_name = selected_project_name;
  }

  /*var tag_name = $('input[name="tags"]:checked').attr('id').substring(4);
  if (tag_name == "") {
   // Cela veut dire que le tag vient d'être ajouté dans Tags.js
   // Il n'est alors pas connu du DOM.
  }*/

  var tag_name = selected_tag_name;
  $.ajax({
   url: "lexique.php",
   type: "post",
   datatype: "json",
   data: {"crud_method":"insert",
          "projet_name": project_name,
          "tag_name": tag_name,
          "mots": new_mots},
   success: function(response) {
    lexique_input.val("");
    // On récupère les mots qui ont réussi à être insérés
    // et on met à jour l'affichage afin que ce soit trié
    // par ordre alphabétique
    getAllLexique();
    /*
    new_mots.forEach(function(mot) {
     var lexiqueElements = createNewLexiqueElements(mot, true);
     $("#lexique").append(lexiqueElements.divElement);
     lexiqueElements.inputElement.css("display", "none");
    });*/
    $("#lexique-form-group").css('display', 'none');
    $("#add_lexique_btn").css('display', 'inline');
    $("#del_lexique_btn").css('display', 'inline');
   }
  });
 });

 ////////// Suppression /////////////
 $("#del_lexique_btn").click(function() {
  // Suppression du lexique qui est sélectionné
  var selected = $(".lexique-input").filter(function() { return $(this).css("display") === "inline";});
  selected.each(function() {
   delLexique($(this).val());
  });
 });

 function delLexique(mot) {
  var tag_name = selected_tag_name;
  var project_name = $('input[name="projets"]:checked').attr('id');
  if (project_name == undefined) {
   project_name = selected_project_name;
  }
  if (project_name != undefined) {
   project_name = project_name.replace("_", " ");
  }
  $.ajax({
   url: "lexique.php",
   type: "post",
   datatype: "json",
   data: {"crud_method":"delete",
          "mot":mot,
          "tag_name":tag_name,
          "projet_name":project_name
    }, success: function() {
    // Sélection de la balise parent (lexique-entry)
    var div_to_del = $('.lexique-entry').filter(function() {
     return $(this).find('.lexique-text').text() === mot;
    });
    //var div_to_del = $('.lexique-entry').has('.lexique-text:contains("'+mot+'")'); 
    // Suppression de cette balise et de ses enfants
    div_to_del.empty();
    div_to_del.remove();
   }
  });
 }

 $("#del_lexique_btn").on('dblclick', function() {
  $("#confirmation-del-lexique").fadeIn();
 });

 $("#confirm-del-lexique-btn").click(function() {
  $("#confirmation-del-lexique").fadeOut();
  var selected = $(".lexique-input");
  selected.each(function() {
   delLexique($(this).val());
  });
 });

 $("#cancel-del-lexique-btn").click(function() {
  $("#confirmation-del-lexique").fadeOut();
 });

 ////////////////// INFO ////////////////
 $("#lexique-help-btn").click(function() {
  $("#lexique-help").toggle();
 });

});
