<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

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
    
    private function getSql(): string
    {
        $sql = "
            INSERT INTO
                author
            VALUES
                (1, 'Джордж', 'Оруэлл'),
                (2, 'Рэй', 'Брэдбери'),
                (3, 'Стивен', 'Кинг'),
                (4, 'Стивен', 'Хокинг'),
                (5, 'Дмитрий', 'Котеров'),
                (6, 'Игорь', 'Симдянов'),
                (7, 'An author', 'Without books');
            ";
        $sql .= "
            INSERT INTO
                publisher
            VALUES
                (1, 'АСТ', 'г. Москва, Пресненская наб., д.6, стр.2, БЦ «Империя»'),
                (2, 'Эксмо', '123308, г. Москва, ул. Зорге, д.1, стр.1.'),
                (3, 'Астрель', 'Петроградский район, БЦ \"Сенатор\", Чапаева, 15, Санкт-Петербург, 197101'),
                (4, 'БХВ', 'ул. Гончарная, дом 20, пом. 7Н 191036, Санкт-Петербург'),
                (5, 'A publisher without books', 'USA, New York');
            ";
        $sql .= "
            INSERT INTO
                book
            VALUES
                (1, 1, '1984', '2022'),
                (2, 2, '451 градус по Фаренгейту', '2018'),
                (3, 3, '11/22/63', '2018'),
                (4, 1, 'Краткая история времени. От Большого взрыва до черных дыр', '2019'),
                (5, 2, 'Краткие ответы на большие вопросы', '2019'),
                (6, 4, 'PHP 8', '2023'),
                (7, NULL, 'A book without an author and a publisher', '2023'),
                (8, NULL, 'A book without a publisher', '2023'),
                (9, 1, 'A book without an author', NULL);
            ";
        $sql .= "
            INSERT INTO
                book_author
            VALUES
                (1, 1),
                (2, 2),
                (3, 3),
                (4, 4),
                (5, 4),
                (6, 5),
                (6, 6),
                (8, 6);
            ";

        return $sql;
    }
    
    private function dropDatabase($output): int
    {
        $input = new ArrayInput([
            'command' => 'doctrine:database:drop',
            '--force'  => true,
        ]);
        $returnCode = $this->getApplication()->doRun($input, $output);
        
        return $returnCode;
    }
    
    private function createDatabase($output): int
    {
        $input = new ArrayInput([
            'command' => 'doctrine:database:create',
        ]);
        $returnCode = $this->getApplication()->doRun($input, $output);
        
        return $returnCode;
    }
    
    private function migrate($output): int
    {
        $input = new ArrayInput([
            'command' => 'doctrine:migrations:migrate',
        ]);
        $input->setInteractive(false);
        
        $returnCode = $this->getApplication()->doRun($input, $output);
        
        return $returnCode;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sql = $this->getSql();
        
        $io = new SymfonyStyle($input, $output);
        
        $conn = $this->entityManager->getConnection();
        try {
            $count = $conn->executeStatement($sql);
        } catch (UniqueConstraintViolationException $e) {
            $io->error('The database already has data.');
            
            if (!$io->confirm('Recreate it?', true)) {
                return Command::SUCCESS;
            }
            
            $this->dropDatabase($output);
            $this->createDatabase($output);
            $this->migrate($output);
            return $this->execute($input, $output);
            
        } catch (\Exception $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }
        
        $io->success("The test data was inserted successfully");
        
        return Command::SUCCESS;
    }
}
