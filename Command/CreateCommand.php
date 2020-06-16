<?php

namespace EncreInformatique\UserApiBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class CreateCommand extends Command
{
    protected static $defaultName = "users:create";

    /** @var EntityManagerInterface $entityManager */
    protected $entityManager;

    /** @var JWTEncoderInterface $passwordEncoder */
    protected $passwordEncoder;

    /** @var array $entitiesClass */
    protected $entitiesClass;

    /**
     * ChangePasswordCommand constructor.
     * @param EntityManagerInterface $entityManager
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $encoder, $entitiesClass)
    {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $encoder;
        $this->entitiesClass = $entitiesClass;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription("Create a user")
            ->addArgument('username', InputArgument::REQUIRED, 'Username of the user');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $userName = $input->getArgument('username');
        $user = $this->entityManager->getRepository($this->entitiesClass['user'])->findOneBy(['username' => $userName]);

        if ($user) {
            $output->writeln(sprintf('The user %s already exists', $userName));
            return 0;
        }

        $helper = $this->getHelper('question');
        $question = new Question('Please enter the password:');
        $question->setHidden(true);
        $question->setHiddenFallback(false);
        if (!$password = $helper->ask($input, $output, $question)) {
            return 0;
        }

        $question = new Question('Please confirm the password:');
        $question->setHidden(true);
        $question->setHiddenFallback(false);
        if (!$password2 = $helper->ask($input, $output, $question)) {
            return 0;
        }

        if ($password !== $password2) {
            $output->writeln('The password does not match.');
            return 0;
        }

        /** @var \EncreInformatique\UserApiBundle\Model\User $user */
        $user = new $this->entitiesClass['user']($userName);

        $encodedPassword = $this->passwordEncoder->encodePassword($user, $password);
        $user->setPassword($encodedPassword);

        /*
         * Do we need an email ?
         */
        $reflection = new \ReflectionClass($user);
        try {
            $property = $reflection->getProperty('email');

            $question = new Question('Please specify the email:');

            if (!$email = $helper->ask($input, $output, $question)) {
                $output->writeln('No email has been specify.');
                return 0;
            }

            $user->setEmail($email);
        } catch (\ReflectionException $exception) {
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln('The user has been created.');

        return 0;
    }
}
