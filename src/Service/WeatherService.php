<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Service responsable de récupérer la météo depuis l'API OpenWeatherMap
 * avec un système de cache pour éviter les appels API inutiles.
 */
class WeatherService
{
    // Client HTTP injecté pour interroger une API externe
    private HttpClientInterface $client;

    // Clé API pour authentification auprès de l'API météo
    private string $apiKey;

    // Cache avec support des tags (utile pour invalidation ciblée si besoin)
    private TagAwareCacheInterface $cache;

    /**
     * Le constructeur injecte les dépendances nécessaires via autowiring.
     * - HttpClientInterface : pour les requêtes HTTP externes
     * - TagAwareCacheInterface : pour stocker les réponses de manière temporaire
     * - string $openWeatherKey : clé API (passée via le fichier de configuration services.yaml)
     */
    public function __construct(
        HttpClientInterface $client,
        string $openWeatherKey,
        TagAwareCacheInterface $cache
    ) {
        $this->client = $client;
        $this->apiKey = $openWeatherKey;
        $this->cache = $cache;
    }

    /**
     * Récupère la météo d'une ville donnée.
     * Si les données sont déjà présentes en cache, l'API externe n'est pas appelée.
     *
     * @param string $city Le nom de la ville
     * @return array Un tableau contenant les infos météo + un indicateur 'cached'
     */
    public function getWeatherByCity(string $city): array
    {
        // Clé unique pour le cache, basée sur le nom de la ville
        $cacheKey = 'weather_' . strtolower($city);
        $isCached = true; // Flag utilisé pour savoir si la donnée vient du cache

        // Lecture depuis le cache, ou appel API si absent
        $weather = $this->cache->get($cacheKey, function (ItemInterface $item) use ($city, &$isCached) {
            $isCached = false; // Si on entre ici, c’est qu’on n’a pas trouvé dans le cache
            $item->expiresAfter(3600); // Durée de vie en cache : 1 heure

            // Appel réel à l’API OpenWeatherMap
            $response = $this->client->request('GET', 'https://api.openweathermap.org/data/2.5/weather', [
                'query' => [
                    'q' => $city,
                    'appid' => $this->apiKey,
                    'units' => 'metric',
                    'lang' => 'fr',
                ],
            ]);

            // Délai simulé pour prouver l’appel API pendant la soutenance
            sleep(5); // À retirer si production car ralentissement inutile

            return $response->toArray();
        });

        // Structure de la réponse : les infos utiles + indication "cached"
        return [
            'cached' => $isCached,
            'city' => $weather['name'],
            'temperature' => $weather['main']['temp'],
            'description' => $weather['weather'][0]['description'],
            'humidity' => $weather['main']['humidity'],
            'wind_speed' => $weather['wind']['speed'],
        ];
    }
}
