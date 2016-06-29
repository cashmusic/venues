$(document).ready(function() {

   var search = function() {
      // If the field isn't empty do a search
      if ($("#keyword").val() != ""){

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
         $.getJSON( "/venues/"+encodeURIComponent(term), function( data ) {

            if (data.results != "") {
               var items = [];
               $(data.results).each(function (index, item) {
                  finalMarkup = "<div class='result' id='" + item.id + "'><a class='card' href='/venue/" + item.UUID + ".html'><h1>" + item.name + "</h1><div class='address'>";
                  if (item.address1) {
                     finalMarkup += "<p>" + item.address1 + "</p>";
                  }
                  if (item.address2) {
                     finalMarkup += "<p>" + item.address2 + "</p>";
                  }
                  if (item.city) {
                     finalMarkup += "<p>" + item.city + "</p>";
                  }
                  if (item.region) {
                     finalMarkup += "<p>" + item.region + "</p>";
                  }
                  if (item.postalcode) {
                     finalMarkup += "<p>" + item.postalcode + "</p>";
                  }
                  if (item.country) {
                     finalMarkup += "<p>" + item.country + "</p>";
                  }
                  finalMarkup += "</div>";
                  if (item.url) {
                     finalMarkup += "<p class='website'><a target='_blank' href='http://" + item.url + "'>" + item.url + "</a></p>";
                  }
                  finalMarkup += "<p class='uuid'><span class='title'>UUID</span><span class='id'>" + item.UUID + "</span></p></a></div>";
                  $('.results .inner').append(finalMarkup);
               });

            } else{
               $('.results .inner').empty();
               $('.results').addClass('dice');
            }

            if (!$('#container').hasClass('display')){
               $('#container').addClass('display');
            }

            setTimeout(function() {
               $('.result').addClass('show');
            }, 200);

         }); //getJSON

      } // If you submitted something

      else{
         $('body').removeClass('display');
      }
   };
   
   search();
   /* Search Submit */
   $("#search_submission").submit(function(e) {
      e.preventDefault();
   });

   /* Inputting Text */
   var keystroketimer = false;
   $('#keyword').on('keyup', function() {

      var url_state = "";
      if ($(this).val().length > 2) {
         var url_state = "/venues/"+encodeURIComponent($(this).val())+".html";
      }

      if ($(this).val().length < 3) {
         var url_state = "";
         window.history.replaceState([], null, "/");
      }

      history.pushState(1, null, url_state);
      if ($(this).val().length > 2) {
         keystroketimer = setTimeout(function() {
            search();
            return false;
         }, 150);
      }
   });



   /* Focus outside empty input field */
   $('#keyword').blur(function() {
      if( $(this).val().length === 0 ) {
         $('body').removeClass('display');
      }
   });

   /* Sub Page Stuff */

   $('.return').click(function(){
      parent.history.back();
      return false;
   });


}); // $document
