<?php

namespace App\Service\User;

use App\Entity\User;

interface UserServiceInterface
{
    /**
     * Lấy thông tin người dùng hiện tại
     */
    public function getCurrentUser(): ?User;
}
