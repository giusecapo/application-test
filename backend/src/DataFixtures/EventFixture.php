<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Document\Event;
use App\Document\Speech;
use \DateTime as DateTime;
use Doctrine\Bundle\MongoDBBundle\Fixture\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\MongoDBBundle\Fixture\ODMFixtureInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Faker\Generator;
use Faker\Factory as FakerFactory;

final class EventFixture extends Fixture implements ODMFixtureInterface
{
    private Generator $faker;

    public function __construct()
    {
        $this->faker = FakerFactory::create();
        $this->faker->seed(1234);
    }


    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 100; $i++) {
            $event = new Event();
            $event
                ->setKey($this->faker->uuid())
                ->setName($this->faker->word())
                ->setParticipants($this->generateRandomParticipants())
                ->setDate(new DateTime(sprintf('now +%d days', rand(5, 100))))
                ->setProgram($this->generateRandomProgram());

            $manager->persist($event);
        }

        $manager->flush();
    }

    /**
     * @return string[]
     */
    private function generateRandomParticipants(): array
    {
        $participants = [];

        $length = rand(5, 10);
        for ($i = 0; $i < $length; $i++) {
            $participants[] = sprintf('%s %s', $this->faker->firstName(), $this->faker->lastName());
        }

        return $participants;
    }

    private function generateRandomProgram(): Collection
    {
        $program = new ArrayCollection();

        $length = rand(1, 5);
        for ($i = 0; $i < $length; $i++) {
            $program->add($this->generateRandomSpeech());
        }

        return $program;
    }

    private function generateRandomSpeech(): Speech
    {
        $startTime = rand(420, 1080);
        $endTime = rand($startTime + 60, $startTime + 120);

        $speech = new Speech();
        $speech
            ->setTopic($this->faker->sentence())
            ->setSpeaker(sprintf('%s %s', $this->faker->firstName(), $this->faker->lastName()))
            ->setStartTime($startTime)
            ->setEndTime($endTime);

        return $speech;
    }
}
