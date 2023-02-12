<?php

ini_set("log_errors", 1); // Enable error logging
ini_set("error_log", __DIR__."/tmp/php-error.log"); // set error path

require_once('db_instance.php');
require_once('headers.php');

$request_method = $_SERVER["REQUEST_METHOD"];
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$size = isset($_GET['size']) ? intval($_GET['size']) : 10;
$product_id = $_GET['id'];

processRequest($request_method, $product_id, $page, $size);

function processRequest($requestMethod, $product_id, $page, $size)
{
    switch ($requestMethod) {
        case 'GET':
            if ($product_id) {
                $response = getCommentByProductId($product_id);
            } else {
                $response = getAllComments($page, $size);
            };
            break;
        case 'POST':
            $response = createProductFromRequest();
            break;
        case 'PUT':
            $response = updateCommentFromRequest($product_id);
            break;
        case 'DELETE':
            $response = deleteComment($product_id);
            break;
        case 'OPTIONS':
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = null;
            break;
        default:
            $response = notFoundResponse("Method not found");
            break;
    }

    header($response['status_code_header']);
    if ($response['body']) {
        echo $response['body'];
    }
}

function getAllProducts($page, $size)
{
    global $db_conn;
    try {
        $offset = ($page - 1) * $size;
        $total_pages_sql = "SELECT COUNT(*) FROM products";
        $result = mysql_query($total_pages_sql, $db_conn);
        $total_rows = mysql_fetch_array($result)[0];
        $total_pages = ceil($total_rows / $size);

        $query = "SELECT products.*, (SELECT COUNT(*) FROM comments WHERE comments.product_id = products.id) AS comments FROM products LIMIT $offset, $size";
        $result = mysql_query($query, $db_conn);

        $products = array();
        while ($row = mysql_fetch_assoc($result)) {
            $products[] = $row;
        }

        $data = array();
        $data["total"] = $total_rows;
        $data["pages"] = $total_pages;
        $data["items"] = $products;

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = apiResponse(true, "", $data);
        return $response;
    } catch (Exception $e) {
        exit($e->getMessage());
    }
}

function getProduct($id)
{
    $result = find($id);
    if (!$result) {
        return notFoundResponse("No data available with id $id");
    }
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = apiResponse(true, "", $result);
    return $response;
}

function createProductFromRequest()
{
    global $db_conn;
    try {
        $product = (array)json_decode(file_get_contents('php://input'), true);
        $inputs = array("title", "description", "price", "image");
        $validationError = validateInput($product, $inputs);
        if ($validationError) {
            return unprocessableEntityResponse($validationError);
        }

        $title = $product['title'];
        $description = $product['description'];
        $price = $product['price'];
        $image = $product['image'];

        $query = "INSERT INTO products (title, description, price, image) VALUES ('$title', '$description', '$price', '$image')";
        $result = mysql_query($query, $db_conn);
        if ($result) {
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = apiResponse(true, "Product created successfully", array());
        } else {
            $response['status_code_header'] = 'HTTP/1.1 400 Bad Request';
            $response['body'] = apiResponse(false, "Product creation failed", array());
        }
        return $response;

    } catch (Exception $e) {
        exit($e->getTraceAsString());
    }
}

function updateProductFromRequest($id)
{
    global $db_conn;

    try {

        if (!$id) {
            $response['status_code_header'] = 'HTTP/1.1 400 Bad Request';
            $response['body'] = apiResponse(false, "Id of the product is required", array());;
            return $response;
        }

        $result = find($id);
        if (!$result) {
            return notFoundResponse("Id $id not found");
        }

        $product = (array)json_decode(file_get_contents('php://input'), true);
        $inputs = array("title", "description", "price", "image");
        $validationError = validateInput($product, $inputs);
        if ($validationError) {
            return unprocessableEntityResponse($validationError);
        }

        $title = $product['title'];
        $description = $product['description'];
        $price = $product['price'];
        $image = $product['image'];

        $query = "UPDATE products SET title = '$title', description = '$description', price = '$price', product_image = '$image' WHERE id = '$id'";
        $result = mysql_query($query, $db_conn);

        if ($result) {
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = apiResponse(true, "Product updated successfully", array());
        } else {
            $response['status_code_header'] = 'HTTP/1.1 400 Bad Request';
            $response['body'] = apiResponse(false, "Product update failed", array());
        }
        return $response;
    } catch (Exception $e) {
        exit($e->getMessage());
    }
}

function deleteProduct($id)
{
    global $db_conn;
    try {

        if (!$id) {
            $response['status_code_header'] = 'HTTP/1.1 400 Bad Request';
            $response['body'] = apiResponse(false, "Id of the product is required",  array());;
            return $response;
        }

        $result = find($id);
        if (!$result) {
            return notFoundResponse("Id $id not found");
        }

        $query = "Delete from products WHERE id = '$id'";
        mysql_query($db_conn, $query);

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = null;
        return $response;
    } catch (Exception $e) {
        exit($e->getMessage());
    }
}

function unprocessableEntityResponse($message)
{
    $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
    $response['body'] = $message;
    return $response;
}

function notFoundResponse($message)
{
    $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
    $response['body'] = apiResponse(false, $message);
    return $response;
}

function apiResponse($status, $message, $data)
{
    return json_encode(["hasResult" => $status, "message" => $message, "data" => $data]);
}

function hasProperty($mixed, $property)
{
    return isset($mixed[$property]) && !empty($mixed[$property]);
}

function validateInput($data, array $inputs)
{
    foreach ($inputs as $property) {
        if (!hasProperty($data, $property)) {
            return apiResponse(false, "Failed to create product, $property is needed", array());
        }
    }
    return null;
}

function find($id)
{
    global $db_conn;

    try {
        $query = "SELECT * FROM products WHERE id = '$id'";
        $result = mysql_query($query, $db_conn);

        $product = null;
        if (mysql_num_rows($result)) {
            $product = mysql_fetch_assoc($result);
        }

        return $product;
    } catch (Exception $e) {
        exit($e->getMessage());
    }
}