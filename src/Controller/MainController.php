<?php

namespace App\Controller;

use App\Service\RequestHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route("/api/apod/{date}", name: "api_apod")]
    public function apiApodDate(RequestHandler $requestHandler, string|null $date = null): JsonResponse
    {
        $data = $requestHandler->nasaApiRequest("apod/$date");
        return $this->json($data->toArray(), $data->getStatusCode());
    }

    #[Route("/api/rover/{rover}/{sol}")]
    public function apiRoverSol(RequestHandler $requestHandler, string $rover, int $sol): JsonResponse
    {
        if (!in_array($rover, ['curiosity', 'opportunity', 'spirit', 'perseverance'])) return $this->json('Wrong rover name');

        $data = $requestHandler->nasaApiRequest("mrp-sol/$rover/$sol");
        return $this->json($data->toArray(), $data->getStatusCode());
    }
}