<?php
require __DIR__ . '/vendor/autoload.php';

$app = new \Slim\App();

// Fetch DI Container
$container = $app->getContainer();

// Register Twig View service
$container->register(new \Slim\Views\Twig(__DIR__ . '/views', [
  'cache' => __DIR__ . '/cache',
  'debug' => true,
  'autoescape' => false,
  'template-extension' => 'twig.html',
  ])
);

$app->add(new \Slim\Middleware\HttpBasicAuthentication([
  "path" => "/admin",
  "realm" => "Protected",
  "secure" => true,
  "users" => [
  "username" => "password"
  ],
  "callback" => function ($arguments) use ($app) {
    $app->get('/admin', function ($request, $response, $args) {
      return $this->view->render($response, 'dashboard.twig.html', [
        'title' => 'Logged In'
        ]);
    })->setName('dashboard');
    $app->get('/admin/welcome', function ($request, $response, $args) {
      $authd = $request->hasHeader('PHP_AUTH_USER') && $request->hasHeader('PHP_AUTH_PW');
      return $this->view->render($response, 'welcome.twig.html', [
        'title' => 'Another Page',
        'logged_in' => $authd
        ]);
    })->setName('welcome');
  }
  ]));

// Define named route
$app->get('/', function ($request, $response, $args) {
  $authd = $request->hasHeader('PHP_AUTH_USER') && $request->hasHeader('PHP_AUTH_PW');
  return $this->view->render($response, 'welcome.twig.html', [
    'title' => 'My Website',
    'logged_in' => $authd
    ]);
})->setName('homepage');

// json test route
// $.post('hello', { name: 'Name'}).done(function(res){ console.log(res) });
$app->post('/hello', function ($request, $response) {
  if (!$request->isAjax()) {
    return $response->withStatus(405)->write("Method Not Allowed");
  } else {
    $data = $request->getParsedBody();
    return $response
    ->withHeader('Content-type','application/json')
    ->write(json_encode(array(
      'message' => "Hello, " . $data['name']
      )
    ));
  }
})->setName('hello');

$app->run();