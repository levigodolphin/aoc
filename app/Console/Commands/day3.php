<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class day3 extends Command
{
    /**
     * @var string
     */
    protected $signature = 'advent:day3';

    /**
     * @var string
     */
    protected $description = 'Ngin';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = Storage::disk('files')->get('engine.csv');

        $sum = collect(explode("\r".PHP_EOL, $file))
            ->mapWithKeys($this->mapElements(...))
            ->pipe($this->getRelevantPartNumbers(...))
            ->flatten()
            ->sum();

        $this->info('Sum:'.$sum);
    }

    private function getRelevantPartNumbers(Collection $collection): Collection
    {
        $relevantNumbers = collect($collection)
            ->flatMap(function ($locations, $row) use ($collection) {
                return collect($locations['symbols'])
                    ->flatMap(function ($symbol, $index) use ($collection, $row) {
                        return $this->gaussianPass($collection, $row, $index);
                    });
            });

        return $relevantNumbers;
    }

    private function gaussianPass(Collection $collection, int $x, int $y): array
    {
        $gaussianMatrix = [
            [-1, -1], [-1, 0], [-1, 1],
            [0, -1], [0, 0], [0, 1],
            [1, -1], [1, 0], [1, 1],
        ];
        
        [$indexesToCheck, $ignore] = [collect($gaussianMatrix)
            ->map(function ($modifiers) use ($x, $y) {
                [$modX, $modY] = $modifiers;

                return [$x + $modX, $y + $modY];
            })
            ->toArray(), []];
        
        $hits = collect($indexesToCheck)
            ->filter(function ($indexes) use ($collection) {
                [$row, $index] = $indexes;

                return isset($collection[$row]['numbers'][$index]);
            })
            ->reject(function ($indexes) use (&$ignore) {
                [$row, $index] = $indexes;

                return isset($ignore["r{$row}i{$index}"]);
            })
            ->map(function ($indexes) use ($collection, &$ignore) {
                [$row, $index] = $indexes;
                
                $hit = $collection[$row]['numbers'][$index];

                $ignore["r{$row}i{$index}"] = 1;

                foreach([-1, 1] as $direction) {
                    $before = $index + $direction;

                    if(isset($ignore["r{$row}i{$before}"])) {
                        return null;
                    }

                    $hit = $this->findConsecutiveNumbers($collection, $ignore, $row, $index, $hit, $direction);
                }
        
                return $hit;
            })
            ->filter(fn(?int $hit) => is_numeric($hit))
            ->toArray();

        
        return $hits;        
    }

    private function findConsecutiveNumbers($collection, &$ignore, $row, $index, $hit, $direction): int
    {   
        $current = $index + $direction;
    
        while (isset($collection[$row]['numbers'][$current]) && !isset($ignore["r{$row}i{$current}"])) {
            $hit = $direction === 1 
                ? $hit . $collection[$row]['numbers'][$current] 
                : $collection[$row]['numbers'][$current] . $hit;

            $ignore["r{$row}i{$current}"] = 1;
            $current += $direction;
        }
    
        return $hit;
    }

    private function mapElements(string $line, int $key): array
    {
        $elementIndexes = collect(str_split($line))
            ->reject(function ($element) {
                return $element === '.';
            })
            ->reduce(function ($carry, $item, $index) {
                $type = is_numeric($item) ? 'numbers' : 'symbols';

                $carry[$type][$index] = $item;

                return $carry;
            }, [
                'symbols' => [],
                'numbers' => [],
            ]);

        return [$key => $elementIndexes];
    }
}
