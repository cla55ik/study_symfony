<?php

namespace App\Controller;


use App\Entity\Comment;
use App\Entity\Conference;
use App\Form\CommentFormType;
use App\Message\CommentMessage;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;
use App\Service\ImageOptimizerService as ImageOptimizerServiceAlias;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Routing\Annotation\Route;

class ConferenceController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private MessageBusInterface $bus;
    private ImageOptimizerServiceAlias $imageOptimizerService;
    private string $photoDir;

    public function __construct(EntityManagerInterface $entityManager, MessageBusInterface $bus, ImageOptimizerServiceAlias $imageOptimizerService, string $photoDir)
    {
        $this->entityManager = $entityManager;
        $this->bus = $bus;
        $this->imageOptimizerService = $imageOptimizerService;
        $this->photoDir = $photoDir;

    }

    /**
     * @Route ("/", name="homepage")
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
     * @throws Exception
     */
    public function show(Request $request,
                         Conference $conference,
                         NotifierInterface $notifier,
                         CommentRepository $commentRepository):Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentFormType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $comment->setConference($conference);
            if ($photo = $form['photo']->getData()){
                $filename = bin2hex((random_bytes(6)).'.'.$photo->guessExtension());
                try{
                    $photo->move($this->photoDir, $filename);
                    }catch (FileException $e){

                }
                $comment->setPhotoFilename($filename);
                $this->imageOptimizerService->resize($this->photoDir.'/'.$comment->getPhotoFilename());
            }
            $this->entityManager->persist($comment);
            $this->entityManager->flush();

            $this->bus->dispatch(new CommentMessage($comment->getId()));

            $notifier->send(new Notification('Thant you for the feedback', ['browser']));

            return $this->redirectToRoute('conference', ['slug'=>$conference->getSlug()]);
        }

        if($form->isSubmitted()){
            $notifier->send(new Notification('Can you check submission?', ['browser']));
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

    /**
     * @Route("/conference_header", name="conference_header")
     * @param ConferenceRepository $conferenceRepository
     * @return Response
     */
    public function conferenceHeader(ConferenceRepository $conferenceRepository): Response
    {
        return $this->render('conference/header.html.twig', [
            'conferences' => $conferenceRepository->findAll()
        ]);
    }
}
