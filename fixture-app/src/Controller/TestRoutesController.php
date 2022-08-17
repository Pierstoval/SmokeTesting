<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TestRoutesController extends AbstractController
{
    #[Route("/200", name: "get_200")]
    public function getOk(): Response
    {
        return new Response('200');
    }

    #[Route("/302", name: "get_302")]
    public function getRedirect(): RedirectResponse
    {
        return new RedirectResponse('/200');
    }

    #[Route("/400", name: "get_400")]
    public function get400(): Response
    {
        return new Response('400', 400);
    }

    #[Route("/500", name: "get_500")]
    public function get500(): Response
    {
        return new Response('500', 500);
    }
}
