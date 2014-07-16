/* 19052014 Dynamic Front End Development By Stan */
$(document).ready(function(){
  $("button[name^='OrderStageHistory_']").removeAttr('disabled');
  $("textarea[name^='OrderComment_']").removeAttr('disabled');
  $("input[name^='PORemark_']").removeAttr('disabled');
  $("select[name^='OrderStagesList_']").removeAttr('disabled');

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
  
  $("textarea[name^='OrderComment_']").blur(function(event){
 event.stopPropagation();  
 $.post("custom/ajax/OrderCommentsUpdate.php",
  {
    TransID:this.id.substring(13),
    OrderComments:$(this).val() 
  },
  function(data,status){
     if(status!='success'){ 
       alert('Order Comments Updated Failed!');
     }
    });
  })
  
  $("#StockLocation").change(function() {
    $.post("custom/ajax/POStockLocationUpdate.php",
  {
    PO_OrderNo:$("#PO_OrderNo").val(),
    StockLocation:$(this).val()
  },
  function(data,status){
     if(status!='success'){ 
       alert('PO Stock Location Updated Failed!');
     }
    });    
});

  $("input[name^='PORemark_']").blur(function(event){
 event.stopPropagation();  
 $.post("custom/ajax/PORemarksUpdate.php",
  {
    PO_OrderNo:this.id.substring(9),
    PORemark:$(this).val() 
  },
  function(data,status){
     if(status!='success'){ 
       alert('PO Remark Updated Failed!');
     }
    });
  })
  $("#POCheckbox").change(function() {
    if(this.checked) { 
         $("#POEmailSubject").val($("#POEmailSubject").val()+ " PO");
    }
    else{
        $("#POEmailSubject").val($("#POEmailSubject").val().replace(" PO", ""));
    }
});
  $("#DDCheckbox").change(function() {
    if(this.checked) { 
         $("#POEmailSubject").val($("#POEmailSubject").val()+ " DD");
    }
    else{
        $("#POEmailSubject").val($("#POEmailSubject").val().replace(" DD", ""));
    }
});
  $("#RCTICheckbox").change(function() {
    if(this.checked) { 
         $("#POEmailSubject").val($("#POEmailSubject").val()+ " RCTI");
    }
    else{
        $("#POEmailSubject").val($("#POEmailSubject").val().replace(" RCTI", ""));
    }
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