<?php

namespace App\Controller;

use App\Entity\Advice;
use App\Repository\AdviceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class AdviceController extends AbstractController
{
    #[Route('api/advices', name: 'advice', methods:['GET'])]
    public function getAdviceList(AdviceRepository $adviceRepository, SerializerInterface $serializer ): JsonResponse
    {
        $adviceList = $adviceRepository->findAll();

        $jsonAdviceList = $serializer->serialize($adviceList, 'json', ['groups'=>'getAdvices']);
        return new JsonResponse($jsonAdviceList, Response::HTTP_OK, [], true);
    }

    #[Route('api/advices/{id}', name: 'detailAdvice', methods:['GET'])]
    public function getDetailAdvice(Advice $advice, SerializerInterface $serializer ): JsonResponse
    {
            $jsonAdvice = $serializer->serialize($advice, 'json', ['groups'=>'getAdvices']);
            return new JsonResponse($jsonAdvice, Response::HTTP_OK, [], true);
      
    }

    #[Route('api/advices/{id}', name: 'deleteAdvice', methods:['DELETE'])]
    public function deleteAdvice(Advice $advice, EntityManagerInterface $em  ): JsonResponse
    {
            $em->remove($advice);
            $em->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
      
    }
}
