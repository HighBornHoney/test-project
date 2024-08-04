<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(
    name: 'delete:authors-without-books',
    description: "Deletes authors who don't have books",
)]
class DeleteAuthorsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp('This command finds authors without books and deletes them');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        if (!$io->confirm('Are you sure you want to proceed?', true)) {
            $io->error('Deleting cancelled!');
            return Command::SUCCESS;
        }
        
        $conn = $this->entityManager->getConnection();
        
        $sql = 'DELETE FROM author WHERE id NOT IN (SELECT DISTINCT author_id FROM book_author)';
        $count = $conn->executeStatement($sql);
        
        $io->success("$count authors without books were deleted");

        return Command::SUCCESS;
    }
}
