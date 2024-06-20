<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Service\Document\WriteManager;
use Overblog\GraphQLBundle\Event\ExecutorArgumentsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class GraphQLPreExecutorEventListener implements EventSubscriberInterface
{

    public function __construct(
        private WriteManager $writeManager
    ) {
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return array(
            'graphql.pre_executor' => ['forceUseTransaction', 0]
        );
    }

    /**
     * forceUseTransaction:
     * 
     * The "atomicMutate" mutations is a fake mutation which is used 
     * to instruct the server to execute all the mutations in an atomic way.
     *  
     * Force the use of a transaction for the next flush operation 
     * when the "atomicMutate" mutation is in the executed mutations.
     *
     * @param  ExecutorArgumentsEvent $event
     * @return void
     */
    public function forceUseTransaction(ExecutorArgumentsEvent $event): void
    {
        if (str_contains($event->getRequestString(), 'atomicMutate')) {
            $this->writeManager->forceUseTransaction();
        }
    }
}
