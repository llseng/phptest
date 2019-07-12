<?php

$str = json_encode([$_SERVER,"\r\n\0"]);

var_dump($str);

$str .= "\0";

var_dump(strpos($str . "\0", "\0"));

var_dump(substr($str, 0, strpos($str, "\0")));