<?php

declare(strict_types=1);

namespace App\Command;

use DateTime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Question\Question;

final class DbBootstrapDevCommand extends Command
{

    protected static $defaultName = 'app:db:bootstrap-dev';


    public function __construct(private string $env)
    {
        parent::__construct();
    }


    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setDescription(
                'Creates the DB schema and populates it with a set of data which 
                can be used for automatic testing and development. 
                If the schema already exists, it will be dropped.'
            );
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $confirmed = false;

        if ($this->env === 'dev' || $this->env === 'test' || !$input->isInteractive()) {
            $confirmed = true;
        } else {
            $helper = $this->getHelper('question');
            $confirmQuestion = new Question('app:db:bootstrap-dev: type the phrase \'bootstrap dev\' to confirm, or press enter without typing anything to abort:  ', null);
            $confirmAnswer = $helper->ask($input, $output, $confirmQuestion);
            $confirmed = $confirmAnswer === 'bootstrap dev';
        }

        if (!$confirmed) {
            $io->success('Operation aborted.');
            return 0;
        }

        $executionStartTime = new DateTime();

        $this->getApplication()->find('doctrine:mongodb:schema:drop')->run(new ArrayInput([]), $output);
        $io->success('Schema dropped.');

        $this->getApplication()->find('doctrine:mongodb:schema:create')->run(new ArrayInput([]), $output);
        $io->success('Schema created.');

        $this->getApplication()->find('cache:pool:clear')->run(new ArrayInput(['pools' => ['query.cache']]), $output);
        $io->success('Query cache cleared.');

        $this->getApplication()->find('doctrine:mongodb:fixtures:load')->run(new ArrayInput(['--append' => true]), $output);
        $io->success('Fixtures loaded.');

        $io->success('The DB was bootstrapped for the dev environment.');
        $io->text(sprintf(
            'The operation took %s hours, %s minutes, %s seconds',
            (new DateTime())->diff($executionStartTime)->h,
            (new DateTime())->diff($executionStartTime)->i,
            (new DateTime())->diff($executionStartTime)->s,
        ));
        return 0;
    }
}
