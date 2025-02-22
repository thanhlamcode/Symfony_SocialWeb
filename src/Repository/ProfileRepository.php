<?php

namespace App\Repository;

use App\Entity\Profile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Profile::class);
    }

    /**
     * Tìm Profile theo User ID
     */
    public function findByUserId(int $userId): ?Profile
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.userId = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Lưu hoặc cập nhật Profile vào database
     */
    public function save(Profile $profile, bool $flush = true): void
    {
        $this->_em->persist($profile);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * Cập nhật Profile với dữ liệu mới
     */
    public function update(Profile $profile, array $data): Profile
    {
        // Chỉ cập nhật nếu dữ liệu mới tồn tại
        if (array_key_exists('avatar', $data)) {
            $profile->setAvatar($data['avatar']);
        }
        if (array_key_exists('banner', $data)) {
            $profile->setBanner($data['banner']);
        }
        if (array_key_exists('slug', $data)) {
            $profile->setSlug($data['slug']);
        }
        if (array_key_exists('name', $data)) {
            $profile->setName($data['name']);
        }
        if (array_key_exists('phone', $data)) {
            $profile->setPhone($data['phone']);
        }
        if (array_key_exists('role', $data)) {
            $profile->setRole($data['role']);
        }
        if (array_key_exists('bio', $data)) {
            $profile->setBio($data['bio']);
        }
        if (array_key_exists('interests', $data)) {
            $profile->setInterests($data['interests']);
        }
        if (array_key_exists('socialAccounts', $data)) {
            $profile->setSocialAccounts($data['socialAccounts']);
        }

        // Lưu thay đổi vào database
        $this->_em->flush();

        return $profile;
    }
}
