/* 19052014 Dynamic Front End Development By Stan */
$(document).ready(function(){
  $( "#ChooseEmailTemplate" ).change(function() {
  $.post("custom/EmailTemplateDetails.php",
  {
    TemplateId:$("#ChooseEmailTemplate").val()
  },
  function(data,status){
     if(status=='success'){ 
      window.frames[0].document.body.innerHTML=data;
     }
     else{
      alert('Email template retrieved failed!');
     }
     });
  });
});