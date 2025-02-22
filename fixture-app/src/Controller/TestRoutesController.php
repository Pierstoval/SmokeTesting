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
    /** @Route("/other_param/{id}", name="get_param_with_default", defaults={"id": "default_value"}, methods={"GET"}) */
    #[Route("/other_param/{id}", name: "get_param_with_default", defaults: ["id" => "default_value"], methods: ["GET"])]
    public function getParameterWithDefault(string $id): Response
    {
        return new Response("Content: $id");
    }

    /** @Route("/200", name="get_200", methods={"GET"}) */
    #[Route("/200", name: "get_200", methods: ["GET"])]
    public function getOk(): Response
    {
        return new Response('200');
    }

    /** @Route("/302", name="get_302", methods={"GET"}) */
    #[Route("/302", name: "get_302", methods: ["GET"])]
    public function getRedirect(): RedirectResponse
    {
        return new RedirectResponse('/200');
    }

    /** @Route("/400", name="get_400", methods={"GET"}) */
    #[Route("/400", name: "get_400", methods: ["GET"])]
    public function get400(): Response
    {
        return new Response('400', 400);
    }

    /** @Route("/500", name="get_500", methods={"GET"}) */
    #[Route("/500", name: "get_500", methods: ["GET"])]
    public function get500(): Response
    {
        return new Response('500', 500);
    }

    /** @Route("/payload", name="get_with_payload", methods={"GET"}) */
    #[Route("/payload", name: "get_with_payload", methods: ["GET"])]
    public function getWithPayload(Request $request): Response
    {
        $payload = $request->getContent();

        return new Response($payload, $payload ? 200 : 400);
    }

    /** @Route("/json/valid", name="json_valid", methods={"GET"}) */
    #[Route("/json/valid", name: "json_valid", methods: ["GET"])]
    public function getValidJson(): Response
    {
        return new JsonResponse([
            'message' => 'Ok!',
            'code' => 200,
        ]);
    }

    /** @Route("/json/valid-header", name="json_valid_header", methods={"GET"}) */
    #[Route("/json/valid-header", name: "json_valid_header", methods: ["GET"])]
    public function getValidJsonHeader(): Response
    {
        return new JsonResponse([
            'message' => 'Ok!',
            'code' => 200,
        ], 200, ['Content-Type' => 'application/json; charset=utf-8']);
    }

    /** @Route("/json/missing_header", name="json_missing_header", methods={"GET"}) */
    #[Route("/json/missing_header", name: "json_missing_header", methods: ["GET"])]
    public function getJsonInvalidHeader(): Response
    {
        return new Response(json_encode([
            'message' => 'I miss the JSON response header!',
            'code' => 200,
        ]));
    }

    /** @Route("/json/invalid", name="json_invalid", methods={"GET"}) */
    #[Route("/json/invalid", name: "json_invalid", methods: ["GET"])]
    public function getJsonInvalid(): Response
    {
        return new Response('{"message":', 200, [
            'Content-Type' => 'application/json',
        ]);
    }

    /** @Route("/cookie/value", name="cookie_value", methods={"GET"}) */
    #[Route("/cookie/value", name: "cookie_value", methods: ["GET"])]
    public function getCookieValue(Request $request): Response
    {
        return new Response(\sprintf('Value: "%s"', $request->cookies->get('test_cookie')));
    }

    /** @Route("/host/fixed", name="host_fixed", host="test.localhost", methods={"GET"}) */
    #[Route("/host/fixed", name: "host_fixed", host: "test.localhost", methods: ["GET"])]
    public function getHostFixed(Request $request): Response
    {
        return new Response(\sprintf('Value: "%s"', $request->getHost()));
    }

    /** @Route("/host/dynamic", name="host_dynamic", host="{dynamic_host}", methods={"GET"}) */
    #[Route("/host/dynamic", name: "host_dynamic", host: "{dynamic_host}", methods: ["GET"])]
    public function getHostDynamic(): Response
    {
        throw new \RuntimeException('This route should not be tested by AllRoutesTest');
    }

    /** @Route("/scheme/fixed", name="scheme_fixed", schemes="http", methods={"GET"}) */
    #[Route("/scheme/fixed", name: "scheme_fixed", schemes: 'http', methods: ['GET'])]
    public function getSchemeFixed(Request $request): Response
    {
        return new Response(\sprintf('Value: "%s"', $request->getScheme()));
    }

    /** @Route("/scheme/dynamic", name="scheme_dynamic", schemes="{dynamic_scheme}", methods={"GET"}) */
    #[Route("/scheme/dynamic", name: "scheme_dynamic", schemes: "{dynamic_scheme}", methods: ["GET"])]
    public function getSchemeDynamic(): Response
    {
        throw new \RuntimeException('This route should not be tested by AllRoutesTest');
    }

    /** @Route("/content-type", name="content_type", methods={"GET"}) */
    #[Route("/content-type", name: "content_type", methods: ["GET"])]
    public function getContentType(Request $request): Response
    {
        $contentType = method_exists($request, 'getContentTypeFormat')
            ? $request->getContentTypeFormat()
            : $request->getContentType();

        return new JsonResponse([
            'header' => $request->headers->get('Content-Type'),
            'server_normalized' => $request->server->get('HTTP_CONTENT_TYPE'),
            'server_denormalized' => $request->server->get('CONTENT_TYPE'),
            'format' => $contentType,
        ]);
    }

    /** @Route("/post", name="post_route", methods={"POST"}) */
    #[Route('/post', name: 'post_route', methods: ["POST"])]
    public function getPostRoute(Request $request): Response
    {
        throw new \RuntimeException('This route should not be tested by AllRoutesTest');
    }
}
