<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Author;

#[Route('/authors')]
final class AuthorController extends AbstractController
{
    #[Route('', name: 'authors_store', methods: ['POST'])]
    public function store(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $input = $this->container->get('serializer')->decode($request->getContent(), 'json');
        } catch(\Exception $e) {
            return $this->json(['error' => ['message' => 'Bad request']], 400);
        }
        
        if (!isset($input['name']) || $input['name'] === "") {
            return $this->json(['error' => ['message' => 'The field name cannot be empty']], 400);
        }
        if (!isset($input['surname']) || $input['surname'] === "") {
            return $this->json(['error' => ['message' => 'The field surname cannot be empty']], 400);
        }
        
        $author = new Author();
        $author->setName($input['name']);
        $author->setSurname($input['surname']);
        
        $entityManager->persist($author);
        $entityManager->flush();
        
        return $this->json(['response' => $author->getId()], 201);
    }
    
    #[Route('/{id}', name: 'authors_destroy', methods: ['DELETE'])]
    public function destroy(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $author = $entityManager->getRepository(Author::class)->find($id);
        
        if (!$author) {
            return $this->json(['error' => ['message' => "There is no author with id $id"]], 404);
        }
        
        $entityManager->remove($author);
        $entityManager->flush();
        
        return $this->json(['response' => 1], 200);
    }
}
