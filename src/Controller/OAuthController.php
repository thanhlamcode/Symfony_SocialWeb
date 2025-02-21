<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use App\Security\AppAuthenticator;

class OAuthController extends AbstractController
{
    #[Route('/connect/google', name: 'connect_google_start')]
    public function connectGoogle(ClientRegistry $clientRegistry)
    {
        return $clientRegistry->getClient('google')->redirect(['email', 'profile']);
    }

    #[Route('/google/callback', name: 'google_check')]
    public function googleCallback(
        Request $request,
        ClientRegistry $clientRegistry,
        EntityManagerInterface $entityManager,
        UserAuthenticatorInterface $userAuthenticator,
        AppAuthenticator $appAuthenticator
    ): Response {
        $client = $clientRegistry->getClient('google');

        try {
            $googleUser = $client->fetchUser();
            $email = $googleUser->getEmail();

            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

            if (!$user) {
                $user = new User();
                $user->setEmail($email);
                $user->setRoles(['ROLE_USER']);

                $entityManager->persist($user);
                $entityManager->flush();
            }

            // Đăng nhập người dùng bằng UserAuthenticatorInterface
            return $userAuthenticator->authenticateUser(
                $user,
                $appAuthenticator,
                $request
            );

        } catch (\Exception $e) {
            $this->addFlash('error', 'Lỗi đăng nhập với Google: ' . $e->getMessage());
            return $this->redirectToRoute('app_login');
        }
    }
}