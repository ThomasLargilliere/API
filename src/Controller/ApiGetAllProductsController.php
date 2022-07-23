<?php
namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\Service\Pagination;
use App\Service\HateoasJsonResponse;

class ApiGetAllProductsController extends AbstractController
{
    #[Route('/api/products/{page}', defaults:['page' => 1], name: 'api_get_all_products', methods: ['GET'])]
    public function getAllProducts($page, ProductRepository $productRepository, Pagination $pagination, HateoasJsonResponse $hateoasJsonResponse): JsonResponse
    {

        $page = intval($page);
        $maxPerPage = 5;
        $products = $productRepository->findAll();
    
        $items = $pagination->getPagination($page, $maxPerPage, $products, 'products');

        return $hateoasJsonResponse->getHateoasJsonResponse($items, 200);
    }
}
