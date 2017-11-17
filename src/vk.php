<?php
/**
 * Created by PhpStorm.
 * User: Karavaev
 * Date: 7/25/2015
 * Time: 4:16 PM
 */
error_reporting(0);
date_default_timezone_set("Europe/Minsk");

require '../vendor/autoload.php';
use app\controllers\VKController as Controller;
use app\errors\BreakException;

header("HTTP/1.1 200 OK");

$input =  json_decode(file_get_contents( 'php://input' ));

try {
    $Controller  = new Controller( $input );
    $action = $Controller->parseMessage();
    if ($Controller->message->type == 'confirmation')
    {
        die($action);
    } elseif ($Controller->message->type !== 'wall_post_new')
    {
        $send = $Controller->bot->sendMessage($Controller->message->user_id, $action->reply);
    }
    echo "ok";
} catch (BreakException $breakException) {
    exit($breakException->returnMessage());
} catch (\Exception $e) {
    $reply = 'Идет апдейт бота, обратитесь чуть позже.'.PHP_EOL.'Дебаг инфо: '.$e->getMessage();
} catch (\Error $error) {
    $reply = 'Произошла ошибка в логике бота.'.PHP_EOL.'Инфо: '.$error->getMessage();
}