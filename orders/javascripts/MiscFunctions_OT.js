$(document).ready(function(){


       $('a.checkstatus').click(function(){
             if($('#number').val() === ''){
                        $('#result').html("Invoice Number cannot be empty");
                        return false;
                       }
                       
                      if($('#email').val() === ''){
                        $('#result').html("email cannot be empty");
                        return false;
                       }
                       else{ 
                       //Email regex
                        if(!isValidEmailAddress($('#email').val())){
                             $('#result').html("email address is invalid");
                            return false;
                        }   
                       }
              $.ajax({
                          type: 'POST',
                          url: 'TrackOrderResult.php',
                          data: $("#trackform").serialize(),
                          success: function (msg) {
                          $("#result").html(msg);
                         },
                          error: function() {
                                 alert("Sorry, Validate method is not found");
                                }
                     }); 
       })  

           
});

function isValidEmailAddress(emailAddress) {
    var pattern = new RegExp(/^[+a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/i);
    return pattern.test($.trim(emailAddress));
};


