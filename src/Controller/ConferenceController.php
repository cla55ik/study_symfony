<?php

namespace App\Controller;


use App\Entity\Comment;
use App\Entity\Conference;
use App\Form\CommentFormType;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConferenceController extends AbstractController
{
    /**
     * @Route ("/", name="home")
     * @param ConferenceRepository $conferenceRepository
     * @return Response
     */
    public function index(ConferenceRepository $conferenceRepository): Response
    {
        return $this->render('conference/index.html.twig', [
           'conferences'=> $conferenceRepository->findAll()
        ]);
    }

    /**
     * @Route("/conference/{slug}", name="conference")
     * @param Request $request
     * @param Conference $conference
     * @param CommentRepository $commentRepository
     * @return Response
     */
    public function show(Request $request, Conference $conference, CommentRepository $commentRepository):Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentFormType::class, $comment);

        $offset = max(0, $request->query->getInt('offset',0));
        $paginator = $commentRepository->getCommentPaginator($conference, $offset);

        return $this->render('conference/show.html.twig',[
            'conference'=>$conference,
            'comments'=>$paginator,
            'previous'=>$offset - CommentRepository::PAGINATOR_PER_PAGE,
            'next'=>min(count($paginator), $offset + CommentRepository::PAGINATOR_PER_PAGE),
            'comment_form'=>$form->createView()
        ]);
    }
}
