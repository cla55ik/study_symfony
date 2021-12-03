<?php

namespace App\MessageHandler;

use App\Message\CommentMessage;
use App\Repository\CommentRepository;
use App\Service\SpamCheckerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

class CommentMessageHandler implements MessageHandlerInterface
{
    private EntityManagerInterface $entityManager;
    private CommentRepository $commentRepository;
    private SpamCheckerService $spamCheckerService;

    public function __construct(EntityManagerInterface $entityManager, CommentRepository $commentRepository, SpamCheckerService $spamCheckerService)
    {
        $this->entityManager = $entityManager;
        $this->commentRepository = $commentRepository;
        $this->spamCheckerService = $spamCheckerService;
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
            $status = $this->spamCheckerService->getSpamCheck($comment) ? 'published' : 'spam';
        }



        $comment->setState($status);
        $this->entityManager->flush();

    }
}