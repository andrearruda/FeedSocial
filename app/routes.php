<?php
// Routes

$app->get('/', function(){
    return $this->response->withRedirect($this->router->pathFor('socialmedia'));
});

$app->get('/socialmedia', App\Action\SocialMediaAction::class)->setName('socialmedia');