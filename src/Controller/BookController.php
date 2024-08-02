<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Book;
use App\Entity\Author;
use App\Entity\Publisher;

#[Route('/books')]
final class BookController extends AbstractController
{
    #[Route('', name: 'books_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): JsonResponse
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

        return $this->json($books, context: $context);

    }
    
    #[Route('', name: 'books_store', methods: ['POST'])]
    public function store(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $input = $this->container->get('serializer')->decode($request->getContent(), 'json');
        } catch(\Exception $e) {
            return $this->json(['error' => ['message' => 'Bad request']], 400);
        }
        
        if (!isset($input['title']) || $input['title'] === "") {
            return $this->json(['error' => ['message' => 'The field title cannot be empty']], 400);
        }

        if (isset($input['year']) && !preg_match('/^[0-9]{4}$/', $input['year'])) {
           return $this->json(['error' => ['message' => 'The field year must consist of 4 digits']], 400);
        }

        $book = new Book();
        $book->setTitle($input['title']);
        $book->setYear($input['year']);
        
        if (isset($input['author_ids'])) {
            
            if (!is_array($input['author_ids'])) {
                return $this->json(['error' => ['message' => 'The field author_ids must be an array of integers']], 400);
            }
            
            $author_ids = array_unique($input['author_ids']);
            $author_ids = array_filter($author_ids, function ($value) {
                return is_int($value);
            });
            
            foreach ($author_ids as $author_id) {
                
                $author = $entityManager->getRepository(Author::class)->find($author_id);
                
                if (!$author) {
                    return $this->json(['error' => ['message' => "There is no author with id $author_id"]], 400);
                }
                
                $book->addAuthor($author);
            }
            
        }
        
        if (isset($input['publisher_id'])) {
            
            $publisher_id = $input['publisher_id'];
            
            if (!is_int($publisher_id)) {
                return $this->json(['error' => ['message' => "The field publisher_id must be an integer"]], 400);
            }
            
            $publisher = $entityManager->getRepository(Publisher::class)->find($publisher_id);
            
            if (!$publisher) {
                return $this->json(['error' => ['message' => "There is no publisher with id $publisher_id"]], 400);
            }
            
            $book->setPublisher($publisher);
            
        }

        $entityManager->persist($book);
        $entityManager->flush();
        
        return $this->json(['response' => $book->getId()], 201);
    }
    
    #[Route('/{id}', name: 'books_destroy', methods: ['DELETE'])]
    public function destroy(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $book = $entityManager->getRepository(Book::class)->find($id);
        
        if (!$book) {
            return $this->json(['error' => ['message' => "There is no book with id $id"]], 404);
        }
        
        $entityManager->remove($book);
        $entityManager->flush();
        
        return $this->json(['response' => 1], 200);
    }
}
