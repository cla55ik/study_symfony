<?php

namespace App\MessageHandler;

use App\Message\CommentMessage;
use App\Repository\CommentRepository;
use App\SpamChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class CommentMessageHandler
{
    private EntityManagerInterface $entityManager;
    private CommentRepository $commentRepository;

    public function __construct(EntityManagerInterface $entityManager, CommentRepository $commentRepository)
    {
        $this->entityManager = $entityManager;
        $this->commentRepository = $commentRepository;
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
            return;
        }
        $comment->setState('published');

        $this->entityManager->flush();

    }
}