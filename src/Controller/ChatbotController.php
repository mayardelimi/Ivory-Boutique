<?php
namespace App\Controller;

use App\Repository\ProductRepository;
use App\Service\Chatbot;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ChatbotController extends AbstractController
{
    #[Route('/api/chat', name: 'api_chat', methods: ['POST'])]
    public function chat(Request $request, Chatbot $chatbot, ProductRepository $productRepository,):
    JsonResponse {
        $content = json_decode($request->getContent(), true);
        $usermessage = trim($content['message'] ?? '');
        $availableCat = $productRepository->getDistinctCategoryNames();
        $filters = $chatbot->extractFilters($usermessage, $availableCat);
        $products = $productRepository->findByFilters($filters);
        $reply = $chatbot->formatReply($usermessage, $products);

        return $this->json([
            'reply' => $reply,
            'products' => array_map(fn($p) => [
                'id' => $p->getId(),
                'name' => $p->getName(),
                'slug' => $p->getSlug(),
                'price' => $p->getPrice(),
                'priceWithTax' => $p->getPriceWt(),
                'category' => $p->getCategory()?->getName(),
                'imageUrl' => $p->getImages()->first() ? $p->getImages()->first()->getImagePath() : null,
            ], $products),
        ]);
    }
}
