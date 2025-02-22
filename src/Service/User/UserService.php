<?php

namespace App\Service\User;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Repository\ProfileRepository;
use App\Entity\Profile;
use App\Entity\User;

class UserService implements UserServiceInterface
{
    private TokenStorageInterface $tokenStorage;
    private ProfileRepository $profileRepository;

    public function __construct(TokenStorageInterface $tokenStorage, ProfileRepository $profileRepository)
    {
        $this->tokenStorage = $tokenStorage;
        $this->profileRepository = $profileRepository;
    }

    /**
     * Lấy User hiện tại từ Security Token Storage
     */
    public function getCurrentUser(): ?User
    {
        $token = $this->tokenStorage->getToken();
        $user = $token?->getUser();

        return ($user instanceof User) ? $user : null;
    }

    /**
     * Lấy Profile của User hiện tại
     */
    public function getCurrentUserProfile(): ?array
    {
        $user = $this->getCurrentUser();

        if (!$user) {
            return null;
        }

        $profile = $this->profileRepository->findByUserId($user->getId());

        // Ảnh mặc định
        $defaultAvatar = "https://st4.depositphotos.com/14903220/22197/v/450/depositphotos_221970610-stock-illustration-abstract-sign-avatar-icon-profile.jpg";

        return [
            'id' => $profile?->getId() ?? null,
            'userId' => $user->getId(),
            'name' => $profile?->getName() ?? $user->getEmail(),
            'email' => $user->getEmail(),  // ✅ Thêm email vào đây
            'phone' => $profile?->getPhone() ?? null,
            'role' => $profile?->getRole() ?? ($user->getRoles()[0] ?? 'ROLE_USER'),
            'avatar' => $profile?->getAvatar() ?? $defaultAvatar,
            'bio' => $profile?->getBio() ?? null,
            'interests' => $profile?->getInterests() ?? [],
            'banner' => $profile?->getBanner() ?? null,
            'socialAccounts' => $profile?->getSocialAccounts() ?? [],
            'slug' => $profile?->getSlug() ?? null,
            'job' => $profile?->getJob() ?? null,
        ];
    }

    public function updateCurrentUserProfile(array $data): ?Profile
    {
        $user = $this->getCurrentUser();

        if (!$user) {
            return null;
        }

        $profile = $this->profileRepository->findByUserId($user->getId());

        if (!$profile) {
            return null;
        }

        return $this->profileRepository->update($profile, $data);
    }
}
