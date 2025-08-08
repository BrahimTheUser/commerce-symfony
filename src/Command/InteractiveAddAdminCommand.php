<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:active-add-admin',
    description: 'Adds a new admin user interactively',
)]
class InteractiveAddAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');


        $firstNameQuestion = new Question('Please enter the admin firstName: ');
        $firstNameQuestion->setValidator(function ($firstName) {
            if (empty($firstName)) {
                throw new \RuntimeException('firstName cannot be empty.');
            }
            return $firstName;
        });
        $firstName = $helper->ask($input, $output, $firstNameQuestion);

        $lastNameQuestion = new Question('Please enter the admin lastName: ');
        $lastNameQuestion->setValidator(function ($lastName) {
            if (empty($lastName)) {
                throw new \RuntimeException('lastName cannot be empty.');
            }
            return $lastName;
        });
        $lastName = $helper->ask($input, $output, $lastNameQuestion);

        // Email validation
        $emailQuestion = new Question('Please enter the admin email: ');
        $emailQuestion->setValidator(function ($email) {
            if (empty($email)) {
                throw new \RuntimeException('Email cannot be empty.');
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new \RuntimeException('Invalid email format.');
            }

            return $email;
        });
        $email = $helper->ask($input, $output, $emailQuestion);

        // Password validation
        $passwordQuestion = new Question('Please enter the admin password: ');
        $passwordQuestion->setHidden(true)->setHiddenFallback(false);
        $passwordQuestion->setValidator(function ($password) {
            if (empty($password)) {
                throw new \RuntimeException('Password cannot be empty.');
            }

            return $password;
        });
        $password = $helper->ask($input, $output, $passwordQuestion);

        // Create and persist user
        $user = new User();

        $user->setFirstName($firstName);
        $user->setLastName($lastName);

        $user->setEmail($email);
        $user->setRoles(['ROLE_ADMIN']);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln('Admin user successfully created!');

        return Command::SUCCESS;
    }
}
