<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\Service\HateoasJsonResponse;

class ApiGetOneProductsController extends AbstractController
{
    #[Route('/api/products/show/{id}', name: 'api_get_one_products', methods: ['GET'])]
    public function getOneProduct($id, ProductRepository $productRepository, HateoasJsonResponse $hateoasJsonResponse): JsonResponse
    {
        $product = $productRepository->findOneById($id);
        if ($product === null){
            return $this->json([
                'message' => 'Aucun produit trouvé avec cet ID',
                'status' => '400',
            ], 400);
        }
        return $hateoasJsonResponse->getHateoasJsonResponse($product, 200);
    }
}
