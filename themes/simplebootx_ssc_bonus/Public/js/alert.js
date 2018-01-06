document.write('<div id="modal-container" class="modal fade"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h4 class="modal-title">小猪</h4></div><div class="modal-body"><p class="msg" style="font-size:14px;"></p></div><div class="modal-footer"><button type="button"  style="padding:8px 26px;border-color:rgb(0,122,255);background-color:rgb(0,122,255);"  class="alert-jump btn btn-primary" data-dismiss="modal" data-url="">确定</button></div></div></div></div>')
function alertMsg(msg,url){
	if(url != ''){
        	$('.alert-jump').attr('data-url',url);
        }else{
        	$('.alert-jump').attr('data-url','');
        }

        $('#modal-container .msg').text(msg);
        $('#modal-container').modal({backdrop: 'static', keyboard: false});

}

$('.alert-jump').on('click',function(){
	if($(this).attr('data-url') != ''){
		window.location.href= $(this).attr('data-url');
	}
})