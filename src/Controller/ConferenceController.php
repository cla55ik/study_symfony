<?php

namespace App\Controller;


use App\Entity\Comment;
use App\Entity\Conference;
use App\Form\CommentFormType;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;

use App\SpamChecker;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConferenceController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

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
    public function show(Request $request, Conference $conference, CommentRepository $commentRepository, string $photoDir, SpamChecker $spamChecker):Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentFormType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $comment->setConference($conference);
            if ($photo = $form['photo']->getData()){
                $filename = bin2hex((random_bytes(6)).'.'.$photo->guessExtension());
                try{
                    $photo->move($photoDir, $filename);
                }catch (FileException $e){

                }
                $comment->setPhotoFilename($filename);
            }
            $this->entityManager->persist($comment);

            $context = [
                'user_ip'=>$request->getClientIp(),
                'user_agent'=>$request->headers->get('user-agent'),
                'referrer' => $request->headers->get('referrer'),
                'permalink' => $request->getUri()
            ];

            if (2 == $spamChecker->getSpamScore($comment, $context)){
                throw new \RuntimeException('Spam');
            }

            $this->entityManager->flush();
            return $this->redirectToRoute('conference', ['slug'=>$conference->getSlug()]);
        }


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
