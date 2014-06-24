/* 19052014 Dynamic Front End Development By Stan */
$(document).ready(function(){
  $("button[name^='OrderStageHistory_']").removeAttr('disabled');
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
  
  $( "button[name^='OrderStageHistory_']" ).click(function(event) {
    event.stopPropagation();  
    var params = jQuery.parseJSON($(this).val());
    window.open("ReportOrderStageHistory.php?transfk="+params.transid+"&invoiceno="+params.invoiceno+"&orderno="+params.orderno,"newwindow","height=700,width=950,left=150,top=0, \n\
    toolbar=no,menubar=no,scrollbars=yes,resizable=no, location=no,status=no");
    return false;
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