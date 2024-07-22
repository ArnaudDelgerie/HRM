<?php

namespace App\Command;

use App\Entity\User;
use App\Enum\UserRoleEnum;
use App\Enum\UserStateEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:create:admin',
    description: 'Create admin user',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface      $em,
        private ValidatorInterface          $validator,
        private UserPasswordHasherInterface $userPasswordHasher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('New admin user');

        $user = (new User())
            ->setState(UserStateEnum::Enabled->value)
            ->setRoles([UserRoleEnum::Admin->value]);

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $question = new Question('Email : ');
        $user->setEmail($helper->ask($input, $output, $question));
        
        $question = new Question('Username : ');
        $user->setUsername($helper->ask($input, $output, $question));
        
        $question = new Question('Firstname : ');
        $user->setFirstname($helper->ask($input, $output, $question));
        
        $question = new Question('Lastname : ');
        $user->setLastname($helper->ask($input, $output, $question));
        
        $question = new Question('Password : ');
        $question->setHidden(true)->setHiddenFallback(false);
        $hashedPassword = $this->userPasswordHasher->hashPassword($user, $helper->ask($input, $output, $question));
        $user->setPassword($hashedPassword);

        $errors = $this->validator->validate($user);
        if(count($errors) > 0) {
            $io->error((string) $errors);
            
            return Command::FAILURE;
        }

        $this->em->persist($user);
        $this->em->flush();

        $io->success('Admin user created');

        return Command::SUCCESS;
    }
}
