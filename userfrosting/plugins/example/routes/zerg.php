<?php

	$app->get('/zerg/?', function () use ($app) {
        // Access-controlled page
        if (!$app->user->checkAccess('uri_zerg')){
            $app->notFound();
        }

        $app->render('users/zerg.twig');
    });