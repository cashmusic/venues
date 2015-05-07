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

/* Focus outside empty input field */
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
            $('.results .inner').append("<div class='result' id='" + item.id + "'><a class='card' href='/venues/"+item.UUID+".html'><h1>" + item.name +"</h1><div class='address'><p>"+ item.address1 + "</p><p>" + item.address2 + "</p><p>" + item.city + "</p><p>" + item.region + "</p><p>" + item.postalcode + "</p><p>" + item.country + "</p></div><p class='website'><a target='_blank' href='" + item.url + "'>"+ item.url +"</a></p></p><p class='uuid'><span class='title'>UUID</span><span class='id'>" + item.UUID + "</span></p></a></div>")
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

/* Sub Page Stuff */

  $('.return').click(function(){
        parent.history.back();
        return false;
      });

  $('.edit').click(function(){
        $('#card').toggleClass('flipped');
      });

    /* Edit Form Submit */
    $(".edit-form").submit(function(e) {
        
      var formData = {
            'name' : $('input[name=venuename]').val(),
            'address1' : $('input[name=address1]').val(),
            'address2' : $('input[name=address2]').val()
          };

          console.log(formData);

      // AJAX Code To Submit Form.
      $.ajax({
      type: "POST",
      url: "http://localhost:8888/venues/edit/{{UUID}}",
      data: formData,
      dataType: 'json',
            encode: true
      })
  
      .done(function(data) {
                console.log(data); 
                $('#card').toggleClass('flipped');
            })

            .fail(function(data) {
              alert('fucked up');
          console.log( data );
        });

      e.preventDefault();
    });

    // preselect country & type if they exist
      $("#country").val("{{country}}"); 
      $("#type").val("{{type}}");  

       $.getJSON( "/venues/{{UUID}}", function( data ) {
         var items = [];
         $(data).each(function(index, item) {
              $('.code').append(JSON.stringify(item));
          });
       });

}); // $document