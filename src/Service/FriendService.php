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

        return $this->friendListRepository->findAvailableUsers((string) $user->getId());
    }
}
