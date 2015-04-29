$(document).ready(function() {

console.log('loaded...');
GetURLParameter();

/* Search Submit */
$("#search_submission").submit(function(e) {
    e.preventDefault();
    searchit();
});

/* Inputting Text */
$('#keyword').on('input', function() {
    // do something
		console.log('typing...');
    searchit();
		history.pushState({}, '', $(this).val());
   	return false;
});


function searchit() {
// If the field isn't empty do a search
  if ($("#keyword").val() != ""){

    console.log('submitted...');

    //empty the results 
    //$('.results').empty();
     $('.results').remove();

    //getJSON
    $.getJSON( "/venues?q="+$('#keyword').val(), function( data ) {

      // If there are results show them
      if (data.results != ""){

        var items = [];
    
        var ul = $('<section>').addClass('results').appendTo('body');
          $(data.results).each(function(index, item) {
            ul.append("<div class='result' id='" + item.id + "'><a class='card' href='/venues/"+item.UUID+".html'><h1>" + item.name +"</h1><span>" + item.UUID + "</span></a></div>")
          });

        }else{
          console.log('no joy');
         $('<section>').addClass('results dice').appendTo('body').html("<div class='dice'><h1>No dice :(</h1></div><!--dice-->");
        }

    //show results area 
    $('body').addClass('display');

    }); //getJSON
  } // If you submitted something
  else{
    $('body').removeClass('display');
    $('.results').remove();
  }
} //searchit();

function GetURLParameter(sParam)
  {
    console.log('getting URL');
    var sPageURL = window.location.search.substring(1);
     //console.log(sPageURL);
    var sURLVariables = sPageURL.split('&');
    for (var i = 0; i < sURLVariables.length; i++)
    {
        var sParameterName = sURLVariables[i].split('=');
        if (sParameterName[0] == sParam)
        {
            return sParameterName[1];
        }

    }
  } //GetURLParameter();

}); // $document