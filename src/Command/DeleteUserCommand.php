<?php

namespace App\Command;

use App\Repository\UserRepository;
use App\Utils\Validator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DeleteUserCommand extends Command
{
    protected static $defaultName = 'app:delete-user';

    private $io;
    private $entityManager;
    private $validator;
    private $users;

    /**
     * DeleteUserCommand constructor.
     * @param $entityManager
     * @param $validator
     * @param $users
     */
    public function __construct(EntityManagerInterface $entityManager, Validator $validator, UserRepository $users)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->users = $users;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Deletes users from the database')
            ->addArgument('email', InputArgument::REQUIRED, 'The email of an existing user')
            ->setHelp(<<<'HELP'
The <info>%command.name%</info> command deletes users from the database:

  <info>php %command.full_name%</info> <comment>email</comment>

If you omit the argument, the command will ask you to
provide the missing value:

  <info>php %command.full_name%</info>
HELP
            );
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        // SymfonyStyle is an optional feature that Symfony provides so you can
        // apply a consistent look to the commands of your application.
        // See https://symfony.com/doc/current/console/style.html
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (null !== $input->getArgument('email')) {
            return;
        }

        $this->io->title('Delete User Command Interactive Wizard');
        $this->io->text([
            'If you prefer to not use this interactive wizard, provide the',
            'arguments required by this command as follows:',
            '',
            ' $ php bin/console app:delete-user email',
            '',
            'Now we\'ll ask you for the value of all the missing command arguments.',
            '',
        ]);

        $email = $this->io->ask('email', null, [$this->validator, 'validateEmail']);
        $input->setArgument('email', $email);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $email = $this->validator->validateEmail($input->getArgument('email'));

        /** @var User $user */
        $user = $this->users->findOneBy(['email' => $email]);

        if (null === $user) {
            throw new RuntimeException(sprintf('User with username "%s" not found.', $email));
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        $this->io->success(sprintf('User "%s" (email: %s) was successfully deleted.', $user->getUsername(), $user->getEmail()));
    }
}
