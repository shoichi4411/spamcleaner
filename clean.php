#!/usr/local/bin/php -q
<?php

require_once'/home/rone/sc/sc/log.class.php';
require_once '/home/rone/sc/sc/init.php';
require_once '/home/rone/sc/sc/libs/qdmail_receiver.php';

$log = new BxLog(LOG_FILE, LOG_ROTATE_DAYS, LOG_KEEPS);
//$log->info("----- mail start -----");


// メールを取得
$receiver = QdmailReceiver::start('stdin', 'utf-8');

$mail = $receiver->getMail();
$subject = $receiver->header(array('subject', 'name'));
$originMessageId = $receiver->header('message-id');
sleep(10);	// GMAIL側にメール転送される時間を考慮してスリープ入れる

// GMAIL側のメールを取得
$server = array(
	'protocol'	=> 'imap',
	'port'		=> '993',
	'host'		=> GMAIL_IMAP,
	'user'		=> GMAIL_USERID,
	'pass'		=> GMAIL_PASSWORD,
);

$receiver = QdmailReceiver::start( 'imap' , $server, 'utf-8');
$foundFlg = false;
$receiver->selectMailbox('INBOX');
foreach($receiver->getUidAll() as $uid) {
	$receiver->getMail($uid);
	$messageId = $receiver->header('message-id');
	if ($originMessageId == $messageId) {
		$logmsg = "Found mail : ".$messageId;
		$log->debug($logmsg);
		$foundFlg = true;
		$ret = $receiver->deleteUid($uid);
		break;
	}
}
$receiver->expunge();
$receiver->close();
unset($receiver);

$header = "X-SpamCleaner-Version: SpamCleaner 0.0.1 (2013-01-09)\r\n";
if (!$foundFlg) {
	$logmsg = "Spam mail : {$subject} [{$originMessageId}]";
	$log->info($logmsg);
	$header .= "X-SpamCleaner-Status: Bad";
}else{
	$header .= "X-SpamCleaner-Status: Clean";
}
echo preg_replace("/Received/s", "{$header}\r\nReceived", $mail, 1);
?>