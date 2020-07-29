<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationController extends AbstractFOSRestController
{
    private $userRepository;
    private $passwordEncoder;
    private $entityManager;

    public function __construct(UserRepository $userRepository, UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $entityManager)
    {
        $this->userRepository = $userRepository;
        $this->passwordEncoder = $passwordEncoder;
        $this->entityManager = $entityManager;
    }

    /**
     * @Rest\Post("/api/register", name="register")
     * @Rest\RequestParam(name="username", nullable=false)
     * @Rest\RequestParam(name="password", nullable=false)
     * @Rest\RequestParam(name="confirm_password", nullable=false)
     */
    public function index(ParamFetcher $paramFetcher)
    {
        $username = $paramFetcher->get('username');
        $password = $paramFetcher->get('password');
        $confirmPassword = $paramFetcher->get('confirm_password');

        $user = $this->userRepository->findOneBy([
            'username' => $username
        ]);

        if ($password != $confirmPassword) {
            return $this->view([
                'code' => 400,
                'message' => 'Passwords do not match'
            ], 400);
        }

        if (!is_null($user)) {
            return $this->view([
                'code' => 409,
                'message' => 'User already exists'
            ], 409);
        }

        $user = new User();

        $user->setUsername($username);
        $user->setPassword(
            $this->passwordEncoder->encodePassword($user, $password)
        );

        $this->entityManager->persist($user);
        $this->entityManager->flush();


        return $this->view([
            'id' => $user->getId(),
            'username' => $user->getUsername()
        ], 201);
    }
}
