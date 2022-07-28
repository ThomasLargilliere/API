<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Customer;

use App\Repository\UserRepository;
use App\Repository\CustomerRepository;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\Service\HateoasJsonResponse;

class ApiGetOneUsersController extends AbstractController
{
    #[Route('/api/{customer}/users/show/{id}', name: 'api_get_user', methods: ['GET'])]
    public function getOneUser($customer, $id, UserRepository $userRepository, CustomerRepository $customerRepository, HateoasJsonResponse $hateoasJsonResponse): JsonResponse
    {

        $id = intval($id);
        if ($id == 0){
            return $this->json([
                'message' => 'Veuillez entrer un nombre en ID.',
                'status' => '400',
            ], 400);
        }

        $user = $userRepository->findOneByName($customer);
        if ($user === null){
            return $this->json([
                'message' => 'Aucun client avec ce nom trouvé',
                'status' => '404',
            ], 404);            
        }

        $customer = $customerRepository->findOneById($id);
        if ($customer === null){
            return $this->json([
                'message' => 'Aucun utilisateur trouvé avec cet ID',
                'status' => '404',
            ], 404);             
        }

        return $hateoasJsonResponse->getHateoasJsonResponse($customer, 200);
    }
}
