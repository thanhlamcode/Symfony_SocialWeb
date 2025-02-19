<?php

namespace App\Controller;

use App\Message\ChatMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Messenger\MessageBusInterface;

class ChatController extends AbstractController
{
    #[Route('/chat/send', name: 'chat_send', methods: ['POST'])]
    public function send(Request $request, MessageBusInterface $bus): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $message = new ChatMessage($data['content'], $this->getUser()->getId(), $data['receiver_id']);
        $bus->dispatch($message);

        return $this->json(['status' => 'Message sent']);
    }
}