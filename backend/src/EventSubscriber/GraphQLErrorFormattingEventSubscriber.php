<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Overblog\GraphQLBundle\Event\ErrorFormattingEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Service\Constant\ExceptionCodes;

final class GraphQLErrorFormattingEventSubscriber implements EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return array(
            'graphql.error_formatting' => ['formatError', 0]
        );
    }

    /**
     * formatError: add the exception's code in the json response
     * under the 'extensions' key of the error 
     * 
     * e.g. when an AccessDeniedHttpException is thrown, the http response body would look like this:
     * { "errors": [ { 
     *      "message": "Access denied",
     *      "extensions": { 
     *          "category": "user",
     *           "code": 5120,
     *          } 
     *      } ],
     *      ... HERE OTHER RESPONSE DATA ...
     * }
     * 
     *
     * @param  ErrorFormattingEvent $event
     * @return void
     */
    public function formatError(ErrorFormattingEvent $event)
    {
        $error = $event->getError();
        $previous = isset($error) ? $error->getPrevious() : null;
        $prePrevious = isset($previous) ? $previous->getPrevious() : null;

        $formattedError = $event->getFormattedError();

        if (isset($prePrevious)) {
            $formattedError['extensions']['code'] = $prePrevious->getCode();
        }

        if (isset($previous)) {
            $formattedError['extensions']['code'] = $previous->getCode();
        }

        if ($error->getCategory() === 'graphql') {
            $formattedError['extensions']['code'] = ExceptionCodes::BAD_REQUEST_EXCEPTION;
        }
    }
}
