<?php

namespace api\controllers;

class CommentsController
{
    private $db;
    private $requestMethod;
    private $userId;
    private $productService;

    public function __construct($db, $requestMethod, $userId)
    {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->userId = $userId;
        $this->productService = new ProductService($db);
    }
}