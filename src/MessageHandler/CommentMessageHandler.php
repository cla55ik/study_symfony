<?php

namespace App\MessageHandler;

use App\Message\CommentMessage;
use App\Repository\CommentRepository;
use App\Service\SpamCheckerService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\WorkflowInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

class CommentMessageHandler implements MessageHandlerInterface
{
    private EntityManagerInterface $entityManager;
    private CommentRepository $commentRepository;
    private SpamCheckerService $spamCheckerService;
    private MessageBusInterface $bus;
    private WorkflowInterface $workflow;
    private LoggerInterface $logger;
    private string $adminEmail;
    private MailerInterface $mailer;

    public function __construct(EntityManagerInterface $entityManager,
                                CommentRepository $commentRepository,
                                SpamCheckerService $spamCheckerService,
                                MessageBusInterface $bus,
                                WorkflowInterface $commentStateMachine,
                                LoggerInterface $logger,
                                MailerInterface $mailer,
                                string $adminEmail
    )
    {
        $this->entityManager = $entityManager;
        $this->commentRepository = $commentRepository;
        $this->spamCheckerService = $spamCheckerService;
        $this->bus = $bus;
        $this-> workflow = $commentStateMachine;
        $this->logger = $logger;
        $this->mailer = $mailer;
        $this->adminEmail = $adminEmail;
    }

    /**
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function __invoke(CommentMessage $message)
    {
        $comment = $this->commentRepository->find($message->getId());
        if (!$comment){
            return;
        }

        if ($this->workflow->can($comment, 'accepts')){
            $status = $this->spamCheckerService->getSpamCheck($comment) ? 'confirmation' : 'spam';
            $transition = 'accepts';
            if ('spam' === $status){
                $transition = 'reject_spam';
            }
            $this->workflow->apply($comment, $transition);
            $this->entityManager->flush();
//            $this->bus->dispatch($message);

        } elseif($this->workflow->can($comment, 'publish')) {
            $this->workflow->apply($comment, 'published');
        }else{
            $this->logger->bebug('Dropping comment message', ['comment'=>$comment->getId(), 'state'=>$comment->getState()]);
        }


//        $comment->setState($status);
//        $this->entityManager->flush();

    }
}