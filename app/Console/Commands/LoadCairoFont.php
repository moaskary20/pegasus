<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Dompdf\Dompdf;
use Dompdf\Options;

class LoadCairoFont extends Command
{
    protected $signature = 'certificate:load-font';
    protected $description = 'Load Cairo font for PDF certificates';

    public function handle()
    {
        $fontPath = storage_path('fonts/Cairo-Regular.ttf');
        
        if (!file_exists($fontPath)) {
            $this->error('Cairo font file not found at: ' . $fontPath);
            $this->info('Downloading Cairo font...');
            
            // Try to download the font
            $fontUrl = 'https://github.com/google/fonts/raw/main/ofl/cairo/static/Cairo-Regular.ttf';
            $fontDir = dirname($fontPath);
            if (!is_dir($fontDir)) {
                mkdir($fontDir, 0755, true);
            }
            
            $fontContent = @file_get_contents($fontUrl);
            if ($fontContent) {
                file_put_contents($fontPath, $fontContent);
                $this->info('Font downloaded successfully!');
            } else {
                $this->error('Failed to download font. Please download Cairo-Regular.ttf manually to: ' . $fontPath);
                return 1;
            }
        }

        $options = new Options();
        $options->set('fontDir', storage_path('fonts'));
        $options->set('fontCache', storage_path('fonts'));
        
        $dompdf = new Dompdf($options);
        $fontMetrics = $dompdf->getFontMetrics();
        
        try {
            // Register the font
            $fontMetrics->registerFont([
                'family' => 'Cairo',
                'style' => 'normal',
                'weight' => 'normal'
            ], $fontPath);
            
            $this->info('Cairo font registered successfully!');
            $this->info('Font path: ' . $fontPath);
            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to register font: ' . $e->getMessage());
            $this->warn('Note: dompdf may still work with DejaVu Sans which has better Unicode support');
            return 1;
        }
    }
}
