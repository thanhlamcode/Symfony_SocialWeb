<?php

namespace App\Repository;

use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    /**
     * Lấy danh sách tin nhắn giữa hai người dùng (sắp xếp theo thời gian)
     */
    public function findMessagesBetweenUsers(int $userId1, int $userId2): array
    {
        return $this->createQueryBuilder('m')
            ->where('(m.senderId = :user1 AND m.receiverId = :user2)')
            ->orWhere('(m.senderId = :user2 AND m.receiverId = :user1)')
            ->setParameter('user1', $userId1)
            ->setParameter('user2', $userId2)
            ->orderBy('m.sentAt', 'ASC') // Lấy theo thời gian gửi tăng dần
            ->getQuery()
            ->getResult();
    }

    /**
     * Lấy tin nhắn chưa đọc của người nhận
     */
    public function findUnreadMessages(int $receiverId): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.receiverId = :receiverId')
            ->andWhere('m.isRead = false')
            ->setParameter('receiverId', $receiverId)
            ->orderBy('m.sentAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Đánh dấu tin nhắn là đã đọc
     */
    public function markMessagesAsRead(int $receiverId): void
    {
        $this->createQueryBuilder('m')
            ->update(Message::class, 'm')
            ->set('m.isRead', ':read')
            ->where('m.receiverId = :receiverId')
            ->setParameters([
                'read' => true,
                'receiverId' => $receiverId,
            ])
            ->getQuery()
            ->execute();
    }

    public function findRecentChats(int $userId): array
    {
        return $this->createQueryBuilder('m')
            ->select('m') // Lấy toàn bộ thông tin tin nhắn
            ->where('m.senderId = :userId OR m.receiverId = :userId')
            ->setParameter('userId', $userId)
            ->andWhere('m.sentAt = (
            SELECT MAX(m2.sentAt) 
            FROM App\Entity\Message m2
            WHERE (m2.senderId = m.senderId AND m2.receiverId = m.receiverId) 
               OR (m2.senderId = m.receiverId AND m2.receiverId = m.senderId)
        )')
            ->orderBy('m.sentAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Lấy tin nhắn cuối cùng giữa hai người dùng
     */
    public function findLastMessageBetweenUsers(int $userId1, int $userId2): ?Message
    {
        return $this->createQueryBuilder('m')
            ->where('(m.senderId = :user1 AND m.receiverId = :user2)')
            ->orWhere('(m.senderId = :user2 AND m.receiverId = :user1)')
            ->setParameter('user1', $userId1)
            ->setParameter('user2', $userId2)
            ->orderBy('m.sentAt', 'DESC') // Lấy tin nhắn mới nhất
            ->setMaxResults(1) // Chỉ lấy 1 tin nhắn cuối cùng
            ->getQuery()
            ->getOneOrNullResult(); // Trả về 1 kết quả hoặc null nếu không có tin nhắn
    }
}
