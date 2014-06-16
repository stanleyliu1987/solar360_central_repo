/* 19052014 Dynamic Front End Development By Stan */
$(document).ready(function(){
  /* Css Set up 16062014 */
  $( "input[name^='OrderStageHistory']" ).css('width', '80px');
  $( "select[name^='OrderStagesList']" ).css('width', '160px');
  
  $( "#ChooseEmailTemplate" ).change(function() { 
  $.post("custom/ajax/EmailTemplateDetails.php",
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

  function ChangeOrderStages(transID){
  $.post("custom/ajax/OrderStagesUpdate.php",
  {
    OrderStages:$("#OrderStagesList_"+transID).val(),
    TransID:transID,
    UserID:$("#UserID").val()
  },
  function(data,status){
     if(status!='success'){ 
       alert('Order Stages Updated Failed!');
     }
    });
  }