<?php

namespace App\Jobs;

use App\Models\Upload;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProcessUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Upload $upload) {}

    public function handle(): void
    {
        $this->upload->update([
            'status' => 'processing',
            'error' => null,
            'rows_processed' => 0,
        ]);

        // Ensure file exists
        if (!Storage::disk('local')->exists($this->upload->stored_path)) {
            $this->upload->update([
                'status' => 'failed',
                'error'  => 'Upload file missing: '.$this->upload->stored_path,
            ]);
            return;
        }

        $path = Storage::disk('local')->path($this->upload->stored_path);

        $file = new \SplFileObject($path);
        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY);

        $header = null;
        $buffer = [];
        $processed = 0;
        $skippedMissingKey = 0;

        foreach ($file as $row) {
            if ($row === false || $row === [null]) {
                continue;
            }

            // First non-empty line is header
            if ($header === null) {
                $header = array_map(function ($h, $i) {
                    $h = (string) $h;
                    // strip BOM on first header cell if present
                    if ($i === 0) {
                        $h = preg_replace('/^\xEF\xBB\xBF/', '', $h);
                    }
                    return trim($h);
                }, $row, array_keys($row));
                continue;
            }

            $data = $this->mapRow($header, $row);
            if (!$data) continue;

            // Clean UTF-8 + trim
            array_walk($data, function (&$v) {
                if ($v === null) return;
                $v = iconv('UTF-8', 'UTF-8//IGNORE', (string) $v);
                $v = trim($v);
            });

            // Guard: UNIQUE_KEY must exist and not be placeholder
            $uniqueKey = isset($data['UNIQUE_KEY']) ? trim((string) $data['UNIQUE_KEY']) : '';
            if ($uniqueKey === '' || $uniqueKey === '?') {
                $skippedMissingKey++;
                continue; // skip this row
            }

            // Normalise price (remove commas/currency if any)
            $priceRaw = $data['PIECE_PRICE'] ?? null;
            if ($priceRaw !== null && $priceRaw !== '') {
                $priceNorm = preg_replace('/[^0-9.\-]/', '', (string) $priceRaw);
                $piecePrice = $priceNorm === '' ? null : (float) $priceNorm;
            } else {
                $piecePrice = null;
            }

            $buffer[] = [
                'unique_key'             => $uniqueKey,
                'product_title'          => $data['PRODUCT_TITLE'] ?? null,
                'product_description'    => $data['PRODUCT_DESCRIPTION'] ?? null,
                'style_number'           => $data['STYLE#'] ?? null,
                'sanmar_mainframe_color' => $data['SANMAR_MAINFRAME_COLOR'] ?? null,
                'size'                   => $data['SIZE'] ?? null,
                'color_name'             => $data['COLOR_NAME'] ?? null,
                'piece_price'            => $piecePrice,
                'created_at'             => now(),
                'updated_at'             => now(),
            ];

            $processed++;

            if (count($buffer) >= 1000) {
                $this->doUpsert($buffer);
                $buffer = [];
                $this->upload->update(['rows_processed' => $processed]);
            }
        }

        if (!empty($buffer)) {
            $this->doUpsert($buffer);
        }

        // Completed (but note any skips)
        $this->upload->update([
            'status' => 'completed',
            'rows_processed' => $processed,
            'error' => $skippedMissingKey > 0 ? "Skipped {$skippedMissingKey} row(s) without UNIQUE_KEY." : null,
        ]);
    }

    private function doUpsert(array $rows): void
    {
        DB::table('products')->upsert(
            $rows,
            ['unique_key'],
            [
                'product_title',
                'product_description',
                'style_number',
                'sanmar_mainframe_color',
                'size',
                'color_name',
                'piece_price',
                'updated_at',
            ]
        );
    }

    private function mapRow(array $header, array $row): ?array
    {
        if (count(array_filter($row, fn($v) => $v !== null && $v !== '')) === 0) {
            return null;
        }
        $assoc = [];
        foreach ($header as $i => $key) {
            $assoc[$key] = $row[$i] ?? null;
        }
        return $assoc;
    }

    public function failed(\Throwable $e): void
    {
        $this->upload->update([
            'status' => 'failed',
            'error'  => $e->getMessage(),
        ]);
    }
}
