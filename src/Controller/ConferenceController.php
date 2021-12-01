<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConferenceController extends AbstractController
{
    /**
     * @Route ("/", name="home")
     * @return Response
     *
     */
    public function index(Request $request): Response
    {

        $greet = 'Hi';
        if($name=$request->query->get('hello')){
            $greet = sprintf('<div>Hello %s</div>', htmlspecialchars($name));
        }
        return new Response($greet);
    }
}
