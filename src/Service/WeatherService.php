<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class WeatherService
{
    private HttpClientInterface $client;
    private string $apiKey;
    private TagAwareCacheInterface $cache;

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
     * Récupère la météo d'une ville avec mise en cache pendant 1 heure.
     */
   public function getWeatherByCity(string $city): array
{
    $cacheKey = 'weather_' . strtolower($city);
    $isCached = true; // On suppose que c’est en cache

    $weather = $this->cache->get($cacheKey, function (ItemInterface $item) use ($city, &$isCached) {
        $isCached = false; // On entre ici uniquement si ce n’était pas en cache
        $item->expiresAfter(3600); // 1 heure de cache

        // Appel réel à l’API
        $response = $this->client->request('GET', 'https://api.openweathermap.org/data/2.5/weather', [
            'query' => [
                'q' => $city,
                'appid' => $this->apiKey,
                'units' => 'metric',
                'lang' => 'fr',
            ],
        ]);

        // Tu peux ajouter ce sleep(2) pour démonstration :
        sleep(5); // TEMPORAIRE, pour prouver que ça vient du cache après le premier appel

        return $response->toArray();
    });

    // On retourne un tableau enrichi pour vérification dans la réponse de l’API
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
