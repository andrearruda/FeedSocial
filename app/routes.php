<?php
// Routes

$app->get('/', function(){
    return $this->response->withRedirect($this->router->pathFor('social-media'));
});

$app->get('/social-media', App\Action\SocialMediaAction::class)->setName('social-media');
$app->get('/medal-board', App\Action\MedalBoardAction::class)->setName('medal-board');
$app->group('/olympic-schedule', function () {
    $this->get('/day', App\Action\OlympicScheduleDayAction::class)->setName('olympic-schedule.day');
    $this->get('/hour', App\Action\OlympicScheduleHourAction::class)->setName('olympic-schedule.hour');
    $this->get('/hour-all', App\Action\OlympicScheduleHourAllAction::class)->setName('olympic-schedule.hour-all');
});