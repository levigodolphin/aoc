<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
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

    public function handle()
    {
        $file = Storage::disk('files')->get('games.csv');

        $sum = collect(explode("\r".PHP_EOL, $file))
            ->map($this->tallyGames(...))
            ->sum();

        $this->info($sum);
    }

    private function tallyGames(string $line): int
    {
        $game = explode(':', $line);

        $colorsMax = ['red' => 0, 'green' => 0, 'blue' => 0];

        collect(explode(';', $game[1]))
            ->flatMap(fn ($check) => explode(',', $check))
            ->each(function ($pull) use (&$colorsMax) {
                collect(array_keys($colorsMax))
                    ->filter(fn ($color) => str_contains($pull, $color))
                    ->each(function ($color) use (&$colorsMax, $pull) {
                        $colorsMax[$color] = max([
                            (int) trim(str_replace($color, '', $pull)), 
                            $colorsMax[$color],
                        ]);
                    });
            });

        return array_product(array_values($colorsMax));
    }
}
