<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Message\CommentMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin", name="admin")
 */
class AdminController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private MessageBusInterface $bus;

    public function __construct(EntityManagerInterface $entityManager, MessageBusInterface $bus)
    {
        $this->entityManager = $entityManager;
        $this->bus = $bus;
    }

    /**
     * @Route("/comment/review/{id}", name="review_comment")
     * @param Request $request
     * @param Comment $comment
     * @param Registry $registry
     * @return Response
     */
    public function reviewComment(Request $request, Comment $comment, Registry $registry):Response
    {
        $accepted = !$request->query->get('reject');
        $machine = $registry->get($comment);

        if($machine->can($comment, 'publish')){
            $transition = $accepted ? 'publish' : 'reject';
        }else {
            return $this->render('admin/review.html.twig', [
                'transition'=>'Comment already reviewed',

            ]);
//            return new Response('Comment already reviewed' . $request->query->get('reject'));
        }
        $machine->apply($comment, $transition);
        $this->entityManager->flush();

        if ($accepted){
            $this->bus->dispatch(new CommentMessage($comment->getId()));
        }
        return $this->render('admin/review.html.twig', [
            'transition'=>$transition,
            'comment'=>$comment
        ]);
    }
}