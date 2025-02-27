<?php

namespace App\Service;

use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Message;

class MessageService
{
    private MessageRepository $messageRepository;
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(MessageRepository $messageRepository, UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        $this->messageRepository = $messageRepository;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * Lấy danh sách tin nhắn giữa hai người dùng và hiển thị với tên + email
     */
    public function getChatHistory(int $userId1, int $userId2): array
    {
        $messages = $this->messageRepository->findMessagesBetweenUsers($userId1, $userId2);

        // Lấy thông tin người dùng
        $users = $this->userRepository->findBy(['id' => [$userId1, $userId2]]);
        $userMap = [];
        foreach ($users as $user) {
            $userMap[$user->getId()] = [
                'email' => $user->getEmail(),
            ];
        }

        // Chuẩn bị dữ liệu hiển thị
        $chatHistory = [];
        foreach ($messages as $message) {
            $chatHistory[] = [
                'id' => $message->getId(),
                'senderId' => $message->getSenderId(),
                'senderEmail' => $userMap[$message->getSenderId()]['email'] ?? 'Người gửi không xác định',
                'receiverId' => $message->getReceiverId(),
                'receiverEmail' => $userMap[$message->getReceiverId()]['email'] ?? 'Người nhận không xác định',
                'content' => $message->getContent(),
                'sentAt' => $message->getSentAt()->format('Y-m-d H:i:s'),
                'isRead' => $message->isRead(),
            ];
        }

        return $chatHistory;
    }

    /**
     * Gửi tin nhắn mới
     */
    public function sendMessage(int $senderId, int $receiverId, string $content): Message
    {
        $message = new Message();
        $message->setSenderId($senderId);
        $message->setReceiverId($receiverId);
        $message->setContent($content);
        $message->setSentAt(new \DateTime());

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        return $message;
    }

    /**
     * Lấy tin nhắn chưa đọc của người nhận
     */
    public function getUnreadMessages(int $receiverId): array
    {
        return $this->messageRepository->findUnreadMessages($receiverId);
    }

    /**
     * Đánh dấu tin nhắn là đã đọc
     */
    public function markMessagesAsRead(int $receiverId): void
    {
        $this->messageRepository->markMessagesAsRead($receiverId);
    }

    public function getRecentChats(int $userId): array
    {
        $messages = $this->messageRepository->findRecentChats($userId);

        $chatList = [];
        foreach ($messages as $message) {
            $chatPartnerId = ($message->getSenderId() === $userId) ? $message->getReceiverId() : $message->getSenderId();
            $chatPartner = $this->userRepository->find($chatPartnerId);

            $chatList[] = [
                'id' => $chatPartner?->getId(),
                'name' => $chatPartner?->getEmail() ?? 'Ẩn danh',
                'avatar' => '/images/avatar.png',
                'last_message' => $message->getContent(),
                'time' => $message->getSentAt()->format('H:i'),
            ];
        }

        return $chatList;
    }

}
