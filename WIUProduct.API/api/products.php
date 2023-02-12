<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

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

$db = new DatabaseConfig();
$conn = $db->connect();
$requestMethod = $_SERVER["REQUEST_METHOD"];

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$size = isset($_GET['size']) ? intval($_GET['size']) : 5;
$productId = $_GET['id'];

$controller = new ProductsController($conn, $requestMethod, $productId, $page, $size);
$controller->processRequest();

$conn->close();