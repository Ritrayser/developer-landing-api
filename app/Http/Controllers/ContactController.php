<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Services\ContactService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    public function __construct(private ContactService $contactService) {}

    public function submit(ContactRequest $request): JsonResponse
    {
        try {

            $data = $request->validated();

            $result = $this->contactService->process($data);

            Log::channel('contact')->info('Contact processed', $result);

            return response()->json([
                'status'    => 'success',
                'message'   => 'Сообщение успешно отправлено',
                'sentiment' => $result['sentiment'] ?? null,
                'category'  => $result['category'] ?? null,
            ], 200);
        } catch (\Exception $e) {
            Log::channel('contact')->error('Contact error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Внутренняя ошибка сервера',
            ], 500);
        }
    }
}