<?php

namespace App\Http\Controllers;

use App\Services\DocBookAuthorService;
use Illuminate\Http\Request;

class GatewayController extends Controller
{
    protected $productService;
    protected $orderService;
    protected $userService;

    public function __construct(
        DocBookAuthorService $bookService
        // OrderService $orderService,
        // UserService $userService
    ) {
        $this->productService = $bookService;
        // $this->orderService = $orderService;
        // $this->userService = $userService;
    }

    public function index(Request $request)
    {
        return $this->productService->getBook($request->all());
    }

    public function findByID($id)
    {
        return $this->productService->getBookById($id);
    }

    public function storeBook(Request $request)
    {
        return $this->productService->createBookDocService($request);
    }

    // public function getOrder($id)
    // {
    //     return $this->orderService->getOrder($id);
    // }

    // public function getUser($id)
    // {
    //     return $this->userService->getUser($id);
    // }
}
