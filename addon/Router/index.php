<?php

$router->get('/', function (App\Core\Http\Request $req, App\Core\Http\Response $res) {
  return $res->renderPage([]);
});
