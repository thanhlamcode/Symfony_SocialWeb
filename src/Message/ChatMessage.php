<?php

namespace App\Message;

class ChatMessage
{
    private string $content;
    private int $senderId;
    private int $receiverId;

    public function __construct(string $content, int $senderId, int $receiverId)
    {
        $this->content = $content;
        $this->senderId = $senderId;
        $this->receiverId = $receiverId;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getSenderId(): int
    {
        return $this->senderId;
    }

    public function getReceiverId(): int
    {
        return $this->receiverId;
    }
}