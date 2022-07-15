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
use Hateoas\Representation\PaginatedRepresentation;
use Hateoas\Representation\CollectionRepresentation;

use Symfony\Component\Routing\Exception\RouteNotFoundException;

class ApiController extends AbstractController
{

    #[Route('/api/products/{page}', defaults:['page' => 1], name: 'api_get_products', methods: ['GET'])]
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

    #[Route('/api/{customer}/users/{page}', defaults:['page' => 1], name: 'api_get_users', methods:  ['GET'])]
    public function getAllUsers($customer, $page, UserRepository $userRepository, CustomerRepository $customerRepository): JsonResponse
    {
        $user = $userRepository->findOneByName($customer);
        $customerName = $customer;

        if ($user === null){
            return $this->json([
                'message' => 'Aucun client avec ce nom trouvé',
                'status' => '400',
            ], 400); 
        }

        $page = intval($page);
        $maxPerPage = 5;

        $customers = $customerRepository->findByUser($user->getId());
        $array = $customers;

        $adapter = new ArrayAdapter($array);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta
            ->setMaxPerPage($maxPerPage)
            ->setCurrentPage($page);
        
        $returnCustomers = array();
        foreach($pagerfanta->getCurrentPageResults() as $customer){
            $returnCustomers[] = $customer;
        }

        $data = [
            'total' => $pagerfanta->getNbResults(),
            'count' => count($returnCustomers),
            'customers' => $returnCustomers
        ];

        if ($pagerfanta->hasPreviousPage()){
            $data['previousPage'] = '/api/' . $customerName .'/users/' . $pagerfanta->getPreviousPage();
        }
        if ($pagerfanta->hasNextPage()){
            $data['nextPage'] = '/api/' . $customerName .'/users/' . $pagerfanta->getNextPage();
        }

        $hateoas = HateoasBuilder::create()->build();
        $json = $hateoas->serialize($data, 'json');

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
        if ($jsonRecu === ""){
            return $this->json([
                'message' => 'Le body ne peut pas être vide',
                'status' => '400',
            ], 400);             
        }

        $result = json_decode($jsonRecu, true);
        if ($result === null){
            return $this->json([
                'message' => 'Veuillez spécifier un email, un prénom, un nom de famille pour créer cet utilisateur',
                'status' => '400',
            ], 400);             
        }

        if ((isset($result['email'])) && (gettype($result['email']) != "string")){
            return $this->json([
                'message' => 'Le type de "email" doit être string',
                'status' => '400',
            ], 400);  
        }
        if ((isset($result['firstName'])) && (gettype($result['firstName']) != "string")){
            return $this->json([
                'message' => 'Le type de "firstName" doit être string',
                'status' => '400',
            ], 400);  
        }
        if ((isset($result['lastName'])) && (gettype($result['lastName']) != "string")){
            return $this->json([
                'message' => 'Le type de "lastName" doit être string',
                'status' => '400',
            ], 400);  
        }

        $user = $serializer->deserialize($jsonRecu, Customer::class, 'json');

        if ($user->getEmail() === null){
            return $this->json([
                'message' => 'Veuillez spécifier un email (email) pour créer cet utilisateur',
                'status' => '400',
            ], 400);               
        }

        if ($user->getFirstName() === null){
            return $this->json([
                'message' => 'Veuillez spécifier un prénom (firstName) pour créer cet utilisateur',
                'status' => '400',
            ], 400);               
        }

        if ($user->getLastName() === null){
            return $this->json([
                'message' => 'Veuillez spécifier un nom de famille (lastName) pour créer cet utilisateur',
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

        $hateoas = HateoasBuilder::create()->build();
        $json = $hateoas->serialize($user, 'json');

        $response = new JsonResponse();
        $response->setContent($json);
        $response->setStatusCode(201);
        return $response;
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
        if ($jsonRecu === ""){
            return $this->json([
                'message' => 'Le body ne peut pas être vide',
                'status' => '400',
            ], 400);             
        }

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

        $hateoas = HateoasBuilder::create()->build();
        $json = $hateoas->serialize($user, 'json');

        $response = new JsonResponse();
        $response->setContent($json);
        return $response;

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