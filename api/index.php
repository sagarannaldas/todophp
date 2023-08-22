<?php

declare(strict_types=1);

require __DIR__ . "/bootstrap.php";

ini_set("display_errors", "On");

$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

$parts = explode("/", $path);

//  print_r($parts);

$resource = $parts[3];

$id = $parts[4] ?? null;

// echo $resource, " , ", $id;

// echo $_SERVER["REQUEST_METHOD"];

if ($resource != "tasks") {
    // header("{$_SERVER['SERVER_PROTOCOL']} 404 Not Found");
    http_response_code(404);
    exit;
}

$database = new Database($_ENV["DB_HOST"], $_ENV["DB_NAME"], $_ENV["DB_USER"], $_ENV["DB_PASSWORD"]);

$user_gateway = new UserGateway($database);

// $headers = apache_request_headers();
// echo $headers["Authorization"];

$jwtCodec = new JWTCodec($_ENV["SECRET_KEY"]);

$auth = new Auth($user_gateway, $jwtCodec);

if (!$auth->authenticationAccessToken()) {
    exit;
}

// if (!$auth->authenticateKey()) {
// exit;
// }

$user_id = $auth->getUserId();

// $database->getConnection();

// require dirname(__DIR__) . "/src/TaskController.php";

$task_gateway = new TaskGateway($database);

$controller = new TaskController($task_gateway, $user_id);

$controller->processRequest($_SERVER['REQUEST_METHOD'], $id);
