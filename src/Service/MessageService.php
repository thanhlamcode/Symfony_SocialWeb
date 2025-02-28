<?php

namespace App\Service;

use App\Repository\MessageRepository;
use App\Repository\ProfileRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Message;

class MessageService
{
    private MessageRepository $messageRepository;
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;

    private ProfileRepository $profileRepository;

    public function __construct(MessageRepository $messageRepository, UserRepository $userRepository,
                                EntityManagerInterface $entityManager, ProfileRepository $profileRepository)
    {
        $this->messageRepository = $messageRepository;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->profileRepository = $profileRepository;
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

    /**
     * Lấy danh sách cuộc trò chuyện gần đây
     */
    public function getRecentChats(int $userId): array
    {
        $messages = $this->messageRepository->findRecentChats($userId);
        $chatList = [];

        foreach ($messages as $message) {
            $chatPartnerId = ($message->getSenderId() === $userId) ? $message->getReceiverId() : $message->getSenderId();
            $chatPartner = $this->userRepository->find($chatPartnerId);

            if (!$chatPartner) {
                continue; // Bỏ qua nếu không tìm thấy user
            }

            // Tìm Profile của người bạn chat
            $profile = $this->profileRepository->findOneBy(['userId' => $chatPartner->getId()]);

            $chatList[] = [
                'id' => $chatPartner->getId(),
                'name' => $profile?->getName() ?? $chatPartner->getEmail(), // Ưu tiên tên từ profile, nếu không có thì lấy email
                'avatar' => $profile?->getAvatar() ?? 'https://st4.depositphotos.com/14903220/22197/v/450/depositphotos_221970610-stock-illustration-abstract-sign-avatar-icon-profile.jpg', // Lấy avatar từ profile nếu có
                'last_message' => $message->getContent() ?: 'Chưa có tin nhắn',
                'time' => $message->getSentAt() ? $message->getSentAt()->format('H:i') : '',
            ];
        }

        return $chatList;
    }


    public function getLastMessage(int $currentUserId, int $receiverId): ?array
    {
        $lastMessage = $this->messageRepository->findLastMessageBetweenUsers($currentUserId, $receiverId);

        if (!$lastMessage) {
            return null;
        }

        return [
            'id' => $lastMessage->getId(),
            'senderId' => $lastMessage->getSenderId(),
            'receiverId' => $lastMessage->getReceiverId(),
            'content' => $lastMessage->getContent(),
            'sentAt' => $lastMessage->getSentAt()->format('Y-m-d H:i:s'),
            'isRead' => $lastMessage->isRead(),
        ];
    }

}
