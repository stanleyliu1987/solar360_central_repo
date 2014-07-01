function defaultControl(c){
c.select();
c.focus();
}
function ReloadForm(fB){
fB.click();
}
function rTN(event){
	if (window.event) k=window.event.keyCode;
	else if (event) k=event.which;
	else return true;
	kC=String.fromCharCode(k);
	if ((k==null) || (k==0) || (k==8) || (k==9) || (k==13) || (k==27)) return true;
	else if ((("0123456789.-").indexOf(kC)>-1)) return true;
	else return false;
}
function assignComboToInput(c,i){
	i.value=c.value;
}
function inArray(v,tA,m){
	for (i=0;i<tA.length;i++) {
		if (v.value==tA[i].value) {
			return true;
		}
	}
	alert(m);
	return false;
}
function isDate(dS,dF){
	var mA=dS.match(/^(\d{1,2})(\/|-|.)(\d{1,2})(\/|-|.)(\d{4})$/);
	if (mA==null){
		alert("Please enter the date in the format "+dF);
		return false;
	}
	if (dF=="d/m/Y"){
		d=mA[1];
		m=mA[3];
	}else{
		d=mA[3];
		m=mA[1];
	}
	y=mA[5];
	if (m<1 || m>12){
		alert("Month must be between 1 and 12");
		return false;
	}
	if (d<1 || d>31){
		alert("Day must be between 1 and 31");
		return false;
	}
	if ((m==4 || m==6 || m==9 || m==11) && d==31){
		alert("Month "+m+" doesn`t have 31 days");
		return false;
	}
	if (m==2){
		var isleap=(y%4==0);
		if (d>29 || (d==29 && !isleap)){
			alert("February "+y+" doesn`t have "+d+" days");
			return false;
		}
	}
	return true;
}
function eitherOr(o,t){
	if (o.value!='') t.value='';
	else if (o.value=='NaN') o.value='';
}
/*Renier & Louis (info@tillcor.com) 25.02.2007
Copyright 2004-2007 Tillcor International
*/
days=new Array('Su','Mo','Tu','We','Th','Fr','Sa');
months=new Array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
dateDivID="calendar";
function Calendar(md,dF){
	iF=document.getElementsByName(md).item(0);
	pB=iF;
	x=pB.offsetLeft;
	y=pB.offsetTop+pB.offsetHeight;
	var p=pB;
	while (p.offsetParent){
		p=p.offsetParent;
		x+=p.offsetLeft;
		y+=p.offsetTop;
	}
	dt=convertDate(iF.value,dF);
	nN=document.createElement("div");
	nN.setAttribute("id",dateDivID);
	nN.setAttribute("style","visibility:hidden;");
	document.body.appendChild(nN);
	cD=document.getElementById(dateDivID);
	cD.style.position="absolute";
	cD.style.left=x+"px";
	cD.style.top=y+"px";
	cD.style.visibility=(cD.style.visibility=="visible" ? "hidden" : "visible");
	cD.style.display=(cD.style.display=="block" ? "none" : "block");
	cD.style.zIndex=10000;
	drawCalendar(md,dt.getFullYear(),dt.getMonth(),dt.getDate(),dF);
}
function drawCalendar(md,y,m,d,dF){
	var tD=new Date();
	if ((m>=0) && (y>0)) tD=new Date(y,m,1);
	else{
		d=tD.getDate();
		tD.setDate(1);
	}
	TR="<tr>";
	xTR="</tr>";
	TD="<td class='dpTD' onMouseOut='this.className=\"dpTD\";' onMouseOver='this.className=\"dpTDHover\";'";
	xTD="</td>";
	html="<table class='dpTbl'>"+TR+"<th colspan=3>"+months[tD.getMonth()]+" "+tD.getFullYear()+"</th>"+"<td colspan=2>"+
	getButtonCode(md,tD,-1,"&lt;",dF)+xTD+"<td colspan=2>"+getButtonCode(md,tD,1,"&gt;",dF)+xTD+xTR+TR;
	for(i=0;i<days.length;i++) html+="<th>"+days[i]+"</th>";
		html+=xTR+TR;
	for (i=0;i<tD.getDay();i++) html+=TD+"&nbsp;"+xTD;
	do{
		dN=tD.getDate();
		TD_onclick=" onclick=\"postDate('"+md+"','"+formatDate(tD,dF)+"');\">";
		if (dN==d) html+="<td"+TD_onclick+"<div class='dpDayHighlight'>"+dN+"</div>"+xTD;
		else html+=TD+TD_onclick+dN+xTD;
		if (tD.getDay()==6) html+=xTR+TR;
		tD.setDate(tD.getDate()+1);
	} while (tD.getDate()>1)
	if (tD.getDay()>0) for (i=6;i>tD.getDay();i--) html+=TD+"&nbsp;"+xTD;
		html+="</table>";
	document.getElementById(dateDivID).innerHTML=html;
}
function getButtonCode(mD,dV,a,lb,dF){
	nM=(dV.getMonth()+a)%12;
	nY=dV.getFullYear()+parseInt((dV.getMonth()+a)/12,10);
if (nM<0){
	nM+=12;
	nY+=-1;
}
return "<button onClick='drawCalendar(\""+mD+"\","+nY+","+nM+","+1+",\""+dF+"\");'>"+lb+"</button>";
}
function formatDate(dV,dF){
	ds=String(dV.getDate());
	ms=String(dV.getMonth()+1);
	d=("0"+dV.getDate()).substring(ds.length-1,ds.length+1);
	m=("0"+(dV.getMonth()+1)).substring(ms.length-1,ms.length+1);
	y=dV.getFullYear();
	switch (dF) {
		case "d/m/Y":
			return d+"/"+m+"/"+y;
		case "d.m.Y":
			return d+"."+m+"."+y;
		case "Y/m/d":
			return y+"/"+m+"/"+d;
                case "Y-m-d":
                        return y+"-"+m+"-"+d;
		default :
			return m+"/"+d+"/"+y;
	}
}
function convertDate(dS,dF){
	var d,m,y;
	if (dF=="d.m.Y")
		dA=dS.split(".");
	else
		dA=dS.split("/");
	switch (dF){
		case "d/m/Y":
			d=parseInt(dA[0],10);
			m=parseInt(dA[1],10)-1;
			y=parseInt(dA[2],10);
			break;
	case "d.m.Y":
		d=parseInt(dA[0],10);
		m=parseInt(dA[1],10)-1;
		y=parseInt(dA[2],10);
		break;
	case "Y/m/d":
		d=parseInt(dA[2],10);
		m=parseInt(dA[1],10)-1;
		y=parseInt(dA[0],10);
		break;
	default :
		d=parseInt(dA[1],10);
		m=parseInt(dA[0],10)-1;
		y=parseInt(dA[2],10);
		break;
}
return new Date(y,m,d);
}
function postDate(mydate,dS){
var iF=document.getElementsByName(mydate).item(0);
iF.value=dS;
var cD=document.getElementById(dateDivID);
cD.style.visibility="hidden";
cD.style.display="none";
iF.focus();
}
function clickDate(){
	Calendar(this.name,this.alt);
}
function changeDate(){
	isDate(this.value,this.alt);
}
function initial(){
	if (document.getElementsByTagName){
		var as=document.getElementsByTagName("a");
		for (i=0;i<as.length;i++){
			var a=as[i];
			if (a.getAttribute("href") &&
				a.getAttribute("rel")=="external")
				a.target="_blank";
		}
	}
	var ds=document.getElementsByTagName("input");
	for (i=0;i<ds.length;i++){
		if (ds[i].className=="date"){
			ds[i].onclick=clickDate;
			ds[i].onchange=changeDate;
		}
		if (ds[i].className=="number") ds[i].onkeypress=rTN;
	}
}

function popupwindow(transno){
window.open("ReportHistoryList.php?transno="+transno,"newwindow","height=700,width=950,left=150,top=0, \n\
toolbar=no,menubar=no,scrollbars=yes,resizable=no, location=no,status=no");
}

function popupevareportwindow(){
window.open("EvaluationReport.php","newwindow","height=700,width=950,left=150,top=0, \n\
toolbar=no,menubar=no,scrollbars=yes,resizable=no, location=no,status=no");
}

function checkCustomerwindow(custname,branchname,billingemail){
window.open("CheckCustomerList.php?custname="+custname+"&branchname="+branchname+"&billingemail="+billingemail,"newwindow","height=700,width=950,left=150,top=0, \n\
toolbar=no,menubar=no,scrollbars=yes,resizable=no, location=no,status=no");
}

function popupFreightwindow(freno,theme,rootpath){ 
freightcostwindow=window.open("eservice/CostETACalculation.php?freightcosttxt="+freno.toString()+"&theme="+theme+"&rootpath="+rootpath,"newwindow","height=700,width=950,left=150,top=0, \n\
toolbar=no,menubar=no,scrollbars=yes,resizable=no, location=no,status=no");
}

function GetFreightFromChild(fcost,ftxb){
    // alert(window.location.href);
    // ftxb = "FreightCost_"+ftxb;
    // var the_cookie = "freightcost=" + fcost ; 
   //  window.location.reload();
 
     window.location.href=window.location.href+"&freightcost="+fcost+"&freightline="+ftxb+"&Recalculate=Recalculate";
     //document.cookie=the_cookie;
    // document.getElementById(ftxb).value = fcost;
}

//function popupConsignmentNotewindow(ref){ 
//
//    if(ref=='con'){
//        consignmentnotewindow=window.open("","form1","height=700,width=950,left=150,top=0, \n\
//toolbar=no,menubar=no,scrollbars=yes,resizable=no, location=no,status=no////");
//        document.getElementById("condetail").submit();
//    }
//    else{
//        consignmentnotewindow=window.open("","form2","height=700,width=950,left=150,top=0, \n\
//toolbar=no,menubar=no,scrollbars=yes,resizable=no, location=no,status=no////");
//        document.getElementById("refdetail").submit(); 
//    }
//}

  

function getDelStatusDate(POlist,Offset,Row,Invid){

 var POListItem= POlist.split(',');
// var invoiceNumber=document.getElementById('OrderNumber').value;
// var customername=document.getElementById('CustName').value;
// var customercode=document.getElementById('CustCode').value;
// var customerbranch=document.getElementById('CustBranch').value; 
 for (var i = 0; i < POListItem.length; i++)
    {
        getStarTrackDelDetails(POListItem[i],Row,Invid);

    }

document.getElementById("msgsuccess_"+Row).innerHTML='Update Successfully!';    
// Thread Safety    
setTimeout(function() { 
document.getElementById("msgsuccess_"+Row).innerHTML='';    
//window.location.href="TrackConsignmentNote.php?Offset="+Offset+"&OrderNumber="+invoiceNumber+"&CustName="+customername+"&CustCode="+customercode+"&CustBranch="+customerbranch;
}, 5000);

}

function popupStatusConsignmentNotewindow(consignmentId, theme , rootpath){ 

        consignmentnotewindow=window.open("eservice/EnquireConsignmentDetails.php?consignmentId="+consignmentId.toString()+"&theme="+theme+"&rootpath="+rootpath,"newwindow","height=700,width=950,left=150,top=0, \n\
toolbar=no,menubar=no,scrollbars=yes,resizable=no, location=no,status=no");
      
    }

function getStarTrackDelDetails(conPos,row,Invid){ 
    
/* 01052015 Continue to update SO delivery status in Tracking Sheet */
var Invdelstatus=document.getElementById('InvDelStatus_'+row.toString()).value;
var conValue= document.getElementById('conID_'+conPos.toString()).value;
var paymentdate=document.getElementById("paymentdate_"+conPos.toString()).value;
var paymentdatesplit= paymentdate.split('/');
var paymentdateformat=paymentdatesplit[2]+'-'+paymentdatesplit[1]+'-'+paymentdatesplit[0];
var porefnumber=document.getElementById("porefnumber_"+conPos.toString()).value;
var delservice=document.getElementById('chooseservice_'+conPos.toString()).value;

var delstatus=document.getElementById('delStatus_'+conPos.toString()).value;
var deldate=document.getElementById('delDate_'+conPos.toString()).value;

var poComment=document.getElementById('poComment_'+conPos.toString()).value;

xmlhttp=createAjaxObject();
xmlhttp.onreadystatechange=function()
  { 
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    { 
       if(conValue==''){  
      document.getElementById("delStatus_"+conPos.toString()).innerHTML='';
      document.getElementById("delDate_"+conPos.toString()).innerHTML='';
      document.getElementById("difDays_"+conPos.toString()).innerHTML='';    
       }
       
       
      else{
    var responseArray=xmlhttp.responseText.split(',');
 
    if(responseArray.length==1){
        alert('Consignmet Note not existed');
    document.getElementById("delStatus_"+conPos.toString()).innerHTML='';
    document.getElementById("delDate_"+conPos.toString()).innerHTML='';
    document.getElementById("difDays_"+conPos.toString()).innerHTML='';
    }

    // format the Delivery Date output
    var deldatesplit= responseArray[0].split('-');
    var deldateformat=responseArray[2]+deldatesplit[2]+'/'+deldatesplit[1]+'/'+deldatesplit[0];
    
    document.getElementById("delStatus_"+conPos.toString()).innerHTML=responseArray[1];
    document.getElementById("delDate_"+conPos.toString()).innerHTML=deldateformat;
    document.getElementById("difDays_"+conPos.toString()).innerHTML='Days: '+responseArray[3];

      }
    }
   
  }
xmlhttp.open("GET","eservice/GetDeliveryStatusDate.php?consignmentId="+conValue+"&paymentdate="+paymentdateformat+"&porefnumber="+porefnumber+"&delservice="+delservice+"&pocomment="+poComment
+"&delstatus="+delstatus+"&deldate="+deldate+"&Invdelstatus="+Invdelstatus+"&InvoiceId="+Invid+"&UserID="+$("#UserID").val(),true);
xmlhttp.send();
}

function getTollDelDetails(conPos){


}

function sortConsignmentReport(Offset,sorttag){

var sortvalue=document.getElementById("sortreport").value;
var ordertype=document.getElementById("OrderType").value;
var filtertype=document.getElementById("FilterType").value;

if(sortvalue == 'InvoiceNo'){
     document.getElementById("sortreport").value=sortvalue+" Selected";
}
window.location.href="TrackConsignmentNote.php?Offset="+Offset+"&sortreport="+sortvalue+"&sorttag="+sorttag+"&ordertype="+ordertype+"&filtertype="+filtertype;

}

//function chooseDelService(Offset, pos){
//var delservice=document.getElementById('chooseservice_'+pos.toString()).value;
//var searchArray=GrabSearchDetails();
//window.location.href="TrackConsignmentNote.php?Offset="+Offset+"&delservice="+delservice+"&position="+pos+"&OrderNumber="+searchArray[0]+"&CustName="+searchArray[1]+"&CustCode="+searchArray[2]+"&CustBranch="+searchArray[3]; 
//}

function createAjaxObject(){
if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
 
  }
else
  {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
  
  return xmlhttp;
}

function UpdateInvDelStatus(pos,id,Offset){
    var delstatus=document.getElementById('InvDelStatus_'+pos.toString()).value;
    var searchArray=GrabSearchDetails();
    window.location.href="TrackConsignmentNote.php?InvDelStatus=yes"+"&Offset="+Offset+"&InvoiceId="+id+"&Invdelstatus="+delstatus+"&OrderNumber="+searchArray[0]+"&CustName="+searchArray[1]+"&CustCode="+searchArray[2]+"&CustBranch="+searchArray[3];;
}

function GrabSearchDetails(){
    var invoiceNumber=document.getElementById('OrderNumber').value;
    var customername=document.getElementById('CustName').value;
    var customercode=document.getElementById('CustCode').value;
    var customerbranch=document.getElementById('CustBranch').value;
    
    var searchArray=new Array(invoiceNumber,customername,customercode,customerbranch);
    
    return searchArray;
}

function ChangeOrderTypeDefault(selectValue){ 

  document.getElementById(selectValue).options[0].selected=true;
}

/* Jquery Customized Code */
//$(document).ready(function(){
//    alert("sdf");
//});
window.onload=initial;