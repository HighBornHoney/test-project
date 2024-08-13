<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Author;

#[Route('/authors')]
final class AuthorController extends AbstractController
{
    #[Route(name: 'authors_store', methods: ['POST'])]
    public function store(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, EntityManagerInterface $entityManager): Response
    {
        $author = $serializer->deserialize($request->getContent(), Author::class, $_ENV['FORMAT'], [AbstractNormalizer::ATTRIBUTES => ['name', 'surname']]);

        $errors = $validator->validate($author);
        if (count($errors) > 0) {
            return new Response($serializer->serialize(['error' => ["{$errors->get(0)->getPropertyPath()}" => $errors->get(0)->getMessage()]], $_ENV['FORMAT']), 400);
        }

        $entityManager->persist($author);
        $entityManager->flush();

        return new Response($serializer->serialize(['author_id' => $author->getId()], $_ENV['FORMAT']), 201);
    }

    #[Route('/{id}', name: 'authors_destroy', methods: ['DELETE'])]
    public function destroy(Author $author, EntityManagerInterface $entityManager, SerializerInterface $serializer): Response
    {
        $entityManager->remove($author);
        $entityManager->flush();

        return new Response($serializer->serialize(['success' => 1], $_ENV['FORMAT']), 200);
    }
}
