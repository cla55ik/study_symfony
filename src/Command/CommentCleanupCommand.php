<?php

namespace App\Command;

use App\Repository\CommentRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CommentCleanupCommand extends Command
{
    private CommentRepository $commentRepository;
    protected static $defaultName = 'app:comment:cleanup';

    /**
     * @param CommentRepository $commentRepository
     */
    public function __construct(CommentRepository $commentRepository)
    {
        $this->commentRepository = $commentRepository;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Deletes comment from database')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Dry run')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if($input->getOption('dry-run')){
            $io->note('dry mode enabled');

            $count = $this->commentRepository->countOldRejectded();
        }else{
            $count = $this->commentRepository->deleteOldRejected();
        }

        $io->success(sprintf('Delete "%d" old spam comments.', $count));
        return 0;
    }
}