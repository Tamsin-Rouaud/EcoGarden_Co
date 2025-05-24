<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/user')]
class UserController extends AbstractController
{
    #[OA\Post(summary: 'Créer un nouvel utilisateur', tags: ['Utilisateur'], requestBody: new OA\RequestBody( required: true, content: new OA\JsonContent( required: ['email', 'password', 'city'], properties: [
                    new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'password'),
                    new OA\Property(property: 'city', type: 'string', example: 'Marseille'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Utilisateur créé'),
            new OA\Response(response: 400, description: 'Erreur de validation'),
            new OA\Response(response: 409, description: 'Email déjà utilisé')
        ]
    )]
    #[Route('', name: 'createUser', methods: ['POST'])]
    public function createUser(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher,      UserRepository $userRepository ): JsonResponse {
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return new JsonResponse($errorMessages, Response::HTTP_BAD_REQUEST);
        }

        if ($userRepository->findOneBy(['email' => $user->getEmail()])) {
            return new JsonResponse(['email' => 'Cet email est déjà utilisé.'], Response::HTTP_CONFLICT);
        }

        $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
        $user->setPassword($hashedPassword);

        $em->persist($user);
        $em->flush();

        return new JsonResponse(['message' => 'Utilisateur créé avec succès.'], Response::HTTP_CREATED);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[OA\Get( summary: 'Lister tous les utilisateurs', security: [['bearerAuth' => []]], tags: ['Utilisateur'], responses: [ new OA\Response(response: 200, description: 'Liste des utilisateurs'), new OA\Response(response: 403, description: 'Accès refusé')] )]
    #[Route('', name: 'get_all_users', methods: ['GET'])]
    public function getAllUsers( UserRepository $userRepository, SerializerInterface $serializer ): JsonResponse {
        $users = $userRepository->findAll();
        $json = $serializer->serialize($users, 'json', ['groups' => 'getUsers']);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'update_user', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Put(
        summary: 'Mettre à jour un utilisateur',
        security: [['bearerAuth' => []]],
        tags: ['Utilisateur'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'email', type: 'string'),
                    new OA\Property(property: 'password', type: 'string'),
                    new OA\Property(property: 'city', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Utilisateur mis à jour'),
            new OA\Response(response: 400, description: 'Erreur de validation'),
            new OA\Response(response: 404, description: 'Utilisateur non trouvé')
        ]
    )]
    public function updateUser(int $id, Request $request, UserRepository $userRepository, EntityManagerInterface $em, SerializerInterface $serializer, ValidatorInterface $validator, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $user = $userRepository->find($id);

        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        $serializer->deserialize($request->getContent(), User::class, 'json', [
            AbstractNormalizer::OBJECT_TO_POPULATE => $user,
        ]);

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return new JsonResponse($errorMessages, Response::HTTP_BAD_REQUEST);
        }

        if ($request->toArray()['password'] ?? null) {
            $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);
        }

        $em->flush();

        return new JsonResponse(['message' => 'Utilisateur mis à jour avec succès.'], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete_user', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Delete(
        summary: 'Supprimer un utilisateur',
        security: [['bearerAuth' => []]],
        tags: ['Utilisateur'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Utilisateur supprimé'),
            new OA\Response(response: 404, description: 'Utilisateur non trouvé')
        ]
    )]
    public function deleteUser(int $id, UserRepository $userRepository, EntityManagerInterface $em): JsonResponse
    {
        $user = $userRepository->find($id);

        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($user);
        $em->flush();

        return new JsonResponse(['message' => 'Utilisateur supprimé avec succès.'], Response::HTTP_NO_CONTENT);
    }
}
