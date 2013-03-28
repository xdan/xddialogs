<?php
include 'class.db.php';
include '../xddialog.php';
$db = new db('localhost','root','','');
$add = false;
if( !isset($_COOKIE['userid']) or !intval($_COOKIE['userid']) or !$db->exists('user',intval($_COOKIE['userid'])) ){
  $db->insert('user',array('name'=>substr(md5(time()),0,8)));
	setcookie('userid',$db->insertid(),time()+3600*24*365,'/');
	$add = true;
	$_COOKIE['userid'] = $db->insertid();
}
$userid = intval($_COOKIE['userid']);
// мы будем всех юзером подключать к одному диалогу, поэтому 'zPcfYXgp' 
// в реальном проекте нужно isset($_REQUEST['hash'])?$_REQUEST['hash']:''
$xddialog = new xddialog($db,time(),$userid,isset($_REQUEST['hash'])?$_REQUEST['hash']:'zPcfYXgp');
if( $add ){
	// если мы только вошли,то добавляем себя в основной диалог
	$xddialog->add_users_to_dialog( array($userid) );
}
function get_messages( $new=false ){
	global $xddialog;
	$cnt = $xddialog->get_new_messages_cnt();
	$messages = (!$cnt&&$new)?array():$xddialog->get_messages_from_dialog($new);
	$out = '';
	foreach($messages as $msg)
		$out.='
		<div>
			<div class="float_left">
				<span class="nikname"><a href="#" id="user_'.$msg['senderid'].'">'.$msg['sender_name'].'</a></span>
				<span class="message_time">'.date('H:i:s d/m/Y',$msg['public']).'</span>
				<div>'.$msg['message'].'</div>
			</div>
			<div class="clearex"></div>
		</div>';
	return array('html'=>$out,'cnt'=>$cnt,'res'=>'0');
}
if(!isset($_GET['action'])){
// по идее следующие строки должны быть раскоменчены, но так как у нас всего один диалог то они не нужны
// $all_user = $db->getRows('select id from #_user where id<>'.$userid,'id');
// $xddialog->find_suit_dialog($all_user );
// $all_user[] = $userid;
// $xddialog->add_users_to_dialog($all_user);
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Пример - Система диалогов аля вконтакте</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
    <link href="style.css" rel="stylesheet" media="screen">
  </head>
  <body>
<div style="margin-top:5px;" id="message_box">
	<?php
		$msg = get_messages();
		echo $msg['html'];
	?>
</div>
<div id="message_form">
	<div class="alert" style="display:none;" id="alert_box">
	  <strong>Ошибка!</strong><span class="error_message"></span>
	</div>
	<div  class="float_left">
		<div>
			<textarea rows="3" id="message" placeholder="Введите Ваше сообщение..." name="message"></textarea>
			<input type="hidden" id="dialog_hash" value="<?php echo $xddialog->hash;?>"/>
		</div>
		<input onclick="fastChat();" class="btn" type="button" value="Отправить"/>
	</div>
	<div class="clearex"></div>
</div>
<div class="alert alert-block">
<a href="http://xdan.ru/Sistema-dialogov-na-php-kak-v-v-kontakte.html">xddialog - система диалогов на php</a>&nbsp;&nbsp;&nbsp;
<a href="https://github.com/xdan/xddialogs">github</a>
</div>
	<script type="text/javascript" src="js/jquery-1.9.1.min.js"></script>
	<script type="text/javascript" src="js/dialog.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>
  </body>
</html>
<?php
}else{
	switch($_GET['action']){
		case 'send':
			$xddialog->send($_POST['message']);
		break;
	}
	echo json_encode(get_messages(true));
} ?>
