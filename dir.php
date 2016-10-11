#!/usr/bin/env php
<?php
$dir=opendir(".");
while (($dirname=readdir($dir))!==false){
    echo $dirname.PHP_EOL;
}