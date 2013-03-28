var xhr = 0,litsentimer;
function listenMe(hash){
  clearTimeout(litsentimer);
	litsentimer = setTimeout(function(){
		xhr = $.post('index.php?action=get_new_messages'+'&hash='+hash,function(data,status){
			if( status=='success' )
				if( data.res==0 ){
					if( data.html!=''){
						$('#message_box').append(data.html);
						_scrollBottom();
					}
				}
			
			listenMe(hash);
		},'json');
	},3000);
}
function _scrollBottom(){
if( $('#message_box').length )
	$('#message_box').get(0).scrollTop = $('#message_box').get(0).scrollHeight;
}
function fastChat(){
	var mess = $('#message_form').find('#message').val();
	if(!$.trim(mess).length){
		$('#alert_box').stop().css({opacity:1.0}).find('.error_message').html('Сообщение слишком короткое!').parent().show();
		setTimeout(function(){
			$('#alert_box').fadeOut(1000);
		},2000);
	}else{
		clearTimeout(litsentimer);
		if(xhr)xhr.abort();
		$('#message_form').find('#message').val('');
		$.post('index.php?action=send',{message:mess,hash:$('input#dialog_hash').length?$('input#dialog_hash').val():''},function(data,status){
			listenMe($('input#dialog_hash').val());
			if( status=='success' ){
				if( data.res==0 ){
					$('#message_box').append(data.html);
					_scrollBottom()
				}
			}
		},'json');
	}
	return false;
}
$(function(){
	_scrollBottom();
	listenMe($('input#dialog_hash').val());
	$('#message').keydown(function(e){
		if( e.which==13 ){
			e.preventDefault();
			fastChat();
			return false;
		};
	});
});
