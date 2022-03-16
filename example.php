<?php

require __DIR__.'/vendor/autoload.php';

use Jasonchen\Util\Util;
use Jasonchen\Accunix\AccunixLineApi;

//write log
$util = new Util();
$util->logs('Repo', 'register message strings', 'api', __DIR__);
##

//accunix api
$accunix = new AccunixLineApi('xxxxx');
$access_token = 'xxxxxxxxxxxxxxxxxx';
$accunix->setAccessToken($access_token);
$user_token = 'xxxxxxxxxxxx';

// 寄送訊息
$body = [[
    "type"  => "text",
    "text"  => "Hello, world",
]];

$response = $accunix->sendMessages($user_token, $body);
##

