<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TestRoutesController extends AbstractController
{
    #[Route("/param/{id}", name: "get_param_without_default")]
    public function getParameterWithoutDefault(string $id): Response
    {
        return new Response("Content: $id");
    }

    #[Route("/other_param/{id}", name: "get_param_with_default", defaults: ["id" => "default_value"])]
    public function getParameterWithDefault(string $id): Response
    {
        return new Response("Content: $id");
    }

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

    #[Route("/payload", name: "get_with_payload")]
    public function getWithPayload(Request $request): Response
    {
        $payload = $request->getContent();

        return new Response($payload, $payload ? 200 : 400);
    }

    #[Route("/json/valid", name: "json_valid")]
    public function getValidJson(): Response
    {
        return new JsonResponse([
            'message' => 'Ok!',
            'code' => 200,
        ]);
    }

    #[Route("/json/missing_header", name: "json_missing_header")]
    public function getJsonInvalidHeader(): Response
    {
        return new Response(json_encode([
            'message' => 'I miss the JSON response header!',
            'code' => 200,
        ]));
    }

    #[Route("/json/invalid", name: "json_invalid")]
    public function getJsonInvalid(): Response
    {
        return new Response('{"message":', 200, [
            'Content-Type' => 'application/json',
        ]);
    }
}
