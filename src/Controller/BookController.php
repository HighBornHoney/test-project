<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Book;
use App\Entity\Author;
use App\Entity\Publisher;

#[Route('/books')]
final class BookController extends AbstractController
{
    #[Route(name: 'books_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager, SerializerInterface $serializer): Response
    {
        $books = $entityManager->getRepository(Book::class)->findAll();

        $context = [
            AbstractNormalizer::ATTRIBUTES => [
                'id',
                'title',
                'year',
                'authors' => [
                    'surname',
                ],
                'publisher' => [
                    'title',
                ],
            ],
        ];
        
        return new Response($serializer->serialize($books, $_ENV['FORMAT'], $context), 200);
    }
    
    #[Route(name: 'books_store', methods: ['POST'])]
    public function store(SerializerInterface $serializer, Request $request, ValidatorInterface $validator, EntityManagerInterface $entityManager): Response
    {
        $book = $serializer->deserialize($request->getContent(), Book::class, $_ENV['FORMAT'], [AbstractNormalizer::IGNORED_ATTRIBUTES => ['authors', 'publisher']]);
        
        $errors = $validator->validate($book);
        if (count($errors) > 0) {
            return new Response($serializer->serialize(['error' => ["{$errors->get(0)->getPropertyPath()}" => $errors->get(0)->getMessage()]], $_ENV['FORMAT']), 400);
        }
        
        $options = $serializer->decode($request->getContent(), $_ENV['FORMAT']);
        
        if (isset($options['author_ids']) && is_array($options['author_ids']) && array_is_list($options['author_ids'])) {
            $author_ids = array_filter($options['author_ids'], function ($value) {
                return is_int($value);
            });
            $author_ids = array_unique($author_ids);
            $author_ids = array_values($author_ids);
            
            $authors = $entityManager->getRepository(Author::class)->findByIds($author_ids);
            foreach ($authors as $author) {
                $book->addAuthor($author);
            }
        }
        
        if (isset($options['publisher_id']) && is_int($options['publisher_id'])) {
            $publisher = $entityManager->getRepository(Publisher::class)->find($options['publisher_id']);
            if ($publisher) {
                $book->setPublisher($publisher);
            }
        }

        $entityManager->persist($book);
        $entityManager->flush();
        
        return new Response($serializer->serialize(['book_id' => $book->getId()], $_ENV['FORMAT']), 201);
    }
    
    #[Route('/{id}', name: 'books_destroy', methods: ['DELETE'])]
    public function destroy(Book $book, EntityManagerInterface $entityManager, SerializerInterface $serializer): Response
    {   
        $entityManager->remove($book);
        $entityManager->flush();
        
        return new Response($serializer->serialize(['success' => 1], $_ENV['FORMAT']), 200);
    }
}
