<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class day4 extends Command
{
    /**
     * @var string
     */
    protected $signature = 'advent:day4';

    /**
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = Storage::disk('files')->get('tickets.csv');

        $sum = collect(explode("\r".PHP_EOL, $file))
            ->map($this->getWinningNumbers(...))
            ->reject(fn (array $win) => empty($win))
            ->map($this->scoreWins(...))
            ->sum();

        $this->info('Sum: '.$sum);
    }

    private function scoreWins(array $wins): int
    {
        return collect(array_values($wins))
            ->reduce(function (int $carry, string $number, int $index) {
                return $index === 0 ? $carry : $carry * 2;
            }, 1);
    }

    private function getWinningNumbers(string $card): array
    {
        $game = explode(':', $card);

        [$winning, $chosen] = explode('|', $game[1]);

        return array_filter(
            array_unique(
                array_intersect(
                    explode(' ', trim($winning)), 
                    explode(' ', trim($chosen))
                )
            )
        );
    }
}
