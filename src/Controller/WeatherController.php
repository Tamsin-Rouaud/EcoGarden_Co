<?php
/**
 * Contrôleur pour l'API externe de la météo.
 * Permet de récupérer la météo en fonction :
 * - de la ville enregistrée dans le profil de l'utilisateur connecté
 * - ou d'une ville spécifiée dans l'URL.
 * Toutes les routes sont sécurisées et nécessitent une authentification (ROLE_USER ou ROLE_ADMIN).
 */
namespace App\Controller;

use App\Service\WeatherService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/meteo')]
class WeatherController extends AbstractController
{
    /**
     * Récupère la météo pour la ville de l'utilisateur actuellement connecté.
     * La ville doit être renseignée dans le champ "city" de son profil.
     * Exemple de réponse : température, humidité, vent, etc.
     */
    #[OA\Get(
        path: '/api/meteo',
        summary: 'Récupère la météo pour la ville de l’utilisateur connecté',
        security: [['bearerAuth' => []]],
        tags: ['Météo'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Météo actuelle pour la ville de l’utilisateur',
                content: new OA\JsonContent(
                    example: [
                        "city" => "Marseille",
                        "temperature" => 22.5,
                        "description" => "ensoleillé",
                        "humidity" => 56,
                        "wind_speed" => 3.4
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Demande mal formulée'),
            new OA\Response(response: 401, description: 'Utilisateur non authentifié'),
            new OA\Response(response: 404, description: 'Ville non trouvée ou météo indisponible')
        ]
    )]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('', name: 'get_weather_by_user_city', methods: ['GET'])]
    public function getWeatherByUserCity(WeatherService $weatherService): JsonResponse
    {
        $user = $this->getUser();

        // Si l'utilisateur ou sa ville n'est pas définie
        if (!$user || !$user->getCity()) {
            return new JsonResponse(['error' => 'Ville non définie pour l’utilisateur.'], Response::HTTP_NOT_FOUND);
        }

        // Récupération de la météo via le service dédié
        $weather = $weatherService->getWeatherByCity($user->getCity());

        if (!$weather) {
            return new JsonResponse(['error' => 'Météo introuvable pour cette ville.'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($weather);
    }


    /** Récupère la météo d'une ville donnée, passée en paramètre d'URL. Permet à un utilisateur connecté de consulter la météo d'une autre ville que la sienne. */
    #[OA\Get(
        path: '/api/meteo/{city}',
        summary: 'Récupère la météo d’une ville spécifique',
        security: [['bearerAuth' => []]],
        tags: ['Météo'],
        parameters: [
            new OA\Parameter(
                name: 'city',
                in: 'path',
                required: true,
                description: 'Nom de la ville',
                schema: new OA\Schema(type: 'string', example: 'Paris')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Météo actuelle pour la ville spécifiée',
                content: new OA\JsonContent(
                    example: [
                        "city" => "Paris",
                        "temperature" => 19.2,
                        "description" => "nuageux",
                        "humidity" => 63,
                        "wind_speed" => 4.1
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Demande mal formulée'),
            new OA\Response(response: 401, description: 'Utilisateur non authentifié'),
            new OA\Response(response: 404, description: 'Ville non trouvée ou météo indisponible')
        ]
    )]
    #[Route('/{city}', name: 'get_weather_by_city', methods: ['GET'])]
    public function getWeatherByCity(string $city, WeatherService $weatherService): JsonResponse
    {
        $weather = $weatherService->getWeatherByCity($city);

        if (!$weather) {
            return new JsonResponse(['error' => 'Météo introuvable pour cette ville.'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($weather);
    }
}
