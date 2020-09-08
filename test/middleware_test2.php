<?php
require_once __DIR__ . '/../vendor/autoload.php';

$a = function( $params, callable $handle ) {
    echo "a middleware.\n";
    return $handle( $params );
};

$b = function( $params, callable $handle ) {
    $result = $handle( $params );
    echo "b middleware.\n";
    return $result;
};

$c = function( $params, callable $handle ) {
    $result = $handle( $params );
    echo "c middleware.\n";
    return $result;
};

$d = function( $params, callable $handle ) {
    echo "d middleware.\n";
    return $handle( $params );
};

function pack_middleware( callable $handle, array $middlewares ) {
    foreach (array_reverse( $middlewares ) as $key => $middleware) {
        $handle = function ( $params )use( $handle, $middleware ) {
            return $middleware( $params, $handle );
        };
    }

    return $handle;
}

$list = [$a, $b, $c, $d];
$app = function ( $params ) {
    var_dump( $params );
};
$handle = pack_middleware($app, $list);
$handle( "123456789" );