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
            ->setParameters([
                'user1' => $userId1,
                'user2' => $userId2,
            ])
            ->orderBy('m.sentAt', 'ASC') // Sắp xếp theo thời gian gửi
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
}
