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

//        dump($profile); exit;

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

    #[Route('/user/profile/update-bio', name: 'update_bio', methods: ['POST'])]
    public function updateBio(Request $request, CsrfTokenManagerInterface $csrfTokenManager): RedirectResponse
    {
        // ✅ Lấy dữ liệu từ form
        $bio = $request->request->get('bio');
        $csrfToken = $request->request->get('_csrf_token');

        // ✅ Kiểm tra CSRF Token
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('update-bio', $csrfToken))) {
            $this->addFlash('error', 'CSRF token không hợp lệ!');
            return $this->redirectToRoute('profile');
        }

        // ✅ Chỉ cập nhật trường "bio"
        $isUpdated = $this->userService->updateCurrentUserProfile(['bio' => $bio]);

        if (!$isUpdated) {
            $this->addFlash('error', 'Cập nhật bio thất bại.');
        } else {
            $this->addFlash('success', 'Cập nhật bio thành công.');
        }

        return $this->redirectToRoute('profile');
    }

    #[Route('/user/profile/update-interests', name: 'update_interests', methods: ['POST'])]
    public function updateInterests(Request $request, CsrfTokenManagerInterface $csrfTokenManager): RedirectResponse
    {
        // ✅ Lấy dữ liệu từ form
        $csrfToken = $request->request->get('_csrf_token');
        $interests = explode(',', $request->request->get('interests', ''));

        // ✅ Kiểm tra CSRF Token
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('update-interests', $csrfToken))) {
            $this->addFlash('error', 'CSRF token không hợp lệ!');
            return $this->redirectToRoute('profile');
        }

        // ✅ Cập nhật sở thích của user
        $isUpdated = $this->userService->updateCurrentUserProfile(['interests' => $interests]);

        if (!$isUpdated) {
            $this->addFlash('error', 'Cập nhật sở thích thất bại.');
        } else {
            $this->addFlash('success', 'Cập nhật sở thích thành công.');
        }

        return $this->redirectToRoute('profile');
    }

    #[Route('/user/profile/update-social-accounts', name: 'update_social_accounts', methods: ['POST'])]
    public function updateSocialAccounts(Request $request, CsrfTokenManagerInterface $csrfTokenManager): RedirectResponse
    {
        // ✅ Lấy dữ liệu từ form
        $csrfToken = $request->request->get('_csrf_token');
        $socialAccountsJson = $request->request->get('social_accounts', '[]');

        // ✅ Giải mã JSON
        $socialAccounts = json_decode($socialAccountsJson, true);


        // ✅ Kiểm tra CSRF Token
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('update-social-accounts', $csrfToken))) {
            $this->addFlash('error', 'CSRF token không hợp lệ!');
            return $this->redirectToRoute('profile');
        }

        // ✅ Chỉ cho phép 3 tài khoản mạng xã hội hợp lệ
        $allowedAccounts = [
            'github' => 'icons/github.png',
            'linkedin' => 'icons/linkdn.png',
            'instagram' => 'icons/instagram.png'
        ];

        $filteredAccounts = [];

        foreach ($allowedAccounts as $key => $icon) {
            if (!empty($socialAccounts) && is_array($socialAccounts)) {
                foreach ($socialAccounts as $acc) {
                    if (isset($acc['url']) && isset($acc['icon']) && $acc['icon'] === $icon) {
                        $filteredAccounts[] = [
                            'url' => $acc['url'],
                            'icon' => $icon
                        ];
                    }
                }
            }
        }

        // ✅ Cập nhật tài khoản mạng xã hội của profile
        $isUpdated = $this->userService->updateCurrentUserProfile(['socialAccounts' => $filteredAccounts]);

        if (!$isUpdated) {
            $this->addFlash('error', 'Cập nhật tài khoản mạng xã hội thất bại.');
        } else {
            $this->addFlash('success', 'Cập nhật tài khoản mạng xã hội thành công.');
        }

        return $this->redirectToRoute('profile');
    }
}