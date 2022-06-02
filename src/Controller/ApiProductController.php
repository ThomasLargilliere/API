<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use Doctrine\Persistence\ManagerRegistry;

use Symfony\Component\Serializer\SerializerInterface;

use App\Entity\Product;
use App\Repository\ProductRepository;

class ApiProductController extends AbstractController
{
    #[Route('/api/product', name: 'api_product_index', methods: 'GET')]
    public function getAllProducts(ProductRepository $productRepository, SerializerInterface $serializer): JsonResponse
    {
        return $this->json($productRepository->findAll(), 200, [], ['groups' => 'product:read']);
    }
    #[Route('/api/product/{id}', name: 'api_product_id', methods: 'GET')]
    public function getOneProduct($id, ProductRepository $productRepository, SerializerInterface $serializer): JsonResponse
    {
        $product = $productRepository->find($id);
        if ($product == null){
            return $this->json([
                'status' => '404',
                'message' => 'Le produit demandé n\'existe pas'
            ], 404);
        }
        return $this->json($product, 200, [], ['groups' => 'product:read']);
    }
    #[Route('/api/product', name: 'api_product_post', methods: 'POST')]
    public function post(Request $request, SerializerInterface $serializer, ManagerRegistry $doctrine, ValidatorInterface $validator)
    {
        $jsonRecu = $request->getContent();

        try {
            $product = $serializer->deserialize($jsonRecu, Product::class, 'json');

            $entityManager = $doctrine->getManager();

            $errors = $validator->validate($product);

            if(count($errors) > 0){
                return $this->json($errors, 400);
            }

            $entityManager->persist($product);
            $entityManager->flush();

            return $this->json($product, 201, [], ['groups' => 'product:read']);
        } catch(NotEncodableValueException $e){
            return $this->json([
                'status' => '400',
                'message' => $e->getMessage()
            ], 400);
        }
    }
    #[Route('/api/product/{id}', name: 'api_product_id', methods: 'PATCH')]
    public function updateOneProduct($id, Request $request, ProductRepository $productRepository, SerializerInterface $serializer, ManagerRegistry $doctrine, ValidatorInterface $validator): JsonResponse
    {
        $product = $productRepository->find($id);
        if ($product == null){
            return $this->json([
                'status' => '404',
                'message' => 'Le produit demandé n\'existe pas'
            ], 404);
        }

        $jsonRecu = $request->getContent();

        try {

            $newProduct = $serializer->deserialize($jsonRecu, Product::class, 'json');

            if ($newProduct->getName()){
                $product->setName($newProduct->getName());
            }

            if ($newProduct->getDescription()){
                $product->setDescription($newProduct->getDescription());
            }

            if ($newProduct->getPrice()){
                $product->setPrice($newProduct->getPrice());
            }

            $entityManager = $doctrine->getManager();

            $errors = $validator->validate($product);

            if(count($errors) > 0){
                return $this->json($errors, 400);
            }

            $entityManager->persist($product);
            $entityManager->flush();

            return $this->json($product, 202, [], ['groups' => 'product:read']);

        } catch(NotEncodableValueException $e){
            return $this->json([
                'status' => '400',
                'message' => $e->getMessage()
            ], 400);
        }

    }
    #[Route('/api/product/{id}', name: 'api_product_id', methods: 'DELETE')]
    public function deleteOneProduct($id, Request $request, ProductRepository $productRepository, SerializerInterface $serializer, ManagerRegistry $doctrine, ValidatorInterface $validator): JsonResponse
    {
        $product = $productRepository->find($id);
        if ($product == null){
            return $this->json([
                'status' => '404',
                'message' => 'Le produit demandé n\'existe pas'
            ], 404);
        }

        $entityManager = $doctrine->getManager();

        $entityManager->remove($product);
        $entityManager->flush();

        return $this->json([
            'status' => '200',
            'message' => 'Le produit portant l\'id ' . $id . ' a bien été supprimé'
        ], 200);

    }
}
