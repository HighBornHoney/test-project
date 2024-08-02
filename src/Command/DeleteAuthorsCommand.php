<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Author;

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

        $authors = $this->entityManager->getRepository(Author::class)->findAll();

        $deleted_authors_count = 0;
        
        foreach ($authors as $author) {
            if ($author->getBooks()->count() === 0) {
                $this->entityManager->remove($author);
                $deleted_authors_count++;
            }
        }
            
        $this->entityManager->flush();

        $io->success("Success! There were deleted $deleted_authors_count authors without books");

        return Command::SUCCESS;
    }
}
