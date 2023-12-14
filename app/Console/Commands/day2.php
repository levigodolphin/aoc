<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class day2 extends Command
{
    /**
     * @var string
     */
    protected $signature = 'advent:day2';

    /**
     * @var string
     */
    protected $description = 'Some game stuff?';

    /**
     * @var array<string, int>
     */
    private array $target = [
        'red' => 12,
        'green' => 13,
        'blue' => 14,
    ];

    public function handle()
    {
        $file = Storage::disk('files')->get('games.csv');

        $sum = collect(explode("\r".PHP_EOL, $file))
            ->map($this->getEligableIds(...))
            ->filter(fn (?int $value) => is_numeric($value))
            ->sum();

        $this->info($sum);
    }

    private function getEligableIds(string $line): ?int
    {
        $parts = explode(':', $line);

        foreach(explode(';', $parts[1]) as $check) {            
            foreach(explode(',', $check) as $pull) {
                foreach(array_keys($this->target) as $color) {
                    if(str_contains($pull, $color)) {
                        if((int) trim(str_replace($color, '', $pull)) > $this->target[$color]) {
                            return null;
                        }

                        break;
                    }
                }
            }
        }

        return str_replace('Game ', '', $parts[0]);
    }
}
