<?php

//Hook on the setupServices to add "breadcrumbs" to $app
$app->hook('setupServices', function () use ($app){
    $app->breadcrumbs = new \UserFrosting\Breadcrumbs($app);
}, 1);

//Setup the Twigg template hook
$app->schema->registerTwigHook("content_before", "components/common/breadcrumbs.twig");

//Add a new Twig function to get the Breadcrumbs items
$function_breadcrumbs = new \Twig_SimpleFunction('breadcrumbs', function () use ($app) {
    return $app->breadcrumbs->getItems();
});
$twig = $app->view()->getEnvironment();
$twig->addFunction($function_breadcrumbs);

