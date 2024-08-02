<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Publisher;

#[Route('/publishers')]
final class PublisherController extends AbstractController
{
    #[Route('/{id}', name: 'publishers_update', methods: ['PUT', 'PATCH'])]
    public function update(EntityManagerInterface $entityManager, int $id, Request $request): JsonResponse
    {
        $publisher = $entityManager->getRepository(Publisher::class)->find($id);

        if (!$publisher) {
            return $this->json(['error' => ['message' => "There is no publisher with id $id"]], 404);
        }

        try {
            $input = $this->container->get('serializer')->decode($request->getContent(), 'json');
        } catch(\Exception $e) {
            return $this->json(['error' => ['message' => 'Bad request']], 400);
        }

        if(!isset($input['title']) || $input['title'] === "") {
            return $this->json(['error' => ['message' => 'The field title cannot be empty']], 400);
        }

        if(!isset($input['address']) || $input['address'] === "") {
            return $this->json(['error' => ['message' => 'The field address cannot be empty']], 400);
        }

        $publisher->setTitle($input['title']);
        $publisher->setAddress($input['address']);

        $entityManager->persist($publisher);
        $entityManager->flush();

        return $this->json(['response' => 1], 200);
    }
    
    #[Route("/{id}", name: 'publishers_destroy', methods: ['DELETE'])]
    public function destroy(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $publisher = $entityManager->getRepository(Publisher::class)->find($id);

        if (!$publisher) {
            return $this->json(['error' => ['message' => "There is no publisher with id $id"]], 404);
        }

        $entityManager->remove($publisher);
        $entityManager->flush();

        return $this->json(['response' => 1], 200);
    }
}
