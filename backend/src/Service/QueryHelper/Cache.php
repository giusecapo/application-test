<?php

declare(strict_types=1);

namespace App\Service\QueryHelper;

use App\Service\QueryHelper\QueryCriteria;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use function count;

/**
 * @internal
 * Cache layer for query helper
 */
final class Cache
{

    private array $documentsScheduledForInvalidation;

    public function __construct(private TagAwareCacheInterface $queryCache)
    {
        $this->documentsScheduledForInvalidation = array();
    }

    /**
     * @param  QueryCriteria $queryCriteria
     * @param  int $queryType: distinguishes query by type.
     *             Required to avoid cache key collisions when two queries
     *             have the same queryCriteria but are different (e.g. distinct vs. sum)
     * @param  bool $useCachedValue: when true the value is retrieved from the cache if available
     *             without executing the queryCallback. When false the queryCallback is executed
     *             in all cases, instead of retrieving the value from cache  
     * @param  callable|null $postProcessCachedValueCallback: callback to execute on values 
     *            retrieved from the cache before returning them. Useful to hydrate documents etc.
     */
    public function get(
        string $documentName,
        QueryCriteria $queryCriteria,
        int $queryType,
        bool $useCachedValue,
        callable $queryCallback,
        ?callable $postProcessCachedValueCallback = null
    ): mixed {
        if (!$useCachedValue) {
            return $queryCallback();
        }

        //Execute query / cache result / get result from cache
        $result = $this->queryCache->get(
            $this->computeCacheKey($documentName, $queryCriteria, $queryType),
            function (ItemInterface $item) use ($queryCallback, $documentName): mixed {
                $item->tag([md5($documentName)]);
                return $queryCallback();
            }
        );

        //Post-process cached result if callback is defined.
        //E.g.: hydrate documents
        if (isset($postProcessCachedValueCallback)) {
            return $postProcessCachedValueCallback($result);
        }

        return $result;
    }

    private function computeCacheKey(
        string $documentName,
        QueryCriteria $queryCriteria,
        int $queryType
    ): string {
        $serializedQueryCriteria = serialize($queryCriteria);
        $documentNameAndSerializedQueryCriteria = sprintf('%s|%s', $documentName, $serializedQueryCriteria);
        return sprintf('%s|%s', $queryType, md5($documentNameAndSerializedQueryCriteria));
    }

    /**
     * Invalidate documents of the given type/class
     */
    public function invalidate(string $documentName): void
    {
        $this->queryCache->invalidateTags([md5($documentName)]);
    }

    /**
     * Schedule documents of the given type/class for invalidation.
     * The invalidation happens when the Cache::invalidateCache() method is called
     */
    public function scheduleForInvalidation(string $documentName): void
    {
        $this->documentsScheduledForInvalidation[] = $documentName;
    }

    /**
     * Invalidate all the documents which were scheduled for invalidation
     * with the method Cache::scheduleForInvalidation().
     */
    public function invalidateCache(): void
    {
        if (count($this->documentsScheduledForInvalidation) > 0) {
            $this->queryCache->invalidateTags(
                array_map(
                    fn (string $documentName) => md5($documentName),
                    array_unique($this->documentsScheduledForInvalidation)
                )
            );
        }

        $this->documentsScheduledForInvalidation = array();
    }
}
