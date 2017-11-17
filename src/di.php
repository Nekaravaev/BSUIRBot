<?php
require_once '../vendor/autoload.php';
use BSUIRBot\Model\DIContainer;
use BSUIRBot\Model\Util\CommandParseHelper;


$container = new DIContainer;

/* init config class. Lookout what to use Dev or Prod */
$container->register('config', function(DIContainer $container) {
    return new BSUIRBot\Config\DevConfig();
});

$container->register(\BSUIRBot\Model\Util\Phrase::class, function (DIContainer $container) {
    return new \BSUIRBot\Model\Util\Phrase();
});

$container->register(\BSUIRBot\Model\Request::class, function (DIContainer $container) {
    return new \BSUIRBot\Model\Request();
});

$container->register(\BSUIRBot\Model\Database\Redis::class, function (DIContainer $container) {
    return new BSUIRBot\Model\Database\Redis();
});

$container->register(\BSUIRBot\Model\Bot\Telegram::class, function(DIContainer $container) {
   $token = $container->get('config')->getTGtoken();
   $requestClass = $container->get(\BSUIRBot\Model\Request::class);
   return new \BSUIRBot\Model\Bot\Telegram($token, $requestClass);
});

$container->register(\BSUIRBot\Model\BSUIR::class, function (DIContainer $container) {
    return new \BSUIRBot\Model\BSUIR($container->get(\BSUIRBot\Model\Request::class));
});

$container->register(Bugsnag\Client::class, function (DIContainer $container) {
   $key = $container->get('config')->getBugSnagAPI();
   return Bugsnag\Client::make($key);
});

$container->register(\BSUIRBot\Model\Util\Phrase::class, function (DIContainer $container) {
   return new \BSUIRBot\Model\Util\Phrase();
});

$container->register(\BSUIRBot\Model\Util\CommandParseHelper::class, function (DIContainer $container) {
   return new CommandParseHelper();
});