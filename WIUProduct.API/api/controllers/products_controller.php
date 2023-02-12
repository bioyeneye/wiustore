<?php

namespace api\controllers;

require_once(__DIR__ . '/../services/product_service.php');

use ProductService;

class ProductsController
{
    private $db;
    private $requestMethod;
    private $productId;
    private $productService;
    private $page;
    private $size;

    public function __construct($db, $requestMethod, $userId, $page, $size)
    {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->productId = $userId;
        $this->productService = new ProductService($db);
        $this->page = $page;
        $this->size = $size;
    }

    public function processRequest()
    {
        switch ($this->requestMethod) {
            case 'GET':
                if ($this->productId) {
                    $response = $this->getProduct($this->productId);
                } else {
                    $response = $this->getAllProducts($this->page, $this->size);
                };
                break;
            case 'POST':
                $response = $this->createProductFromRequest();
                break;
            case 'PUT':
                $response = $this->updateProductFromRequest($this->productId);
                break;
            case 'DELETE':
                $response = $this->deleteProduct($this->productId);
                break;
            case 'OPTIONS':
                $response['status_code_header'] = 'HTTP/1.1 200 OK';
                $response['body'] = null;
                break;
            default:
                $response = $this->notFoundResponse("Method not found");
                break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    private function getAllProducts($page, $size)
    {
        $result = $this->productService->findAll($page, $size);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] =$this->apiResponse(true, "", $result);
        return $response;
    }

    private function getProduct($id)
    {
        $result = $this->productService->find($id);
        if (!$result) {
            return $this->notFoundResponse("No data available with id $id");
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = $this->apiResponse(true,"", $result);
        return $response;
    }

    private function createProductFromRequest()
    {
        $product = (array) json_decode(file_get_contents('php://input'), true);
        $inputs = array("title", "description", "price", "image");
        $validationError = $this->validateInput($product, $inputs);
        if ($validationError) {
            return $this->unprocessableEntityResponse($validationError);
        }

        $result = $this->productService->insert($product);
        if ($result) {
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = $this->apiResponse(true,  "Product created successfully");
        } else {
            $response['status_code_header'] = 'HTTP/1.1 400 Bad Request';
            $response['body'] = $this->apiResponse(false, "Product creation failed");
        }
        return $response;
    }

    private function updateProductFromRequest($id)
    {
        if(!$id) {
            $response['status_code_header'] = 'HTTP/1.1 400 Bad Request';
            $response['body'] = $this->apiResponse(false, "Id of the product is required");;
            return $response;
        }

        $result = $this->productService->find($id);
        if (! $result) {
            return $this->notFoundResponse("Id $id not found");
        }

        $product = (array) json_decode(file_get_contents('php://input'), true);
        $inputs = array("title", "description", "price", "image");
        $validationError = $this->validateInput($product, $inputs);
        if ($validationError) {
            return $this->unprocessableEntityResponse($validationError);
        }

        $result = $this->productService->update($id, $product);
        if ($result) {
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = $this->apiResponse(true, "Product updated successfully");
        } else {
            $response['status_code_header'] = 'HTTP/1.1 400 Bad Request';
            $response['body'] = $this->apiResponse(false, "Product update failed");
        }
        return $response;
    }

    private function deleteProduct($id) 
    {
        if(!$id) {
            $response['status_code_header'] = 'HTTP/1.1 400 Bad Request';
            $response['body'] = $this->apiResponse(false, "Id of the product is required");;
            return $response;
        }

        $result = $this->productService->find($id);
        if (! $result) {
            return $this->notFoundResponse("Id $id not found");
        }
        $this->productService->delete($id);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = null;
        return $response;
    }

    private function unprocessableEntityResponse($messsage)
    {
        $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
        $response['body'] = $messsage;
        return $response;
    }

    private function notFoundResponse($message)
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = $this->apiResponse(false, $message);
        return $response;
    }

    private function apiResponse(bool $status, string $message, $data = array())
    {
        return json_encode(["hasResult"=> $status, "message"=> $message, "data"=> $data]);
    }

    private function validateInput($data, array $inputs)
    {
        foreach ($inputs as $property) {
            if (!hasProperty($data, $property)) {
                return $this->apiResponse( false, "Failed to create product, $property is needed");
            }
        }

        return null;
    }
}