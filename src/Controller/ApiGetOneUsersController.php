<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Customer;

use App\Repository\UserRepository;
use App\Repository\CustomerRepository;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\ArrayAdapter;

use Hateoas\HateoasBuilder;

class ApiGetOneUsersController extends AbstractController
{
    #[Route('/api/{customer}/users/show/{id}', name: 'api_get_user', methods: ['GET'])]
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
}
