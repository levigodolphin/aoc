<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class day5 extends Command
{
    /**
     * @var string
     */
    protected $signature = 'advent:day5';

    /**
     * @var string
     */
    protected $description = 'Command description';

    private string $currentKey = 'seeds';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = Storage::disk('files')->get('seeds.csv');

        $maps = collect(explode("\r".PHP_EOL, $file))  
            ->reject(fn (string $line) => empty($line))
            ->reduce($this->getSeedsAndMaps(...), [
                'seeds-start' => [],
                'seed-to-soil' => [], 
                'soil-to-fertilizer' => [], 
                'fertilizer-to-water' => [], 
                'water-to-light' => [], 
                'light-to-temperature' => [], 
                'temperature-to-humidity' => [],
                'humidity-to-location' => [],
            ]);

        $dests = collect($maps)
            ->pipe($this->getDests(...))
            ->pluck('location')
            ->flatten()
            ->min();

        $this->info('Lowest dest: '. $dests);
    }

    private function getDests(Collection $maps): Collection
    {
        $seeds = array_fill_keys($maps['seeds-start'], []);

        $maps->forget('seeds-start');

        foreach($seeds as $seed => $translations) {
            foreach ($maps as $key => $ranges) {
                [$source, $to, $dest] = explode('-', $key);

                foreach($ranges as [$destStart, $srcStart, $length]) {
                    $last = $seeds[$seed][$source] ?? $seed;

                    $seeds[$seed][$dest] = $last;

                    if($last >= $srcStart && $last <= $srcStart + $length) {
                        $seeds[$seed][$dest] = $destStart + ($last - $srcStart);

                        continue 2;
                    }
                }
            }
        }

        return collect($seeds);
    }

    private function getSeedsAndMaps(array $carry, string $line): array
    {
        $parts = explode(':', $line);

        $key = trim(str_replace('map', '', $parts[0]));

        match(true) {
            isset($carry[$key])   => $this->currentKey = $key,
            ! isset($parts[1])    => $carry[$this->currentKey][] = explode(' ', trim($line)),
            $parts[0] === 'seeds' => $carry['seeds-start'] = explode(' ', trim($parts[1]))
        };

        return $carry;
    }
}
