<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Product;
use App\Repository\ProductRepository;

use App\Entity\User;
use App\Repository\UserRepository;

use App\Entity\Customer;
use App\Repository\CustomerRepository;

class ApiController extends AbstractController
{
    #[Route('/api/products', name: 'api_get_products', methods: ['GET'])]
    public function getAllProducts(ProductRepository $productRepository): JsonResponse
    {
        return $this->json($productRepository->findAll(), 200, [], ['groups' => 'products:read']);
    }

    #[Route('/api/product/{id}', name: 'api_get_product', methods: ['GET'])]
    public function getOneProducts($id, ProductRepository $productRepository): JsonResponse
    {
        $product = $productRepository->findOneById($id);
        if ($product === null){
            return $this->json([
                'message' => 'Aucun produit trouvé avec cet ID',
                'status' => '400',
            ], 400);
        }
        return $this->json($product, 200, [], ['groups' => 'products:read']);
    }

    #[Route('/api/{customer}/users', name: 'api_get_users', methods:  ['GET'])]
    public function getAllUsers($customer, UserRepository $userRepository, CustomerRepository $customerRepository): JsonResponse
    {

        $user = $userRepository->findOneByName($customer);

        if ($user === null){
            return $this->json([
                'message' => 'Aucun client avec ce nom trouvé',
                'status' => '400',
            ], 400);            
        }

        return $this->json($customerRepository->findById($user->getId()), 200, [], ['groups' => 'customers:read']);
    }

    #[Route('/api/{customer}/user/{id}', name: 'api_get_user', methods: 'GET')]
    public function getOneUser($customer, $id, UserRepository $userRepository, CustomerRepository $customerRepository): JsonResponse
    {
        $user = $userRepository->findOneByName($customer);

        if ($user === null){
            return $this->json([
                'message' => 'Aucun client avec ce nom trouvé',
                'status' => '400',
            ], 400);            
        }

        return $this->json($customerRepository->findOneById($user->getId()), 200, [], ['groups' => 'customers:read']);
    }

    #[Route('/api/{customer}/user', name: 'api_post_user', methods: 'POST')]
    public function addUser($customer): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/ApiLoginController.php',
        ]);
    }

    #[Route('/api/{customer}/user', name: 'api_delete_user', methods: 'DELETE')]
    public function delUser($customer): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/ApiLoginController.php',
        ]);
    }
}
