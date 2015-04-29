$(document).ready(function() {

console.log('loaded...');

// get the location
var dirs = window.location.pathname.substring(1);

console.log(dirs);
searchit();

/* Search Submit */
$("#search_submission").submit(function(e) {
    e.preventDefault();
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
  if ($("#keyword").val() != "" || dirs != "" ){

    console.log('submitted...');

    //empty the results 
     $('.results').empty();
     $('.results').remove();

     if ($("#keyword").val() != ""){ 
      var term = $('#keyword').val(); 
     } 
     else if (dirs != "") { 
      var term = dirs; 
     }

      //getJSON
      $.getJSON( "/venues?q="+term, function( data ) {

      // If there are results show them
      if (data.results != ""){

        var items = [];
    
        var ul = $('<section>').addClass('results').appendTo('body');
          $(data.results).each(function(index, item) {
            ul.append("<div class='result' id='" + item.id + "'><a class='card' href='/venues/"+item.UUID+".html'><h1>" + item.name +"</h1><p><span>"+ item.address1 + "</span><span>" + item.address2 + "</span><span>" + item.city + "</span><span>" + item.region +"</p><span> UUID: " + item.UUID + "</span></a></div>")
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

}); // $document