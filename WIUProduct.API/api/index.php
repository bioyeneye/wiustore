<?php

include_once(__DIR__ . '/configs/database_config.php');
include_once(__DIR__ . '/controllers/products_controller.php');
include_once(__DIR__ . '/controllers/comments_controller.php');

use api\configs\DatabaseConfig;
use api\controllers\ProductsController;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', trim($uri));

// all of our endpoints start with /person
// everything else results in a 404 Not Found
if ($uri[2] !== 'products') {
    header("HTTP/1.1 404 Not Found");
    exit();
}

// the user id is, of course, optional and must be a number:
$productId = null;
if (isset($uri[3])) {
    $productId = (string)$uri[3];
}

$isCommentEndpoint = false;

if (count($uri) > 4 && isset($uri[5]) && $uri[5] !== 'comments') {
    header("HTTP/1.1 404 Not Found");
    exit();
} else {
    if (count($uri) > 4) {
        $isCommentEndpoint = true;
    }
}

$db = new DatabaseConfig();
$conn = $db->connect();
$requestMethod = $_SERVER["REQUEST_METHOD"];
if ($isCommentEndpoint) {
    // comment controller
    // product_id, comment_id, method
    error_log("In comment\n");

} else {
    // product controller
    // product_id, comment_id, method
    error_log("In product\n");

    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $size = isset($_GET['size']) ? intval($_GET['size']) : 5;

    $controller = new ProductsController($conn, $requestMethod, $productId, $page, $size);
    $controller->processRequest();
}

$conn->close();