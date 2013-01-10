<?php
mb_language('ja');
mb_internal_encoding('UTF-8');

define('LOG_FILE'			,  dirname(__FILE__).'/../log/log.cgi');
define('LOG_ROTATE_DAYS'	, 1);
define('LOG_KEEPS'			, 7);
define('ATTACHMENTS_DIR', dirname(__FILE__).'/../log');


/* ここから設定  */
define('GMAIL_IMAP'		, 'ssl://imap.gmail.com');
define('GMAIL_USERID'	, '');
define('GMAIL_PASSWORD'	, '');

define('USER_POP3'		, '');
define('USER_USERID'	, '');
define('USER_PASSWORD'	, '');
