<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Entity\Author;
use App\Entity\Publisher;
use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(
    name: 'db:seed',
    description: 'Seeds the database with testing data',
)]
class DbSeedCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp('This command allows you to insert a little bit of data into your database');
    }
    
    private function getBooks(): array
    {
        $authors = [
            ['name' => 'Джордж', 'surname' => 'Оруэлл'],
            ['name' => 'Рэй', 'surname' => 'Брэдбери'],
            ['name' => 'Игорь', 'surname' => 'Симдянов'],
            ['name' => 'Дмитрий', 'surname' => 'Котеров'],
            ['name' => 'Мадлен', 'surname' => 'Л’Энгль'],
            ['name' => 'Льюис', 'surname' => 'Кэрролл'],
        ];

        $publishers = [
            ['title' => 'Эксмо', 'address' => 'просп. Обуховской Обороны, 84Е, Санкт-Петербург, 192029'],
            ['title' => 'Like Book', 'address' => 'ул. Зорге, д. 1, стр. 1, Москва, 123308'],
            ['title' => 'БХВ', 'address' => 'ул. Гончарная, дом 20, пом. 7Н 191036, Санкт-Петербург'],
            ['title' => 'Scholastic', 'address' => 'New York, New York, United States'],
        ];
        
        $books = [
            ['title' => '1984', 'year' => 2024, 'authors' => [$authors[0]], 'publisher' => $publishers[0]],
            ['title' => "451' по Фаренгейту", 'year' => 2024, 'authors' => [$authors[1]], 'publisher' => $publishers[1]],
            ['title' => 'PHP 8', 'year' => 2023, 'authors' => [$authors[2], $authors[3]], 'publisher' => $publishers[3]],
            ['title' => 'Складка времени', 'year' => 2020, 'authors' => [$authors[4]], 'publisher' => $publishers[1]],
            ['title' => 'Алиса в Стране чудес и в Зазеркалье', 'year' => 1871, 'authors' => [$authors[5]], 'publisher' => $publishers[2]],
            ['title' => 'Some new awesome book', 'year' => 2024, 'authors' => [$authors[0], $authors[1], $authors[2], $authors[3], $authors[4]], 'publisher' => $publishers[3]],
        ];
        
        return $books;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {        
        $books = $this->getBooks();

        for ($i = 0; $i < count($books); $i++) {
            
            $book = new Book();
            $book->setTitle($books[$i]['title']);
            $book->setYear($books[$i]['year']);
            
            if (!empty($books[$i]['authors'])) {
                                
                for ($j = 0; $j < count($books[$i]['authors']); $j++) {
                    
                    $author = $this->entityManager->getRepository(Author::class)->findOneBy([
                        'name' => $books[$i]['authors'][$j]['name'],
                        'surname' => $books[$i]['authors'][$j]['surname']
                    ]);
                    
                    if (!$author) {
                        $author = new Author;
                        $author->setName($books[$i]['authors'][$j]['name']);
                        $author->setSurname($books[$i]['authors'][$j]['surname']);
                        $this->entityManager->persist($author);
                        $this->entityManager->flush();
                    }
                    
                    $book->addAuthor($author);
                }
            }
            
            if (!empty($books[$i]['publisher'])) {
                
                $publisher = $this->entityManager->getRepository(Publisher::class)->findOneBy([
                    'title' => $books[$i]['publisher']['title'],
                    'address' => $books[$i]['publisher']['address'],
                ]);
                
                if (!$publisher) {
                    $publisher = new Publisher();
                    $publisher->setTitle($books[$i]['publisher']['title']);
                    $publisher->setAddress($books[$i]['publisher']['address']);
                    $this->entityManager->persist($publisher);
                    $this->entityManager->flush();
                }
                
                $book->setPublisher($publisher);
            }
            
            $this->entityManager->persist($book);
        }
        $this->entityManager->flush();
        
        
        $output->writeln('<info>The data was inserted successfully</info>');
        
        return Command::SUCCESS;
    }
}
