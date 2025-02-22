<?php

namespace App\Controller;

use App\Service\User\UserServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

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
    public function updateProfile(Request $request, CsrfTokenManagerInterface $csrfTokenManager): RedirectResponse
    {
        // ✅ Lấy dữ liệu từ form
        $data = $request->request->all();

        // ✅ Kiểm tra CSRF Token
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('update-profile', $data['_csrf_token'] ?? ''))) {
            $this->addFlash('error', 'CSRF token không hợp lệ!');
            return $this->redirectToRoute('profile');
        }

        // ✅ Cập nhật profile
        $isUpdated = $this->userService->updateCurrentUserProfile($data);

        if (!$isUpdated) {
            $this->addFlash('error', 'Cập nhật hồ sơ thất bại.');
        } else {
            $this->addFlash('success', 'Cập nhật hồ sơ thành công.');
        }

        return $this->redirectToRoute('profile');
    }

}