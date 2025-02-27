<?php

namespace App\Service;

use App\Entity\FriendList;
use App\Repository\FriendListRepository;
use App\Service\User\UserService;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class FriendService
{
    private FriendListRepository $friendListRepository;
    private TokenStorageInterface $tokenStorage;

    private UserService $userService;

    public function __construct(FriendListRepository $friendListRepository, TokenStorageInterface $tokenStorage, UserService $userService)
    {
        $this->friendListRepository = $friendListRepository;
        $this->tokenStorage = $tokenStorage;
        $this->userService = $userService;
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

        $friendIds = $this->friendListRepository->findAcceptedFriends($user->getId());

        dump($friendIds);

        $friendList = [];

        foreach ($friendIds as $friendData) {
            if (!isset($friendData['friendId'])) {
                continue;
            }

            $friendId = $friendData['friendId'];
            $friendInfo = $this->userService->getProfileBySlug($friendId); // Hoặc dùng findById($friendId)

            if ($friendInfo) {
                $friendList[] = [
                    'id' => $friendInfo['userId'],
                    'name' => $friendInfo['name'],
                    'avatar' => $friendInfo['avatar'],
                    'slug' => $friendInfo['slug'],
                ];
            }
        }

//        dump($friendList);

        return $friendList;
    }
}
