<?php

namespace App\Service\Auth;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Client\Provider\Google;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class GoogleAuthService implements AuthServiceInterface
{
    private Google $googleProvider;
    private EntityManagerInterface $entityManager;

    public function __construct(Google $googleProvider, EntityManagerInterface $entityManager)
    {
        $this->googleProvider = $googleProvider;
        $this->entityManager = $entityManager;
    }

    public function authenticate(Request $request): ?UserInterface
    {
        $token = $request->query->get('code');
        if (!$token) {
            return null;
        }

        $googleUser = $this->googleProvider->getResourceOwner($this->googleProvider->getAccessToken('authorization_code', [
            'code' => $token
        ]));

        $email = $googleUser->getEmail();
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            $user = new User();
            $user->setEmail($email);
            $user->setRoles(['ROLE_USER']);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        return $user;
    }
}