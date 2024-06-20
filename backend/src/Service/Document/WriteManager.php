<?php

declare(strict_types=1);

namespace App\Service\Document;

use App\Service\Document\CrudHelper;

final class WriteManager
{

    public function __construct(private CrudHelper $crudHelper)
    {
    }

    /**
     * Apply all the changes to the documents in the unit of work to storage.
     */
    public function flush(array $options = [], bool $useTransaction = false): void
    {
        $this->crudHelper->flush($options, $useTransaction);
    }

    /**
     * Clear the unit of work and all the caches, buffers and identity maps
     */
    public function clear(): void
    {
        $this->crudHelper->clear();
    }

    /**
     * Sets the value of forceUseTransaction to true
     * which forces the next call to flush() to be executed 
     * within a multi-document transaction
     */
    public function forceUseTransaction(): void
    {
        $this->crudHelper->forceUseTransaction();
    }

    /**
     * Sets the value of forceUseTransaction to false
     */
    public function resetUseTransaction(): void
    {
        $this->crudHelper->resetUseTransaction();
    }
}
