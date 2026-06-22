<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;

class AIService
{
    private bool $enabled;

    public function __construct()
    {
        $this->enabled = !empty(config('openai.api_key'));
    }

    public function analyze(string $comment): array
    {
        if ($this->enabled) {
            try {
                $result = OpenAI::chat()->create([
                    'model' => config('openai.model', 'gpt-3.5-turbo'),
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Ты анализируешь обращения. Верни только JSON: {"sentiment":"positive/negative/neutral","category":"general/partnership/bug/feature/complaint"}'
                        ],
                        ['role' => 'user', 'content' => $comment],
                    ],
                    'temperature' => 0,
                    'max_tokens' => 50,
                ]);

                $content = $result->choices[0]->message->content;
                $analysis = json_decode($content, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    Log::info('OpenAI analysis successful', $analysis);
                    return $analysis;
                }

                Log::warning('OpenAI response not valid JSON, using fallback');
            } catch (\Exception $e) {
                Log::error('OpenAI error: ' . $e->getMessage());
            }
        }

        // Если ключа нет или произошла ошибка используем fallback
        return $this->fallbackAnalysis($comment);
    }

    private function fallbackAnalysis(string $comment): array
    {
        $lower = mb_strtolower($comment);

        $positive = ['отлично', 'спасибо', 'хорошо', 'круто', 'благодарю', 'супер'];

        $negative = ['плохо', 'ужасно', 'не работает', 'ошибка', 'проблема'];

        $sentiment = 'neutral';

        foreach ($positive as $word) {
            if (str_contains($lower, $word)) {
                $sentiment = 'positive';
                break;
            }
        }
        if ($sentiment === 'neutral') {
            foreach ($negative as $word) {
                if (str_contains($lower, $word)) {
                    $sentiment = 'negative';
                    break;
                }
            }
        }

        $category = 'general';
        if (str_contains($lower, 'партн') || str_contains($lower, 'сотруднич')) {
            $category = 'partnership';
        } elseif (str_contains($lower, 'баг') || str_contains($lower, 'ошибк')) {
            $category = 'bug';
        } elseif (str_contains($lower, 'фич') || str_contains($lower, 'предложен') || str_contains($lower, 'идея')) {
            $category = 'feature';
        } elseif (str_contains($lower, 'жалоб') || str_contains($lower, 'недовол')) {
            $category = 'complaint';
        }

        return ['sentiment' => $sentiment, 'category' => $category];
    }
}
