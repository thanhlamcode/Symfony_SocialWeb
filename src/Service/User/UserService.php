<?php

namespace App\Service\User;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Repository\ProfileRepository;
use App\Entity\Profile;
use App\Entity\User;

class UserService implements UserServiceInterface
{
    private TokenStorageInterface $tokenStorage;
    private ProfileRepository $profileRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(TokenStorageInterface $tokenStorage, ProfileRepository $profileRepository, EntityManagerInterface $entityManager)
    {
        $this->tokenStorage = $tokenStorage;
        $this->profileRepository = $profileRepository;
        $this->entityManager = $entityManager;
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
            'avatar' => $profile?->getAvatar() ?? $defaultAvatar,
            'bio' => $profile?->getBio() ?? null,
            'interests' => $profile?->getInterests() ?? [],
            'banner' => $profile?->getBanner() ?? null,
            'socialAccounts' => $profile?->getSocialAccounts() ?? [],
            'slug' => $profile?->getSlug() ?? null,
            'job' => $profile?->getJob() ?? null,
        ];
    }

    public function updateCurrentUserProfile(array $data): bool
    {
        $user = $this->getCurrentUser();

        if (!$user) {
            return false; // Không tìm thấy User
        }

        // Tìm Profile theo User ID
        $profile = $this->profileRepository->findByUserId($user->getId());

        // Nếu không tìm thấy Profile, tạo mới và gán User ID
        if (!$profile) {
            $profile = new Profile();
            $profile->setUserId($user->getId());
        }

        // Cập nhật dữ liệu từ $data
        if (!empty($data['avatar'])) {
            $profile->setAvatar($data['avatar']);
        }
        if (!empty($data['banner'])) {
            $profile->setBanner($data['banner']);
        }
        if (!empty($data['slug'])) {
            $profile->setSlug($data['slug']);
        }
//        } else {
//            $profile->setSlug(strtolower(str_replace(' ', '-', $profile->getName() ?? 'user-' . $user->getId())));
//        }
        if (!empty($data['name'])) {
            $profile->setName($data['name']);
        }
        if (!empty($data['phone'])) {
            $profile->setPhone($data['phone']);
        }
        if (!empty($data['job'])) {
            $profile->setJob($data['job']);
        }
        if (!empty($data['bio'])) {
            $profile->setBio($data['bio']);
        }
        if (!empty($data['interests'])) {
            $profile->setInterests($data['interests']);
        }
        if (!empty($data['socialAccounts'])) {
            $profile->setSocialAccounts($data['socialAccounts']);
        }

        // Lưu lại Profile vào database
        $this->profileRepository->save($profile);

        return true; // Cập nhật hoặc tạo mới thành công
    }

    /**
     * Lấy Profile theo slug
     */
    public function getProfileBySlug(string $slug): ?array
    {
        $profile = $this->profileRepository->findBySlug($slug);

        if (!$profile) {
            return null;
        }

        // Tìm User theo userId có trong Profile
        $user = $this->entityManager->getRepository(User::class)->find($profile->getUserId());

        return $this->mapProfileToArray($profile, $user);
    }

    /**
     * Chuyển đổi Profile Entity thành mảng dữ liệu
     */
    private function mapProfileToArray(mixed $profile, ?User $user = null): ?array
    {
        if (!$profile) {
            return null;
        }

        // Nếu profile là mảng, lấy phần tử đầu tiên (Profile object)
        if (is_array($profile) && isset($profile[0]) && $profile[0] instanceof Profile) {
            $profile = $profile[0];
        }

        $defaultAvatar = "https://st4.depositphotos.com/14903220/22197/v/450/depositphotos_221970610-stock-illustration-abstract-sign-avatar-icon-profile.jpg";

        return [
            'id' => $profile->getId() ?? null,
            'userId' => $user?->getId(),
            'name' => $profile->getName() ?? $user?->getEmail(), // Nếu Name null, dùng Email
            'email' => $user?->getEmail() ?? null, // Bổ sung email từ User
            'phone' => $profile->getPhone() ?? null,
            'avatar' => $profile->getAvatar() ?? $defaultAvatar,
            'bio' => $profile->getBio() ?? null,
            'interests' => $profile->getInterests() ?? [],
            'banner' => $profile->getBanner() ?? null,
            'socialAccounts' => $profile->getSocialAccounts() ?? [],
            'slug' => $profile->getSlug() ?? null,
            'job' => $profile->getJob() ?? null,
        ];
    }

}
