<?php

require_once('db_instance.php');
require_once('headers.php');

$request_method = $_SERVER["REQUEST_METHOD"];
$product_id = $_GET['product_id'];
$comment_id = $_GET['id'];
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$size = isset($_GET['size']) ? intval($_GET['size']) : 10;

processRequest($request_method, $product_id, $comment_id,$page, $size);

function processRequest($requestMethod, $product_id, $comment_id, $page, $size)
{
    switch ($requestMethod) {
        case 'GET':
            if ($product_id) {
                $response = getCommentByProductId($product_id);
            } else if ($comment_id) {
                $response = getComment($comment_id);
            } else {
                $response = getAllComments($page, $size);
            };
            break;
        case 'POST':
            $response = createCommentFromRequest();
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

function getAllComments($page, $size)
{
    global $db_conn;
    try {
        $offset = ($page - 1) * $size;
        $total_pages_sql = "SELECT COUNT(*) FROM comments";
        $result = mysql_query($total_pages_sql, $db_conn);
        $total_rows = mysql_fetch_array($result)[0];
        $total_pages = ceil($total_rows / $size);

        $query = "SELECT * FROM comments LIMIT $offset, $size";
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

function getComment($id)
{
    $result = find($id);
    if (!$result) {
        return notFoundResponse("No data available with id $id");
    }
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = apiResponse(true, "", $result);
    return $response;
}

function getCommentByProductId($id)
{
    global $db_conn;

    try {
        $query = "SELECT * FROM comments WHERE product_id = '$id'";
        $result = mysql_query($query, $db_conn);

        $comments = array();
        while ($row = mysql_fetch_assoc($result)) {
            $comments[] = $row;
        }

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = apiResponse(true, "", $comments);
        return $response;
    } catch (Exception $e) {
        exit($e->getMessage());
    }
}

function createCommentFromRequest()
{
    global $db_conn;
    try {
        $product = (array)json_decode(file_get_contents('php://input'), true);
        $inputs = array("name", "comment", "product_id");
        $validationError = validateInput($product, $inputs);
        if ($validationError) {
            return unprocessableEntityResponse($validationError);
        }

        $name = mysql_real_escape_string($product['name']);
        $comment = mysql_real_escape_string($product['comment']);
        $id = mysql_real_escape_string($product['product_id']);

        $query =  "INSERT INTO comments (name, comment, created_at, updated_at, product_id) VALUES ('" . $name . "', '" . $comment . "', '" . date('Y-m-d H:i:s') . "', '" . date('Y-m-d H:i:s') . "', '" . $id . "')";
        $result = mysql_query($query, $db_conn);

        if ($result) {

            $id_c = mysql_insert_id();
            $query = "SELECT * FROM comments WHERE id = $id_c";
            $result = mysql_query($query, $db_conn);
            $comment_result = mysql_fetch_assoc($result);
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = apiResponse(true, "Comment created successfully", $comment_result);
        } else {
            $response['status_code_header'] = 'HTTP/1.1 400 Bad Request';
            $response['body'] = apiResponse(false, "Product creation failed", array());
        }
        return $response;

    } catch (Exception $e) {
        exit($e->getTraceAsString());
    }
}

function updateCommentFromRequest($id)
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

function deleteComment($id)
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
            return apiResponse(false, "Failed to create comment, $property is needed", array());
        }
    }
    return null;
}

function find($id)
{
    global $db_conn;

    try {
        $query = "SELECT * FROM comments WHERE id = $id";
        $result = mysql_query($query, $db_conn);

        $comment = null;
        if (mysql_num_rows($result)) {
            $comment = mysql_fetch_assoc($result);
        }

        return $comment;
    } catch (Exception $e) {
        exit($e->getMessage());
    }
}