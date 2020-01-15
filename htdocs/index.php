<?php
/**
 * Bootstrap the framework and handle the request and send the response.
 */

// Were are all the files?
define('ANAX_INSTALL_PATH', realpath(__DIR__ . '/..'));

// Set development/production environment and error reporting
require ANAX_INSTALL_PATH . '/vendor/anax/commons/config/commons.php';

// Get the autoloader by using composers version.
require ANAX_INSTALL_PATH . '/vendor/autoload.php';

// Add all framework services to $di
$di = new \Faxity\DI\DISorcery(ANAX_INSTALL_PATH);
$di->initialize('config/sorcery.php');

// Send the response that the router returns from the route handler
$method = $di->request->getMethod();
$route = $di->request->getRoute();
$router = $di->router->handle($route, $method);

$di->response->send($router);
