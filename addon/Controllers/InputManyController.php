<?php

namespace Addon\Controllers;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\View\View;

class InputManyController
{
  public function index(Request $request, Response $response): View
  {
    return $response->renderPage(
      [],
      ['meta' => ['title' => 'Input Banyak | ' . env('APP_NAME')]]
    );
  }
}
