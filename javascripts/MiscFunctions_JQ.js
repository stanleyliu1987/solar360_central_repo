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

  function ChangeOrderStages(transno){ 
  $.post("custom/OrderStagesUpdate.php",
  {
    OrderStages:$("#OrderStagesList_"+transno).val(),
    TransnoID:transno
  },
  function(data,status){
     if(status!='success'){ 
       alert('Order Stages Updated Failed!');
     }
    });
  }