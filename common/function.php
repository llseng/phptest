<?php

function sock_err($str = '')
{
	die($str . socket_strerror(socket_last_error()) . "\n");
}