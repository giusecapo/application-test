<?php

declare(strict_types=1);

namespace App\Contract\DataFixture;

interface EmbeddedDocumentFixtureInterface
{

    /**
     * Returns an array of fixtures class names the class depends on.
     */
    public function getDependencies(): array;

    /**
     * Return an array of documents in the required quantity.
     */
    public function load(int $numberOfDocuments): array;
}
