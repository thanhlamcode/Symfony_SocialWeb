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


        return $this->render('profile.html.twig', [
            'profile' => $profile
        ]);
    }

    #[Route('/user/profile/update', name: 'update_profile', methods: ['POST'])]
    public function updateProfileField(Request $request, CsrfTokenManagerInterface $csrfTokenManager): RedirectResponse
    {
        // ✅ Lấy dữ liệu từ request
        $data = $request->request->all();
        $csrfToken = $data['_csrf_token'] ?? '';

        // ✅ Kiểm tra CSRF Token
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('update-profile', $csrfToken))) {
            $this->addFlash('error', 'CSRF token không hợp lệ!');
            return $this->redirectToRoute('profile');
        }

        // ✅ Xác định trường cần cập nhật
        $updateFields = [];

        if (!empty($data['bio'])) {
            $updateFields['bio'] = $data['bio'];
        }

        if (!empty($data['interests'])) {
            $updateFields['interests'] = explode(',', $data['interests']);
        }

        if (!empty($data['social_accounts'])) {
            $socialAccountsJson = $data['social_accounts'];
            $socialAccounts = json_decode($socialAccountsJson, true);

            // ✅ Lọc chỉ các tài khoản hợp lệ
            $allowedAccounts = [
                'github' => 'icons/github.png',
                'linkedin' => 'icons/linkdn.png',
                'instagram' => 'icons/instagram.png'
            ];
            $filteredAccounts = [];

            foreach ($allowedAccounts as $key => $icon) {
                foreach ($socialAccounts as $acc) {
                    if (isset($acc['url'], $acc['icon']) && $acc['icon'] === $icon) {
                        $filteredAccounts[] = [
                            'url' => $acc['url'],
                            'icon' => $icon
                        ];
                    }
                }
            }

            $updateFields['socialAccounts'] = $filteredAccounts;
        }

        // ✅ Các trường khác như avatar, banner, job, name, slug
        foreach (['avatar', 'banner', 'slug', 'name', 'phone', 'job'] as $field) {
            if (!empty($data[$field])) {
                $updateFields[$field] = $data[$field];
            }
        }

        // ✅ Gửi dữ liệu cập nhật đến service
        $isUpdated = $this->userService->updateCurrentUserProfile($updateFields);

        if (!$isUpdated) {
            $this->addFlash('error', 'Cập nhật hồ sơ thất bại.');
        } else {
            $this->addFlash('success', 'Cập nhật hồ sơ thành công.');
        }

        return $this->redirectToRoute('profile');
    }

    #[Route('/dashboard/profile/{slug}', name: 'profile_by_slug', methods: ['GET'])]
    public function viewProfileBySlug(string $slug, UserServiceInterface $userService): Response
    {
        $profileDetail = $userService->getProfileBySlug($slug);
        $profile = $userService->getCurrentUserProfile();

        if (!$profileDetail) {
            throw $this->createNotFoundException('Profile not found');
        }

        return $this->render('porfolio.html.twig', [
            'profileDetail' => $profileDetail,
            'profile' => $profile
        ]);
    }

}