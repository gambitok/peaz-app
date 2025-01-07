<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Jobs\ConvertVideo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class ConvertVideoTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_convert_video_job()
    {
        // Підготовка файлів і шляхів
        $inputFileName = 'test_input.mp4';
        $outputExtension = 'mp4';
        $outputFileName = 'test_output.mp4';
        $inputFullPath = storage_path('app/uploads/tmp/' . $inputFileName);

        // Створення фіктивного вхідного файлу
        Storage::fake('local');
        Storage::disk('local')->put('uploads/tmp/' . $inputFileName, 'Fake video content');

        // Переконайтеся, що файл існує
        $this->assertTrue(Storage::disk('local')->exists('uploads/tmp/' . $inputFileName));

        // Виконання роботи
        $job = new ConvertVideo($inputFullPath, $outputExtension, $outputFileName);
        $job->handle();

        // Перевірка, що вихідний файл створений
        $outputFullPath = 'uploads/tmp/' . $outputFileName;
        $this->assertTrue(Storage::disk('local')->exists($outputFullPath));

        // Перевірка логів
        Log::shouldReceive('info')
            ->with('Video conversion completed successfully', [
                'input' => $inputFullPath,
                'output' => storage_path('app/uploads/tmp/' . $outputFileName),
            ]);

        // Видалення файлів після тесту
        Storage::disk('local')->delete('uploads/tmp/' . $inputFileName);
        Storage::disk('local')->delete($outputFullPath);
    }
}
