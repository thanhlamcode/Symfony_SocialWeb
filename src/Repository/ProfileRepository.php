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
        $entityManager = $this->getEntityManager();
        $entityManager->persist($profile);

        if ($flush) {
            $entityManager->flush();
        }
    }

    /**
     * Cập nhật Profile với dữ liệu mới
     */
    public function update(Profile $profile, array $data): Profile
    {
        if (!empty($data['avatar'])) {
            $profile->setAvatar($data['avatar']);
        }
        if (!empty($data['banner'])) {
            $profile->setBanner($data['banner']);
        }
        if (!empty($data['slug'])) {
            $profile->setSlug($data['slug']);
        }
        if (!empty($data['name'])) {
            $profile->setName($data['name']);
        }
        if (!empty($data['phone'])) {
            $profile->setPhone($data['phone']);
        }
        if (!empty($data['job'])) {
            $profile->setRole($data['job']); // Đúng trường dữ liệu
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

        $this->_em->flush();

        return $profile;
    }
}
