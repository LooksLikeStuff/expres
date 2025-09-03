<?php

namespace App\Console\Commands;

use App\Models\BriefAnswer;
use App\Models\Common;
use App\Models\Commercial;
use App\Services\BriefQuestionKeyService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigrateQuestionKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'brief:migrate-question-keys {--dry-run : Показать что будет изменено без сохранения}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Миграция ключей вопросов в брифах и ответах';

    private BriefQuestionKeyService $keyService;

    public function __construct(BriefQuestionKeyService $keyService)
    {
        parent::__construct();
        $this->keyService = $keyService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('🔍 Режим проверки - изменения НЕ будут сохранены');
        } else {
            $this->info('🚀 Начинаем миграцию ключей вопросов...');
        }

        $stats = [
            'brief_answers' => 0,
            'common_answers' => 0,
            'commercial_preferences' => 0,
            'errors' => 0
        ];

        try {
            // Миграция BriefAnswer
            $stats['brief_answers'] = $this->migrateBriefAnswers($dryRun);
            
            // Миграция Common answers
            $stats['common_answers'] = $this->migrateCommonAnswers($dryRun);
            
            // Миграция Commercial preferences
            $stats['commercial_preferences'] = $this->migrateCommercialPreferences($dryRun);

        } catch (\Exception $e) {
            $this->error('Ошибка при миграции: ' . $e->getMessage());
            Log::error('Ошибка миграции ключей вопросов', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $stats['errors']++;
        }

        // Показываем статистику
        $this->displayStats($stats, $dryRun);

        return $stats['errors'] > 0 ? 1 : 0;
    }

    /**
     * Миграция ключей в BriefAnswer
     */
    private function migrateBriefAnswers(bool $dryRun): int
    {
        $this->info('📝 Проверяем BriefAnswer...');
        $updated = 0;

        $briefAnswers = BriefAnswer::with('brief')->get();
        
        foreach ($briefAnswers as $answer) {
            if (!$answer->brief) {
                $this->warn("BriefAnswer {$answer->id}: бриф не найден");
                continue;
            }

            $oldKey = $answer->question_key;
            $newKey = $this->keyService->getActualQuestionKey($oldKey, $answer->brief->type);

            if ($oldKey !== $newKey) {
                $this->line("BriefAnswer {$answer->id}: {$oldKey} → {$newKey}");
                
                if (!$dryRun) {
                    $answer->question_key = $newKey;
                    $answer->save();
                }
                $updated++;
            }
        }

        return $updated;
    }

    /**
     * Миграция ключей в Common answers
     */
    private function migrateCommonAnswers(bool $dryRun): int
    {
        $this->info('📋 Проверяем Common answers...');
        $updated = 0;

        $commons = Common::whereNotNull('answers')->get();
        
        foreach ($commons as $common) {
            $answers = json_decode($common->answers, true);
            if (!is_array($answers)) {
                continue;
            }

            $updatedAnswers = $this->keyService->updateCommonAnswersKeys($answers);
            
            if ($updatedAnswers !== $answers) {
                $changes = [];
                foreach ($answers as $key => $value) {
                    $newKey = array_search($value, $updatedAnswers);
                    if ($newKey !== $key && $newKey !== false) {
                        $changes[] = "{$key} → {$newKey}";
                    }
                }
                
                $this->line("Common {$common->id}: " . implode(', ', $changes));
                
                if (!$dryRun) {
                    $common->answers = json_encode($updatedAnswers);
                    $common->save();
                }
                $updated++;
            }
        }

        return $updated;
    }

    /**
     * Миграция ключей в Commercial preferences
     */
    private function migrateCommercialPreferences(bool $dryRun): int
    {
        $this->info('🏢 Проверяем Commercial preferences...');
        $updated = 0;

        $commercials = Commercial::whereNotNull('preferences')->get();
        
        foreach ($commercials as $commercial) {
            $preferences = json_decode($commercial->preferences, true);
            if (!is_array($preferences)) {
                continue;
            }

            $updatedPreferences = $this->keyService->updateCommercialPreferencesKeys($preferences);
            
            if ($updatedPreferences !== $preferences) {
                $changes = [];
                foreach ($preferences as $key => $value) {
                    $newKey = array_search($value, $updatedPreferences);
                    if ($newKey !== $key && $newKey !== false) {
                        $changes[] = "{$key} → {$newKey}";
                    }
                }
                
                $this->line("Commercial {$commercial->id}: " . implode(', ', $changes));
                
                if (!$dryRun) {
                    $commercial->preferences = json_encode($updatedPreferences);
                    $commercial->save();
                }
                $updated++;
            }
        }

        return $updated;
    }

    /**
     * Показать статистику миграции
     */
    private function displayStats(array $stats, bool $dryRun): void
    {
        $this->newLine();
        $this->info('📊 Статистика миграции:');
        $this->table(
            ['Тип', 'Обновлено'],
            [
                ['BriefAnswer', $stats['brief_answers']],
                ['Common answers', $stats['common_answers']],
                ['Commercial preferences', $stats['commercial_preferences']],
                ['Ошибки', $stats['errors']],
            ]
        );

        $total = $stats['brief_answers'] + $stats['common_answers'] + $stats['commercial_preferences'];
        
        if ($dryRun) {
            $this->info("🔍 Будет обновлено: {$total} записей");
            $this->comment('Для применения изменений запустите команду без --dry-run');
        } else {
            $this->info("✅ Обновлено: {$total} записей");
        }

        if ($stats['errors'] > 0) {
            $this->error("❌ Ошибок: {$stats['errors']}");
        }
    }
}
