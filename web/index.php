<?php
require('../vendor/autoload.php');
require_once ('./lib/LineMessageUtil.php');
require_once ('./lib/LineClient.php');
require_once ('./lib/search/TokenModel.php');
require_once ('./lib/search/SearchModel.php');
require_once ('./lib/search/MessageModel.php');
require_once ('./lib/MemcacheUtil.php');
require_once ('./model/LineRequestModel.php');

use Symfony\Component\HttpFoundation\Request;

date_default_timezone_set("Asia/Tokyo");

$app = new Silex\Application();
$app['debug'] = true;

// Register the monolog logging service
$app->register(new Silex\Provider\MonologServiceProvider(), array(
  'monolog.logfile' => 'php://stderr',
));

// Register view rendering
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

// Our web handlers

$app->get('/', function() use($app) {
  $app['monolog']->addDebug('logging output.');
  return $app['twig']->render('index.twig');
});

$app->post('/callback', function (Request $request) use ($app) {

    $lineClient = new LineClient();
    $lineRequestModel = new LineRequestModel($request);

    $memcacheUtil = new MemcacheUtil($lineRequestModel->getRoomKey());

    $tokenModel = new TokenModel($lineRequestModel->getText());
    $searchModel = new SearchModel($tokenModel,$lineRequestModel->getEventType());
    $messageModel = new MessageModel($searchModel);

    error_log(print_r($messageModel->getMessage(),true));
    error_log($memcacheUtil->get("wakeUp"));

    if ($searchModel->getOperation() == "join") { //joinのときは起きてるかどうかにかかわらず返信する。
        $lineClient->send($lineRequestModel->getReplyToken(), $messageModel->getMessage());
        return 'OK';
    }

    if ($searchModel->getReservedMessageKey() == "3") { //wakeUp
        $memcacheUtil->set("wakeUp",true);
    }

    if($memcacheUtil->get("wakeUp")) {
        if ($messageModel->isResponseMessage()) {
            $lineClient->send($lineRequestModel->getReplyToken(), $messageModel->getMessage());
        }
    }

    if ($searchModel->getReservedMessageKey() == "4") { //sleep
        $memcacheUtil->set("wakeUp",false);
    }

    return 'OK';
});

$app->run();
