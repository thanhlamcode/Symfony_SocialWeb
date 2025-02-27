<?php

namespace App\Service;

use App\Entity\FriendList;
use App\Repository\FriendListRepository;
use App\Repository\ProfileRepository;
use App\Service\User\UserService;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class FriendService
{
    private FriendListRepository $friendListRepository;
    private TokenStorageInterface $tokenStorage;

    private UserService $userService;

    private ProfileRepository $profileRepository;

    public function __construct(FriendListRepository $friendListRepository, TokenStorageInterface $tokenStorage, UserService $userService, profileRepository $profileRepository)
    {
        $this->friendListRepository = $friendListRepository;
        $this->tokenStorage = $tokenStorage;
        $this->userService = $userService;
        $this->profileRepository = $profileRepository;
    }

    /**
     * Lấy danh sách user có thể kết bạn
     */
    public function getAvailableUsers(): array
    {
        $token = $this->tokenStorage->getToken();
        $user = $token?->getUser();

        if (!$user || !($user instanceof \App\Entity\User)) {
            return [];
        }

        $users = $this->friendListRepository->findAvailableUsers((string) $user->getId());

        // Mặc định avatar nếu bị null
        $defaultAvatar = "https://st4.depositphotos.com/14903220/22197/v/450/depositphotos_221970610-stock-illustration-abstract-sign-avatar-icon-profile.jpg";

        // Xử lý dữ liệu để thay thế giá trị null
        foreach ($users as &$userData) {
            $userData['avatar'] = $userData['avatar'] ?? $defaultAvatar;
            $userData['name'] = $userData['name'] ?? $userData['email']; // Nếu name null, lấy email làm name
        }

        return $users;
    }

    public function getFriendRequests(): array
    {
        $token = $this->tokenStorage->getToken();
        $user = $token?->getUser();

        if (!$user || !($user instanceof \App\Entity\User)) {
            return [];
        }

        $users = $this->friendListRepository->findFriendRequests((string) $user->getId());

        // Mặc định avatar nếu bị null
        $defaultAvatar = "https://st4.depositphotos.com/14903220/22197/v/450/depositphotos_221970610-stock-illustration-abstract-sign-avatar-icon-profile.jpg";

        // Xử lý dữ liệu để thay thế giá trị null
        foreach ($users as &$userData) {
            $userData['avatar'] = $userData['avatar'] ?? $defaultAvatar;
            $userData['name'] = $userData['name'] ?? $userData['email']; // Nếu name null, lấy email làm name
            $userData['slug'] = $userData['slug'] ?? "Người dùng chưa tạo slug"; // Nếu slug null
        }

        return $users;
    }

    public function isFriendRequestSent(int $receiverId): bool
    {
        $token = $this->tokenStorage->getToken();
        $user = $token?->getUser();

        if (!$user || !($user instanceof \App\Entity\User)) {
            return false;
        }

        return $this->friendListRepository->isFriendRequestSent($user->getId(), $receiverId);
    }


    /**
     * Gửi lời mời kết bạn
     */
    public function sendFriendRequest(int $receiverId): bool
    {
        $token = $this->tokenStorage->getToken();
        $user = $token?->getUser();

        if (!$user || !($user instanceof \App\Entity\User)) {
            return false;
        }

        return $this->friendListRepository->sendFriendRequest($user->getId(), $receiverId);
    }

    /**
     * Hủy lời mời kết bạn
     */
    public function cancelFriendRequest(int $receiverId): bool
    {
        $token = $this->tokenStorage->getToken();
        $user = $token?->getUser();

        if (!$user || !($user instanceof \App\Entity\User)) {
            return false;
        }

        return $this->friendListRepository->cancelFriendRequest($user->getId(), $receiverId);
    }

    /**
     * Chấp nhận lời mời kết bạn.
     */
    public function acceptFriendRequest(int $senderId): void
    {
        $token = $this->tokenStorage->getToken();
        $user = $token?->getUser();
        if (!$user || !($user instanceof \App\Entity\User)) {
            return;
        }

        $this->friendListRepository->acceptFriendRequest($senderId, $user->getId());
    }

    /**
     * Từ chối lời mời kết bạn.
     */
    public function declineFriendRequest(int $senderId): void
    {
        $token = $this->tokenStorage->getToken();
        $user = $token?->getUser();
        if (!$user || !($user instanceof \App\Entity\User)) {
            return;
        }

        $this->friendListRepository->declineFriendRequest($senderId, $user->getId());
    }
    /**
     * Lấy danh sách bạn bè đã được chấp nhận
     */
    public function getAcceptedFriends(): array
    {
        $user = $this->userService->getCurrentUser();
        if (!$user) {
            return [];
        }

        // Lấy danh sách bạn bè từ friend_list
        $friendIds = $this->friendListRepository->findAcceptedFriends($user->getId());

        // Chuyển danh sách từ [['friendId' => 2], ['friendId' => 7]] thành [2, 7]
        $friendIdArray = array_map(fn($item) => $item['friendId'], $friendIds);

        // Lấy thông tin profile của tất cả bạn bè bằng 1 query duy nhất
        $profiles = $this->profileRepository->findProfilesByUserIds($friendIdArray);

        // Chuyển profile thành dạng [userId => profile]
        $profileMap = [];
        foreach ($profiles as $profile) {
            $profileMap[$profile['userId']] = $profile;
        }

        $friendList = [];

        // Duyệt qua danh sách bạn bè ban đầu để đảm bảo đủ số lượng
        foreach ($friendIdArray as $friendId) {
            $profile = $profileMap[$friendId] ?? null; // Kiểm tra nếu profile tồn tại

            $friendList[] = [
                'id' => $friendId,
                'name' => $profile['name'] ?? "Người dùng ẩn danh",
                'avatar' => $profile['avatar'] ?? "https://default-avatar-url.com/avatar.jpg",
                'slug' => $profile['slug'] ?? null,
            ];
        }

        return $friendList;
    }
}
