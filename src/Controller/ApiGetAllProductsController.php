<?php
namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\ArrayAdapter;

use Hateoas\HateoasBuilder;

class ApiGetAllProductsController extends AbstractController
{
    #[Route('/api/products/{page}', defaults:['page' => 1], name: 'api_get_all_products', methods: ['GET'])]
    public function getAllProducts($page, Request $request, EntityManagerInterface $em, ProductRepository $productRepository): JsonResponse
    {
        $page = intval($page);
        $maxPerPage = 5;

        $products = $productRepository->findAll();
        $array = $products;

        $adapter = new ArrayAdapter($array);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta
            ->setMaxPerPage($maxPerPage)
            ->setCurrentPage($page);
        
        $returnProducts = array();

        foreach($pagerfanta->getCurrentPageResults() as $product){
            $returnProducts[] = $product;
        }

        $data = [
            'total' => $pagerfanta->getNbResults(),
            'count' => count($returnProducts),
            'products' => $returnProducts
        ];

        if ($pagerfanta->hasPreviousPage()){
            $data['previousPage'] = '/api/products/' . $pagerfanta->getPreviousPage();
        }
        if ($pagerfanta->hasNextPage()){
            $data['nextPage'] = '/api/products/' . $pagerfanta->getNextPage();
        }
        
        $hateoas = HateoasBuilder::create()->build();
        $json = $hateoas->serialize($data, 'json');
        

        $response = new JsonResponse();
        $response->setContent($json);

        return $response;
    }
}
