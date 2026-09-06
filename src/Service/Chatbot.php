<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class Chatbot
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $ollamaBaseUrl = 'http://localhost:11434',
        private string $ollamaModel = 'llama3.2',
    ) {}

    public function extractFilters(string $userMessage, array $availableCategories = []): array
    {
        $categoryList = implode(', ', $availableCategories);

        $systemPrompt = <<<PROMPT
        You are a filter-extraction assistant for a clothing store.
        Given a customer's message, return ONLY a JSON object with these optional keys:
        category (string — MUST be exactly one of: {$categoryList},and if not but same meaning return the ones from this list or omitted if none fit ),
        color (string),
        size (string, e.g. "S", "M", "L", "XL" ,"OneSize"),
        maxPrice (number),
        keywords (short string of relevant descriptive words, for matching against product name/description).
        .Return {} if nothing is clear.
        PROMPT;

        $response = $this->httpClient->request('POST', $this->ollamaBaseUrl . '/api/chat', [
            'json' => [
                'model' => $this->ollamaModel,
                'stream' => false,
                'format' => 'json',
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userMessage],
                ],
            ],
        ]);

        $data = $response->toArray();
        $text = $data['message']['content'] ?? '{}';

        $filters = json_decode($text, true);
        return is_array($filters) ? $filters : [];
    }


    public function formatReply(string $userMessage, array $products): string
    {
        if (empty($products)) {
            return "I couldn't find anything matching that. Could you tell me a bit more about what you're looking for?";
        }

        $productSummaries = array_map(
            fn($p) => sprintf(
                "%s (%s) - $%s: %s",
                $p->getName(),
                $p->getCategory()?->getName(),
                $p->getPrice(),
                $p->getDescription()
            ),
            $products
        );

        $prompt = "Customer asked: " . $userMessage . " Matching products: " . implode(', ', $productSummaries) . "Write reply (2 sentences) recommending these items.";
        $response = $this->httpClient->request('POST', $this->ollamaBaseUrl . '/api/chat', [
            'json' => [
                'model' => $this->ollamaModel,
                'stream' => false,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ],
        ]);

        $data = $response->toArray();
        return $data['message']['content'] ?? 'Here are some options for you!';
    }
}
