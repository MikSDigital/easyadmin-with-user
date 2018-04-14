<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ListUsersCommand extends Command
{
    protected static $defaultName = 'app:list-users';

    private $users;

    /**
     * ListUsersCommand constructor.
     * @param $users
     */
    public function __construct(UserRepository $users)
    {
        parent::__construct();
        $this->users = $users;
    }


    protected function configure()
    {
        $this
            ->setDescription('Lists all the existing users');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $allUsers = $this->users->findAll();

        // Doctrine query returns an array of objects and we need an array of plain arrays
        $usersAsPlainArrays = array_map(function (User $user) {
            return [
                $user->getEmail(),
                implode(', ', $user->getRoles()),
            ];
        }, $allUsers);

        $io = new SymfonyStyle($input, $output);

        $io->table(
            ['Email', 'Roles'],
            $usersAsPlainArrays
        );

        $io->success('Users listed');
    }
}
