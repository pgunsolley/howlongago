<?php
// Callback handlers for the fake loading screen.

function onCompleteHandler() {
    echo PHP_EOL;
}

function onRenderHandler($item) {
    $stdout = fopen('php://output', 'w');
    fwrite($stdout, $item, strlen($item));
    fclose($stdout);
}