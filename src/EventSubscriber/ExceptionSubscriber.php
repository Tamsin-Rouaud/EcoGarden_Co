<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Ce subscriber permet d'intercepter toutes les exceptions levées dans l'application
 * et de retourner une réponse JSON structurée au lieu d'une page d'erreur HTML.
 *
 * Il améliore la lisibilité des erreurs pour les consommateurs de l'API,
 * notamment dans un contexte d'API REST.
 */
class ExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * Méthode appelée automatiquement lorsqu'une exception est levée.
     */
    public function onExceptionEvent(ExceptionEvent $event): void
    {
        // On récupère l'exception qui a été levée
        $exception = $event->getThrowable();

        // Si l'exception est une HttpException (avec un code d'erreur défini)
        if ($exception instanceof HttpException) {
            $data = [
                'status' => $exception->getStatusCode(),
                'message' => $exception->getMessage(),
            ];
            // On retourne une réponse JSON avec le code HTTP associé
            $event->setResponse(new JsonResponse($data));
        } else {
            // Si c'est une exception non prévue (ex: bug), on retourne une erreur 500
            $data = [
                'status' => 500,
                'message' => $exception->getMessage(), // En prod, on pourrait mettre un message générique ici
            ];
            $event->setResponse(new JsonResponse($data));
        }
    }

    /**
     * Méthode qui enregistre le subscriber pour qu'il réagisse aux exceptions.
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ExceptionEvent::class => 'onExceptionEvent',
        ];
    }
}
