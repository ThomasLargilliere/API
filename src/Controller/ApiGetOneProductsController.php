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

        $id = intval($id);

        if ($id == 0){
            return $this->json([
                'message' => 'Veuillez entrer un nombre en ID.',
                'status' => '400',
            ], 400);
        }

        $product = $productRepository->findOneById($id);
        if ($product === null){
            return $this->json([
                'message' => 'Aucun produit trouvé avec cet ID',
                'status' => '404',
            ], 404);
        }
        return $hateoasJsonResponse->getHateoasJsonResponse($product, 200);
    }
}
