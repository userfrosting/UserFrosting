<?php

//Setup the Twigg template hook for the topbar
$app->schema->registerTwigHook("nav_topbar", "components/dashboard/notification-dropdown.twig");
$app->schema->registerTwigHook("nav_topbar", "components/dashboard/message-dropdown.twig");
$app->schema->registerTwigHook("nav_topbar", "components/dashboard/task-dropdown.twig");

//Setup the Twigg template hook for the topbar
$app->schema->registerTwigHook("nav_sidebar", "components/dashboard/menus/zerg-sidebar.twig");
$app->schema->registerTwigHook("nav_sidebar", "components/dashboard/menus/zerglings-sidebar.twig");

?>