<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Customer;

use App\Repository\UserRepository;
use App\Repository\CustomerRepository;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\Service\Pagination;
use App\Service\HateoasJsonResponse;

class ApiGetAllUsersController extends AbstractController
{
    #[Route('/api/{customer}/users/{page}', defaults:['page' => 1], name: 'api_get_users', methods:  ['GET'])]
    public function getAllUsers($customer, $page, UserRepository $userRepository, CustomerRepository $customerRepository, Pagination $pagination, HateoasJsonResponse $hateoasJsonResponse): JsonResponse
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
        $items = $pagination->getPagination($page, $maxPerPage, $customers, 'users');
        return $hateoasJsonResponse->getHateoasJsonResponse($items, 200);
    }
}
