<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Product;
use App\Entity\Customer;

use App\Repository\UserRepository;
use App\Repository\ProductRepository;
use App\Repository\CustomerRepository;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;

use Hateoas\HateoasBuilder;
use Hateoas\Representation\Factory\PagerfantaFactory;

use Symfony\Component\Routing\Exception\RouteNotFoundException;

class ApiController extends AbstractController
{

    #[Route('/api/products', name: 'api_get_products', methods: ['GET'])]
    public function getAllProducts(ProductRepository $productRepository): JsonResponse
    {
        $products = $productRepository->findAll();

        $hateoas = HateoasBuilder::create()->build();
        $json = $hateoas->serialize($products, 'json');

        $response = new JsonResponse();
        $response->setContent($json);
        return $response;
    }

    #[Route('/api/product/{id}', name: 'api_get_product', methods: ['GET'])]
    public function getOneProduct($id, ProductRepository $productRepository): JsonResponse
    {
        $product = $productRepository->findOneById($id);
        if ($product === null){
            return $this->json([
                'message' => 'Aucun produit trouvé avec cet ID',
                'status' => '400',
            ], 400);
        }
        $hateoas = HateoasBuilder::create()->build();
        $json = $hateoas->serialize($product, 'json');

        $response = new JsonResponse();
        $response->setContent($json);
        return $response;
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

        $customers = $customerRepository->findByUser($user->getId());

        $hateoas = HateoasBuilder::create()->build();
        $json = $hateoas->serialize($customers, 'json');

        $response = new JsonResponse();
        $response->setContent($json);
        return $response;
    }

    #[Route('/api/{customer}/user/{id}', name: 'api_get_user', methods: ['GET'])]
    public function getOneUser($customer, $id, UserRepository $userRepository, CustomerRepository $customerRepository): JsonResponse
    {
        $user = $userRepository->findOneByName($customer);

        if ($user === null){
            return $this->json([
                'message' => 'Aucun client avec ce nom trouvé',
                'status' => '400',
            ], 400);            
        }

        $customer = $customerRepository->findOneById($id);
        if ($customer === null){
            return $this->json([
                'message' => 'Aucun utilisateur trouvé avec cet ID',
                'status' => '400',
            ], 400);             
        }

        $hateoas = HateoasBuilder::create()->build();
        $json = $hateoas->serialize($customer, 'json');

        $response = new JsonResponse();
        $response->setContent($json);
        return $response;
    }

    #[Route('/api/{customer}/user', name: 'api_post_user', methods: ['POST'])]
    public function addUser($customer, Request $request, UserRepository $userRepository, SerializerInterface $serializer, EntityManagerInterface $em): JsonResponse
    {

        $userCustomer = $userRepository->findOneByName($customer);

        if ($userCustomer === null){
            return $this->json([
                'message' => 'Aucun client avec ce nom trouvé',
                'status' => '400',
            ], 400);            
        }

        $jsonRecu = $request->getContent();
        $user = $serializer->deserialize($jsonRecu, Customer::class, 'json');

        if ($user->getEmail() === null){
            return $this->json([
                'message' => 'Veuillez spécifier un email pour créer cet utilisateur',
                'status' => '400',
            ], 400);               
        }

        if ($user->getFirstName() === null){
            return $this->json([
                'message' => 'Veuillez spécifier un prénom pour créer cet utilisateur',
                'status' => '400',
            ], 400);               
        }

        if ($user->getLastName() === null){
            return $this->json([
                'message' => 'Veuillez spécifier un nom de famille pour créer cet utilisateur',
                'status' => '400',
            ], 400);               
        }

        $user->setUser($userCustomer);

        $em->persist($user);
        $em->flush();

        if ($user->getId() === null){
            return $this->json([
                'message' => 'Erreur lors de la création de l\'utilisateur',
                'status' => '500',
            ], 500);
        }

        return $this->json([
            'message' => 'Création de l\'utilisateur ' . $user->getFirstName() . ' effectué avec succès',
            'status' => '201',
        ], 201);
    }

    #[Route('/api/{customer}/user/{id}', name: 'api_put_user', methods: ['PUT'])]
    public function putOneUser($customer, $id, UserRepository $userRepository, CustomerRepository $customerRepository, Request $request, SerializerInterface $serializer, EntityManagerInterface $em): JsonResponse
    {
        $userCustomer = $userRepository->findOneByName($customer);

        if ($userCustomer === null){
            return $this->json([
                'message' => 'Aucun client avec ce nom trouvé',
                'status' => '400',
            ], 400);            
        }

        $user = $customerRepository->findOneById($id);
        if ($userCustomer === null){
            return $this->json([
                'message' => 'Aucun utilisateur avec cet ID trouvé',
                'status' => '400',
            ], 400);            
        }
        
        $jsonRecu = $request->getContent();
        $modUser = $serializer->deserialize($jsonRecu, Customer::class, 'json');

        if ($modUser->getEmail() === null && $modUser->getFirstName() === null && $modUser->getLastName() === null){
            return $this->json([
                'message' => 'Vous devez modifier au moins une valeur parmis celle-ci : email, prenom, nom',
                'status' => '400',
            ], 400);               
        }

        if ($modUser->getEmail()){
            $user->setEmail($modUser->getEmail());
        }

        if ($modUser->getFirstName()){
            $user->setFirstName($modUser->getFirstName());
        }

        if ($modUser->getLastName()){
            $user->setLastName($modUser->getLastName());
        }

        $em->persist($user);
        $em->flush($user);

        return $this->json($user, 200, [], ['groups' => 'customers:read']);
    }

    #[Route('/api/users/{id}', name: 'api_delete_user', methods: ['DELETE'])]
    public function delUser($id, CustomerRepository $customerRepository, EntityManagerInterface $em): JsonResponse
    {
        $user = $customerRepository->findOneById($id);
        if ($user === null){
            return $this->json([
                'message' => 'Aucun utilisateur avec cet ID trouvé',
                'status' => '400',
            ], 400);
        }

        $em->remove($user);
        $em->flush();
        
        return $this->json([], 204);
    }
}

// Pagination
// Mise en cache