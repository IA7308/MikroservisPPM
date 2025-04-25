<?php

namespace App\Http\Controllers;

use App\Services\DocBookAuthorService;
use App\Services\DocGarudaAuthorService;
use Illuminate\Http\Request;

class GatewayController extends Controller
{
    protected $productService;
    protected $garudaService;
    protected $userService;

    public function __construct(
        DocBookAuthorService $bookService,
        DocGarudaAuthorService $garudaService
        // OrderService $orderService,
        // UserService $userService
    ) {
        $this->productService = $bookService;
        $this->garudaService = $garudaService;
        // $this->orderService = $orderService;
        // $this->userService = $userService;
    }

    public function index(Request $request)
    {
        return $this->productService->getBook($request->all());
    }

    public function indexAC(Request $request)
    {
        return $this->productService->getBookAC($request->all());
    }
    public function findByID($id)
    {
        return $this->productService->getBookById($id);
    }

    public function storeBook(Request $request)
    {
        return $this->productService->createBookDocService($request);
    }
    public function updateBook(Request $request, $id)
    {
        return $this->productService->updateBookDocService($request, $id);
    }
    public function deleteBook($id)
    {
        return $this->productService->deleteBookDocService($id);
    }

// GARUDA

    public function indexGaruda(Request $request)
    {
        return $this->garudaService->getGaruda($request->all());
    }

    public function indexGarudaAC(Request $request)
    {
        return $this->garudaService->getGarudaAC($request->all());
    }
    public function GarudafindByID($id)
    {
        return $this->garudaService->getgarudaById($id);
    }

    public function storeGaruda(Request $request)
    {
        return $this->garudaService->creategarudaDocService($request);
    }
    public function updateGaruda(Request $request, $id)
    {
        return $this->garudaService->updategarudaDocService($request, $id);
    }
    public function deleteGaruda($id)
    {
        return $this->garudaService->deletegarudaDocService($id);
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
