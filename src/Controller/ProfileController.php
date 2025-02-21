<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    #[Route('/dashboard/profile', name: 'profile')]
    public function profile(): Response
    {
        // Dữ liệu user (giả lập)
        $user = [
            'avatar' => '/images/avatar1.png',
            'name' => 'Ayman Shaltoni',
            'email' => 'Aymanshaltoni@gmail.com',
            'phone' => '+966 5502938123',
            'role' => 'Senior Product Designer',
            'bio' => "Hey, I’m a product designer specialized in user interface designs (Web & Mobile) with 10 years of experience...",
            'interests' => ['UI Design', 'Framer', 'Startups', 'UX', 'Crypto', 'Mobile Apps', 'Webflow'],
            'banner' => '/images/banner.png',
            'social_accounts' => [
                ['url' => 'https://twitter.com/ShaltOni', 'icon' => 'icons/twitter.svg'],
                ['url' => 'https://instagram.com/shaltoni', 'icon' => 'icons/instagram.svg'],
                ['url' => 'https://www.linkedin.com/in/aymanshaltoni/', 'icon' => 'icons/linkedin.svg']
            ]
        ];

        return $this->render('profile.html.twig', [
            'user' => $user
        ]);
    }
}