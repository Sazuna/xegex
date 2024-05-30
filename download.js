$(document).ready(function() {
 $('#download-xml').click(function() {
  console.log('Download XML');
  var corpus = $("#corpus");
  var copie = $("<div>").html(corpus.html());
  copie.find("br").remove(); // Suppression des balises <br>
  var spans = copie.find("span");
  spans.each(function() {
   var name = $(this).attr('name').replaceAll(' ', '-').replaceAll('_', '');
   var balise = $("<" + name + ">" + $(this).html() + '</' + name + '>');
   $(this).replaceWith(balise);
  });
  var corpus_xml = "<?xml version='1.0' encoding='utf-8'?>\n<corpus>"+copie.html()+"</corpus>";

  var blob = new Blob([corpus_xml], {type:"text/xml;charset='utf-8'"});

  var link = document.createElement('a');
  link.href = window.URL.createObjectURL(blob);

  var titre = $("#titre").text();
  var extensionIndex = titre.lastIndexOf('.');
  if (extensionIndex !== -1) {
    titre = titre.substring(0, extensionIndex);
  }
  link.download = titre + '.xml';

  document.body.appendChild(link);
  link.click();

  document.body.removeChild(link);
 });


 $('#download-html').click(function() {
  console.log('Download HTML');
  var corpus = $("#corpus").html();
  var titre = $("#titre").text();
  var extensionIndex = titre.lastIndexOf('.');
  if (extensionIndex !== -1) {
    titre = titre.substring(0, extensionIndex);
  }

  corpus = "<!DOCTYPE html><html><head><title>"+titre+"</title><meta charset='utf-8'>" +
	'<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"></head><body>' +  corpus + "</body></html>";
  var blob = new Blob([corpus], {type:"text/html;charset='utf-8'"});

  var link = document.createElement('a');
  link.href = window.URL.createObjectURL(blob);

  link.download = titre + '.html';

  document.body.appendChild(link);
  link.click();

  document.body.removeChild(link);
 });

 $("span").hover(function() {
  var popup = $("<div>").text($(this).attr('name')).addClass('popup').addClass('btn-light')
   .css('text-align', 'center').css("padding", "5px").css("border-radius", ".25rem");

  $("main").after(popup);
  //$(this).after(div);
  // placement au-dessus
  var offset = $(this).offset();
  var popupHeight = popup.outerHeight();
  var popupWidth = $(this).outerWidth();
  popup.css({position: 'absolute',
             top: offset.top - popupHeight,
             left: offset.left,
             "min-width": popupWidth,
             //height: popupHeight,
             display: 'block'});
 }).mouseleave(function() {
  $('.popup').remove();
 });
});
