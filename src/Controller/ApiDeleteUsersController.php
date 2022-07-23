<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Customer;

use App\Repository\UserRepository;
use App\Repository\CustomerRepository;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiDeleteUsersController extends AbstractController
{
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
