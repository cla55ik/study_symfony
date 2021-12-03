<?php

namespace App\MessageHandler;

use App\Message\CommentMessage;
use App\Repository\CommentRepository;
use App\SpamChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

class CommentMessageHandler implements MessageHandlerInterface
{
    private EntityManagerInterface $entityManager;
    private CommentRepository $commentRepository;
    private SpamChecker $spamChecker;

    public function __construct(EntityManagerInterface $entityManager, CommentRepository $commentRepository, SpamChecker $spamChecker)
    {
        $this->entityManager = $entityManager;
        $this->commentRepository = $commentRepository;
        $this->spamChecker = $spamChecker;
    }

    /**
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function __invoke(CommentMessage $message)
    {
        $comment = $this->commentRepository->find($message->getId());
        if (!$comment){
            $status = 'error';
        }else{
            $status = $this->spamChecker->getSpamCheck($comment) ? 'published' : 'spam';
        }



        $comment->setState($status);
        $this->entityManager->flush();

    }
}