<?php

$token = "26_DlpNumX1dLK7s1ooqrCKfoPO1J9PmbgqgbTPTW6n8FPEj4R_WuCJxgoK1d4EfmOQKF5BSczkM-hUgmktqNZcDYOIhub0J7ZEqkMrzR4JCb3MZISNPZHtpVXlNkRWHECvIp5A00G0nOE6mBqwYFEgAJATAZ";

$url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=".$token."&next_openid=";
var_dump($url);
$data_file = __DIR__ . "/../../logs/".date("Ymd").".txt";

var_dump(file_put_contents($data_file, file_get_contents($url)), FILE_APPEND);