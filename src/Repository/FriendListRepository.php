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
            SELECT 
                u.id, 
                u.email, 
                p.avatar, 
                p.name, 
                p.slug,
                CASE 
                    WHEN EXISTS (
                        SELECT 1 
                        FROM friend_list AS f 
                        WHERE f.sender_id = :currentUserId 
                        AND f.receiver_id = u.id 
                        AND f.status = 'pending'
                    ) THEN 'requested'
                    ELSE 'not_requested'
                END AS request_status
            FROM \"user\" AS u
            LEFT JOIN profile AS p ON u.id = p.user_id
            WHERE u.id != :currentUserId
            AND u.id NOT IN (
                SELECT f.receiver_id FROM friend_list AS f WHERE f.sender_id = :currentUserId AND f.status = 'accepted'
                UNION
                SELECT f.sender_id FROM friend_list AS f WHERE f.receiver_id = :currentUserId AND f.status = 'accepted'
            )
        ";


        $stmt = $conn->prepare($sql);
        $stmt->bindValue('currentUserId', (string) $currentUserId, \PDO::PARAM_STR); // Ép kiểu string
        $stmt->execute();

        return $stmt->executeQuery()->fetchAllAssociative();
    }

    public function findFriendRequests(string $currentUserId): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
        SELECT 
            u.id, 
            u.email, 
            COALESCE(p.avatar, 'https://st4.depositphotos.com/14903220/22197/v/450/depositphotos_221970610-stock-illustration-abstract-sign-avatar-icon-profile.jpg') AS avatar, 
            COALESCE(p.name, u.email) AS name, 
            COALESCE(p.slug, 'Người dùng chưa tạo slug') AS slug
        FROM \"friend_list\" AS f
        INNER JOIN \"user\" AS u ON f.sender_id = u.id
        LEFT JOIN \"profile\" AS p ON u.id = p.user_id
        WHERE f.receiver_id = :currentUserId
        AND f.status = 'pending'
    ";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue('currentUserId', (string) $currentUserId, \PDO::PARAM_STR); // Ép kiểu string
        $stmt->execute();

        return $stmt->executeQuery()->fetchAllAssociative();
    }

    /**
     * Kiểm tra xem đã có lời mời kết bạn chưa
     */
    public function isFriendRequestSent(int $senderId, int $receiverId): bool
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
        SELECT COUNT(*) as count 
        FROM friend_list 
        WHERE sender_id = :senderId 
        AND receiver_id = :receiverId 
        AND status = 'pending'
    ";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue('senderId', (string) $senderId, \PDO::PARAM_STR);
        $stmt->bindValue('receiverId', (string) $receiverId, \PDO::PARAM_STR);
        $result = $stmt->executeQuery()->fetchOne(); // Sử dụng executeQuery() trước khi fetchOne()

        return (int) $result > 0;
    }


    /**
     * Gửi lời mời kết bạn
     */
    public function sendFriendRequest(int $senderId, int $receiverId): bool
    {
        if ($this->isFriendRequestSent($senderId, $receiverId)) {
            return false; // Đã gửi lời mời trước đó
        }

        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            INSERT INTO friend_list (sender_id, receiver_id, status, created_at) 
            VALUES (:senderId, :receiverId, 'pending', NOW())
        ";

        $stmt = $conn->prepare($sql);
        $stmt->executeStatement(['senderId' => $senderId, 'receiverId' => $receiverId]);

        return true;
    }

    /**
     * Hủy lời mời kết bạn
     */
    public function cancelFriendRequest(int $senderId, int $receiverId): bool
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            DELETE FROM friend_list 
            WHERE sender_id = :senderId AND receiver_id = :receiverId AND status = 'pending'
        ";

        $stmt = $conn->prepare($sql);
        $stmt->executeStatement(['senderId' => $senderId, 'receiverId' => $receiverId]);

        return true;
    }
}
