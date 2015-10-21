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
   var keystroketimer = false;
   $('#keyword').on('input', function() {
      history.pushState(1, null, $(this).val());
      if ($(this).val().length >= 3) {
         keystroketimer = setTimeout(function() {
            searchit();
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
                  finalMarkup = "<div class='result' id='" + item.id + "'><a class='card' href='/venues/"+item.UUID+".html'><h1>" + item.name +"</h1><div class='address'>";
                  if (item.address1) {
                     finalMarkup += "<p>"+ item.address1 + "</p>";
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
                     finalMarkup += "<p class='website'><a target='_blank' href='http://" + item.url + "'>"+ item.url +"</a></p>";
                  }
                  finalMarkup += "<p class='uuid'><span class='title'>UUID</span><span class='id'>" + item.UUID + "</span></p></a></div>";
                  $('.results .inner').append(finalMarkup);
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
            }, 200);

         }); //getJSON

      } // If you submitted something

      else{
         $('body').removeClass('display');
      }
   }; //searchit();

   /* Sub Page Stuff */

   $('.return').click(function(){
      parent.history.back();
      return false;
   });

   $('.edit').click(function(){
      $('#card').toggleClass('flipped');
   });


   // process the form
   $('.edit-form').submit(function(event) {
      event.preventDefault();

      // get the form data
      // there are many ways to get this data using jQuery (you can use the class or id also)
      var formData = {
         'venuename'    : $('input[name=venuename]').val(),
         'address1'     : $('input[name=address1]').val(),
         'address2'     : $('input[name=address2]').val(),
         'city'         : $('input[name=city]').val(),
         'region'       : $('input[name=region]').val(),
         'country'      : $('select[name=country] option:selected').val(),
         'postalcode'   : $('input[name=postalcode]').val(),
         'latitude'     : $('input[name=latitude]').val(),
         'longitude'    : $('input[name=longitude]').val(),
         'url'          : $('input[name=url]').val(),
         'phone'        : $('input[name=phone]').val(),
         'type'         : $('select[name=type] option:selected').val(),
         'UUID'         : $('input[name=UUID]').val()
      };

      // process the form
      $.ajax({
         type        : 'POST', // define the type of HTTP verb we want to use (POST for our form)
         url         : '/process.php', // the url where we want to POST
         data        : formData, // our data object
         dataType    : 'json', // what type of data do we expect back from the server
         encode      : true
      })
      // using the done promise callback
      .done(function(data) {
         // log data to the console so we can see
         //console.log(data);
         $('#card').toggleClass('flipped');
         location.reload();
         // here we will handle errors and validation messages
      })
      // using the fail promise callback
      .fail(function(data) {
         // show any errors
         //console.log(data);
      });
   });

}); // $document
