<?php

namespace EncreInformatique\UserApiBundle\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ChangePasswordCommand extends Command
{
    protected static $defaultName = "users:profile:change-password";

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
            ->setDescription("Change the password of a user")
            ->addArgument('username', InputArgument::REQUIRED, 'Username of the user');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $userName = $input->getArgument('username');
        $user = $this->entityManager->getRepository($this->entitiesClass['user'])->findOneBy(['username' => $userName]);

        if (!$user) {
            $output->writeln(sprintf('No user found for %s', $userName));
            return;
        }

        $helper = $this->getHelper('question');
        $question = new Question('Please enter the new password:');
        $question->setHidden(true);
        $question->setHiddenFallback(false);
        if (!$password = $helper->ask($input, $output, $question)) {
            return;
        }

        $question = new Question('Please confirm the new password:');
        $question->setHidden(true);
        $question->setHiddenFallback(false);
        if (!$password2 = $helper->ask($input, $output, $question)) {
            return;
        }

        if ($password !== $password2) {
            $output->writeln('The password does not match.');
            return;
        }

        $encodedPassword = $this->passwordEncoder->encodePassword($user, $password);
        $user->setPassword($encodedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln('The password has been saved.');
    }
}
