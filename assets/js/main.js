$(document).ready(function() {

var dirs = window.location.pathname.substring(1);

if (dirs == "" ){
$('body').addClass('loading');

setTimeout(function() {
  $('body').removeClass('loading');
  $('body').addClass('loaded');
  //run search
  searchit();     
}, 2500);
} else {
   $('body').addClass('loaded');
  searchit();  
}

/* Search Submit */
$("#search_submission").submit(function(e) {
    e.preventDefault();
});

/* Inputting Text */
$('#keyword').on('input', function() {
    searchit();
		history.pushState(1, null, $(this).val());
   	return false;
});

$('#keyword').blur(function()
{
    if( $(this).val().length === 0 ) {
        $('body').removeClass('display');
    }
});

function searchit() {
// If the field isn't empty do a search
  if ($("#keyword").val() != "" || dirs != "" ){

     $('.results').removeClass('dice'); 
     $('.result').removeClass('show');

     //clear out old results
     $('.results .inner').empty();


     if ($("#keyword").val() != ""){ 
      var term = $('#keyword').val(); 
     } 
     else if (dirs != "") { 
      var term = dirs; 
     }

      //getJSON
      $.getJSON( "/venues?q="+term, function( data ) {
     
      if (data.results != ""){
        var items = [];
          $(data.results).each(function(index, item) {
            $('.results .inner').append("<div class='result' id='" + item.id + "'><a class='card' target='_blank' href='/venues/"+item.UUID+".html'><h1>" + item.name +"</h1><p><span>"+ item.address1 + "</span><span>" + item.address2 + "</span><span>" + item.city + "</span><span>" + item.region + "</span><span>" + item.postalcode + "</span><span>" + item.country + "</span><span>" + item.url + "</span></p><p class='uuid'><span class='title'>UUID</span><span class='id'>" + item.UUID + "</span></p></a></div>")
          });

        }else{
          $('.results .inner').empty();
         $('.results').addClass('dice');
        }

      if (!$('body').hasClass('display')){
        $('body').addClass('display');
      }

      setTimeout(function() {
          $('.result').addClass('show');
      }, 100);

    }); //getJSON

  } // If you submitted something

  else{
    $('body').removeClass('display');
  }
} //searchit();

}); // $document