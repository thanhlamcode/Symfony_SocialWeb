<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CallController extends AbstractController
{
    #[Route('/call', name: 'call_page', methods: ['GET'])]
    public function call(Request $request): Response
    {
        $senderId = $request->query->get('senderId');
        $receiverId = $request->query->get('receiverId');

        if (!$senderId || !$receiverId) {
            return new Response("Missing parameters", Response::HTTP_BAD_REQUEST);
        }

        return $this->render('call.html.twig', [
            'senderId' => $senderId,
            'receiverId' => $receiverId,
            'websocket_url' => $this->getParameter('websocket_url')
        ]);
    }
}
