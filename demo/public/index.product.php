<?php
/**
http 请求入口
由yaf实现对request的分发
**/
include __DIR__.'/../application/library/Yk/Init.php';
Yk\Init::start('product')->bootstrap()->run();
