<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
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
        $context = (new ObjectNormalizerContextBuilder())->withGroups(['book_basic'])->toArray();

        return new Response($serializer->serialize($books, $_ENV['FORMAT'], $context), 200);
    }

    #[Route('/{id}', name: 'books_show', methods: ['GET'])]
    public function show(Book $book, SerializerInterface $serializer): Response
    {
        $context = (new ObjectNormalizerContextBuilder())->withGroups(['book_basic', 'book_extend'])->toArray();

        return new Response($serializer->serialize($book, $_ENV['FORMAT'], $context), 200);
    }

    #[Route(name: 'books_store', methods: ['POST'])]
    public function store(SerializerInterface $serializer, Request $request, ValidatorInterface $validator, EntityManagerInterface $entityManager): Response
    {
        $book = $serializer->deserialize($request->getContent(), Book::class, $_ENV['FORMAT'], [AbstractNormalizer::IGNORED_ATTRIBUTES => ['authors', 'publisher']]);

        $errors = $validator->validate($book);
        if (count($errors) > 0) {
            return new Response($serializer->serialize(['error' => ["{$errors->get(0)->getPropertyPath()}" => $errors->get(0)->getMessage()]], $_ENV['FORMAT']), 400);
        }

        if ($book->getAuthorIds()) {
            foreach ($book->getAuthorIds() as $author_id) {
                $author = $entityManager->getRepository(Author::class)->find($author_id);
                if (!$author) {
                    return new Response($serializer->serialize(['error' => "Author with id $author_id doesn't exist"], $_ENV['FORMAT']), 404);
                }
                $book->addAuthor($author);
            }
        }

        if ($publisher_id = $book->getPublisherId()) {
            $publisher = $entityManager->getRepository(Publisher::class)->find($publisher_id);
            if (!$publisher) {
                return new Response($serializer->serialize(['error' => "Publisher with id $publisher_id doesn't exist"], $_ENV['FORMAT']), 404);
            }
            $book->setPublisher($publisher);
        }

        $entityManager->persist($book);
        $entityManager->flush();

        return new Response($serializer->serialize(['book_id' => $book->getId()], $_ENV['FORMAT']), 201);
    }

    #[Route('/{id}', name: 'books_update', methods: ['PUT', 'PATCH'])]
    public function update(Book $book, SerializerInterface $serializer, Request $request, ValidatorInterface $validator, EntityManagerInterface $entityManager): Response
    {
        $book = $serializer->deserialize($request->getContent(), Book::class, $_ENV['FORMAT'], [AbstractNormalizer::OBJECT_TO_POPULATE => $book, AbstractNormalizer::IGNORED_ATTRIBUTES => ['authors', 'publisher']]);

        $errors = $validator->validate($book);
        if (count($errors) > 0) {
            return new Response($serializer->serialize(['error' => ["{$errors->get(0)->getPropertyPath()}" => $errors->get(0)->getMessage()]], $_ENV['FORMAT']), 400);
        }

        $book->getAuthors()->clear();
        if ($book->getAuthorIds()) {
            foreach ($book->getAuthorIds() as $author_id) {
                $author = $entityManager->getRepository(Author::class)->find($author_id);
                if (!$author) {
                    return new Response($serializer->serialize(['error' => "Author with id $author_id doesn't exist"], $_ENV['FORMAT']), 404);
                }
                $book->addAuthor($author);
            }
        }

        $book->setPublisher(null);
        if ($publisher_id = $book->getPublisherId()) {
            $publisher = $entityManager->getRepository(Publisher::class)->find($publisher_id);
            if (!$publisher) {
                return new Response($serializer->serialize(['error' => "Publisher with id $publisher_id doesn't exist"], $_ENV['FORMAT']), 404);
            }
            $book->setPublisher($publisher);
        }

        $entityManager->persist($book);
        $entityManager->flush();

        return new Response($serializer->serialize(['success' => 1], $_ENV['FORMAT']), 200);
    }

    #[Route('/{id}', name: 'books_destroy', methods: ['DELETE'])]
    public function destroy(Book $book, EntityManagerInterface $entityManager, SerializerInterface $serializer): Response
    {
        $entityManager->remove($book);
        $entityManager->flush();

        return new Response($serializer->serialize(['success' => 1], $_ENV['FORMAT']), 200);
    }
}
