<?php

use App\Message\ChatMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Message;

class ChatMessageHandler implements MessageHandlerInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(ChatMessage $chatMessage)
    {
        $message = new Message();
        $message->setContent($chatMessage->getContent());
        $message->setSenderId($chatMessage->getSenderId());
        $message->setReceiverId($chatMessage->getReceiverId());
        $message->setCreatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($message);
        $this->entityManager->flush();
    }
}