<?php
$path = realpath(dirname(__FILE__));
require_once 'vendor/autoload.php';
copy($path."/../bot.db", $path."/../bot.db.bak");