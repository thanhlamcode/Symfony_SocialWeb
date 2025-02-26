<?php

namespace App\Service;

use App\Repository\FriendListRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class FriendService
{
    private FriendListRepository $friendListRepository;
    private TokenStorageInterface $tokenStorage;

    public function __construct(FriendListRepository $friendListRepository, TokenStorageInterface $tokenStorage)
    {
        $this->friendListRepository = $friendListRepository;
        $this->tokenStorage = $tokenStorage;
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
}
