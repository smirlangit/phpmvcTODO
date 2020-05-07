<?php

define('ROOT', __DIR__);
//базовая автозагрузка классов
 spl_autoload_register(function ($class) {
     
    $file = str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';
    $file = ROOT.DIRECTORY_SEPARATOR.$file;
    if (file_exists($file)) {
        require $file;
        return true;
    }
    return false;
 });
