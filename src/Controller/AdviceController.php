<?php

/**
 * Contrôleur des conseils mensuels.
 * Gère les routes sécurisées pour récupérer, créer, mettre à jour ou supprimer des conseils de jardinage.
 * Accessible uniquement aux utilisateurs authentifiés, avec des droits spécifiques pour les administrateurs.
 */

namespace App\Controller;

use App\Entity\Advice;
use App\Repository\AdviceRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/conseil')]
#[IsGranted('IS_AUTHENTICATED_FULLY')] // Toutes les routes du contrôleur nécessitent une authentification
class AdviceController extends AbstractController
{

     /** Crée un nouveau conseil. Réservé aux administrateurs. */
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Post(
        summary: 'Créer un nouveau conseil',
        security: [['bearerAuth' => []]],
        tags: ['Conseil'],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                type: 'object',
                required: ['description', 'month'],
                properties: [
                    new OA\Property(property: 'description', type: 'string'),
                    new OA\Property(property: 'month', type: 'array', items: new OA\Items(type: 'integer')),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Conseil créé'),
            new OA\Response(response: 400, description: 'Erreur de validation'),
            new OA\Response(response: 403, description: 'Accès refusé : réservé aux administrateurs'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    #[Route('', name: 'createAdvice', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function createAdvice(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, EntityManagerInterface $em,        UrlGeneratorInterface $urlGenerator ): JsonResponse {
        $advice = $serializer->deserialize($request->getContent(), Advice::class, 'json');
        $errors = $validator->validate($advice);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return new JsonResponse($errorMessages, Response::HTTP_BAD_REQUEST);
        }
        // L'utilisateur connecté est automatiquement récupéré pour définir le créateur
        $user = $this->getUser();
        $advice->setCreatedBy($user);

        $em->persist($advice);
        $em->flush();

        $jsonAdvice = $serializer->serialize($advice, 'json', ['groups' => 'getAdvices']);
        $location = $urlGenerator->generate('createAdvice', ['id' => $advice->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonAdvice, Response::HTTP_CREATED, ['Location' => $location], true);
    }



    /** Retourne tous les conseils du mois en cours. */
    #[OA\Get(
        summary: 'Liste des conseils du mois en cours',
        security: [['bearerAuth' => []]],
        tags: ['Conseil'],
        responses: [
            new OA\Response(response: 200, description: 'Liste de conseils du mois courant'),
            new OA\Response(response: 401, description: 'Non authentifié')
        ]
    )]
    #[Route('', name: 'advices', methods: ['GET'])]
    public function getAdviceList(AdviceRepository $adviceRepository, SerializerInterface $serializer ): JsonResponse {
        $currentMonth = (int) date('n');
        $adviceList = $adviceRepository->findByMonth($currentMonth);
        $jsonAdviceList = $serializer->serialize($adviceList, 'json', ['groups' => 'getAdvices']);

        return new JsonResponse($jsonAdviceList, Response::HTTP_OK, [], true);
    }


    
    /** Retourne les conseils pour un mois spécifique. */

    #[OA\Get(
        summary: 'Récupérer les conseils pour un mois donné',
        security: [['bearerAuth' => []]],
        tags: ['Conseil'],
        parameters: [
            new OA\Parameter(name: 'mois', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Conseils du mois'),
            new OA\Response(response: 400, description: 'Mois invalide'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    #[Route('/{mois}', name: 'getAdviceByMonth', requirements: ['mois' => '\d+'], methods: ['GET'])]
    public function getAdviceByMonth(int $mois, AdviceRepository $adviceRepository, SerializerInterface $serializer ): JsonResponse {
        if ($mois < 1 || $mois > 12) {
            throw new BadRequestHttpException("Mois invalide. Il doit être entre 1 et 12.");
        }

        $data = $adviceRepository->findByMonth($mois);

        $advices = array_map(fn($row) => $adviceRepository->find($row['id']), $data);

        $jsonAdviceList = $serializer->serialize($advices, 'json', ['groups' => 'getAdvices']);

        return new JsonResponse($jsonAdviceList, Response::HTTP_OK, [], true);
    }


    
    /** Met à jour un conseil existant. Réservé aux administrateurs. */
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Put(
        summary: 'Mettre à jour un conseil',
        security: [['bearerAuth' => []]],
        tags: ['Conseil'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'description', type: 'string'),
                    new OA\Property(property: 'month', type: 'array', items: new OA\Items(type: 'integer')),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 204, description: 'Conseil mis à jour'),
            new OA\Response(response: 400, description: 'Erreur de validation'),
            new OA\Response(response: 403, description: 'Accès refusé : réservé aux administrateurs'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    #[Route('/{id}', name: 'updateAdvice', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function updateAdvice(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, Advice $currentAdvice,        EntityManagerInterface $em ): JsonResponse {
        $updatedAdvice = $serializer->deserialize($request->getContent(),Advice::class,'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentAdvice] );

        $errors = $validator->validate($updatedAdvice);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return new JsonResponse($errorMessages, Response::HTTP_BAD_REQUEST);
        }

        // Mise à jour du créateur (utile si modifié par un admin pour le compte d’un autre)
        $user = $this->getUser();
        $updatedAdvice->setCreatedBy($user);

        $em->persist($updatedAdvice);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    
    /** Supprime un conseil. Réservé aux administrateurs.*/
    #[OA\Delete(
        summary: 'Supprimer un conseil',
        security: [['bearerAuth' => []]],
        tags: ['Conseil'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Conseil supprimé'),
            new OA\Response(response: 400, description: 'Erreur de validation'),
            new OA\Response(response: 403, description: 'Accès refusé : réservé aux administrateurs'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    #[Route('/{id}', name: 'deleteAdvice', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteAdvice(Advice $advice, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($advice);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

}
