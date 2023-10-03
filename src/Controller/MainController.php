<?php

namespace App\Controller;

use App\Service\RequestHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route("/api/apod/{date}", name: "api_apod")]
    public function api(RequestHandler $requestHandler, string|null $date = null): JsonResponse
    {
        $data = $requestHandler->nasaApiRequest("apod/$date");
        return $this->json($data->toArray(), $data->getStatusCode());
    }
}