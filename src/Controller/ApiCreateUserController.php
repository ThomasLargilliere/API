<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Customer;

use App\Repository\UserRepository;
use App\Repository\CustomerRepository;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\Service\HateoasJsonResponse;

class ApiCreateUserController extends AbstractController
{
    #[Route('/api/{customer}/users', name: 'api_post_user', methods: ['POST'])]
    public function addUser($customer, Request $request, UserRepository $userRepository, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator, HateoasJsonResponse $hateoasJsonResponse): JsonResponse
    {
        $userCustomer = $userRepository->findOneByName($customer);
        if ($userCustomer === null){
            return $this->json([
                'message' => 'Aucun client avec ce nom trouvé',
                'status' => '404',
            ], 404);            
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

        $user = $serializer->deserialize($jsonRecu, Customer::class, 'json');

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return $this->json([
                'message' => $errorsString,
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

        return $hateoasJsonResponse->getHateoasJsonResponse($user, 201);
    }
}
