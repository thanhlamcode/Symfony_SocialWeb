<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\FriendList;

class FriendListRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FriendList::class);
    }

    /**
     * Lấy danh sách user có thể kết bạn, trừ bản thân và những người đã gửi/nhận lời mời kết bạn
     */
    public function findAvailableUsers(string $currentUserId): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
        SELECT \"u\".\"id\", \"u\".\"email\"
        FROM \"user\" AS \"u\"
        WHERE \"u\".\"id\" != :currentUserId
        AND \"u\".\"id\" NOT IN (
            SELECT \"f\".\"receiver_id\" FROM \"friend_list\" AS \"f\" WHERE \"f\".\"sender_id\" = :currentUserId
            UNION
            SELECT \"f\".\"sender_id\" FROM \"friend_list\" AS \"f\" WHERE \"f\".\"receiver_id\" = :currentUserId
        )
    ";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue('currentUserId', (string) $currentUserId, \PDO::PARAM_STR); // Ép kiểu string
        $stmt->execute();

        return $stmt->executeQuery()->fetchAllAssociative();
    }
}
