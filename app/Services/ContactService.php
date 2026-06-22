<?php

namespace App\Services;

use App\Models\Contact;
use Illuminate\Support\Facades\Log;

class ContactService
{
    public function __construct(private AIService $aiService) {}

    public function process(array $data): array
    {
        // Анализируем комментарий с помощью AI (или fallback)
        $analysis = $this->aiService->analyze($data['comment']);

        Contact::create([
            'name'      => $data['name'],
            'phone'     => $data['phone'],
            'email'     => $data['email'],
            'comment'   => $data['comment'],
            'sentiment' => $analysis['sentiment'] ?? null,
            'category'  => $analysis['category'] ?? null,
        ]);

        $adminEmail = config('app.admin_email', 'owner@example.com');
        Log::channel('contact')->info("Email to owner ($adminEmail): Новое обращение от {$data['name']}");
        Log::channel('contact')->info("Email to user ({$data['email']}): Ваше обращение получено.");

        return $analysis;
    }
}