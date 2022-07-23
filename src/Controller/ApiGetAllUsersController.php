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

use Symfony\Component\Routing\Exception\RouteNotFoundException;

class ApiGetAllUsersController extends AbstractController
{
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
}
