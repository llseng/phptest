<?php

$conf = [
    'wxb6fd5bf20633136e'=>'c20190eab1137dea8527b5eae193cd95', //超级越野
];

$appid = "wxb6fd5bf20633136e";

$secret = $conf[$appid];

$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$secret;

var_dump(file_get_contents($url));