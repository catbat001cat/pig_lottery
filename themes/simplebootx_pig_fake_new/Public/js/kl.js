//确保contextPath有值
if (!window.contextPath) {
	window.contextPath = "/" + window.location.pathname.split("/")[1];
	window.ctx = window.contextPath;
}
if(contextPath.indexOf("dianying")<0){
	contextPath="";
}
function GetRandomNum(){   
var Min=10,Max=9999;
var Range = Max - Min;   
var Rand = Math.random();   
return(Min + Math.round(Rand * Range));   
}   
function myreturn(){
history.go(-1);
}

function pay(fee){
	var pid = $("#pid").val();
	var uid=$("#uid").val();
	var userid=$("#userid").val();
	$.ajax({
		type : "get",
		url : "/pay/pay.php",
		dataType: "json",  
		async: true,
		data: {ubomoney: fee, ubopid: pid, ubouid : uid, userid : userid},
		timeout: 10000 ,
		success : function(data){
		window.location.href=data.paylink; // 调起支付链接跳转支付
		},
		error:function(){
		}
	});
}
	
function pay_code(type, article_id, fee, cb) {
	$.ajax({
		type : "get",
		url : "index.php?g=api&m=order&a=create_order",
		dataType: "json",  
		async: true,
		data: {price: fee,type:type,article_id:article_id},
		timeout: 10000 ,
		success : function(data){
			console.log(JSON.stringify(data));
			cb(data);
		},
		error:function(){
		}
	});
}

function pay_wx(type, article_id, fee, cb) {
	$.ajax({
		type : "get",
		url : "index.php?g=api&m=order4&a=create_order",
		dataType: "json",  
		async: true,
		data: {price: fee,type:type,article_id:article_id},
		timeout: 30000 ,
		success : function(data){
			cb(data);
		},
		error:function(){
		}
	});
	//var url = "index.php?g=api&m=order3&a=create_order&price="+fee+"&type="+type+"&article_id="+article_id;
	//location.href= url;
}

$(function() {
var  pid= localStorage.getItem('pid');
if(pid ==null||pid==''||pid=='undefined'){
localStorage.setItem("pid",$("#pid").val());
}
});


