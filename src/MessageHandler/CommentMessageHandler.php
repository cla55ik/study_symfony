<?php

namespace App\MessageHandler;

use App\Message\CommentMessage;
use App\Repository\CommentRepository;
use App\SpamChecker;
use Doctrine\ORM\EntityManagerInterface;

class CommentMessageHandler
{
    private SpamChecker $spamChecker;
    private EntityManagerInterface $entityManager;
    private CommentRepository $commentRepository;

    public function __construct(EntityManagerInterface $entityManager, SpamChecker $spamChecker, CommentRepository $commentRepository)
    {
        $this->spamChecker = $spamChecker;
        $this->entityManager = $entityManager;
        $this->commentRepository = $commentRepository;
    }

    public function __invoke(CommentMessage $message)
    {
        $comment = $this->commentRepository->find($message->getId());
        if (!$comment){
            return;
        }

        if(2 === $this->spamChecker->getSpamScore($comment, $message->getContext())){
            $comment->setState('spam');
        }else{
            $comment->setState('published');
        }

        $this->entityManager->flush();

    }
}