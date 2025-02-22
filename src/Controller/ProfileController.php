<?php

namespace App\Controller;

use App\Service\User\UserServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ProfileController extends AbstractController
{
    private UserServiceInterface $userService;

    public function __construct(UserServiceInterface $userService){
        $this->userService = $userService;
    }

    #[Route('/dashboard/profile', name: 'profile')]
    public function profile(): Response
    {

        $profile = $this->userService->getCurrentUserProfile();

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
                ['url' => 'https://twitter.com/ShaltOni', 'icon' => 'icons/github.png'],
                ['url' => 'https://instagram.com/shaltoni', 'icon' => 'icons/linkdn.png'],
                ['url' => 'https://www.linkedin.com/in/aymanshaltoni/', 'icon' => 'icons/instagram.png']
            ],
            'slug' => 'aymanshaltoni'
        ];

        return $this->render('profile.html.twig', [
            'user' => $user,
            'profile' => $profile
        ]);
    }

    #[Route('/user/profile/update', name: 'update_profile', methods: ['POST'])]
    public function updateProfile(Request $request): RedirectResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            $this->addFlash('error', 'Dữ liệu không hợp lệ.');
            return $this->redirectToRoute('profile');
        }

        $profile = $this->userService->updateCurrentUserProfile($data);

        if (!$profile) {
            $this->addFlash('error', 'Không tìm thấy Profile hoặc User chưa đăng nhập.');
            return $this->redirectToRoute('profile');
        }

        $this->addFlash('success', 'Cập nhật hồ sơ thành công.');
        return $this->redirectToRoute('profile');
    }
}