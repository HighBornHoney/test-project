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
use App\Entity\Publisher;

#[Route('/publishers')]
final class PublisherController extends AbstractController
{
    #[Route('/{id}', name: 'publishers_update', methods: ['PUT', 'PATCH'])]
    public function update(Publisher $publisher, SerializerInterface $serializer, Request $request, ValidatorInterface $validator, EntityManagerInterface $entityManager): Response
    {
        $serializer->deserialize($request->getContent(), Publisher::class, $_ENV['FORMAT'], [AbstractNormalizer::OBJECT_TO_POPULATE => $publisher, AbstractNormalizer::ATTRIBUTES => ['title', 'address']]);

        $errors = $validator->validate($publisher);
        if (count($errors) > 0) {
            return new Response($serializer->serialize(['error' => ["{$errors->get(0)->getPropertyPath()}" => $errors->get(0)->getMessage()]], $_ENV['FORMAT']), 400);
        }
        
        $entityManager->flush();
        
        return new Response($serializer->serialize(['success' => 1], $_ENV['FORMAT']), 200);
    }
    
    #[Route("/{id}", name: 'publishers_destroy', methods: ['DELETE'])]
    public function destroy(Publisher $publisher, EntityManagerInterface $entityManager, SerializerInterface $serializer): Response
    {
        $entityManager->remove($publisher);
        $entityManager->flush();

        return new Response($serializer->serialize(['success' => 1], $_ENV['FORMAT']), 200);
    }
}
