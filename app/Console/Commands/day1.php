<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class day1 extends Command
{
    /**
     * @var string
     */
    protected $signature = 'advent:day1';

    /**
     * @var string
     */
    protected $description = 'Advent day 1, Some code stuff';

    /** 
     * @var array<int, string>
     */
    private array $numbers = ['one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine'];

    /** 
     * @var array<int, gnirts>
     */
    private array $numbersReversed;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // get CSV - should stream it instead of putting into memory but YOLO
        $file = Storage::disk('files')->get('codes.csv');

        $total = collect(explode("\r".PHP_EOL, $file))
            ->map($this->extract(...))
            ->sum();

        $this->info('sum: '.$total);
    }

    private function extract(string $code) : int
    {
        return $this->firstDigit($code).$this->lastDigit($code);
    }

    private function firstDigit(string $code): int
    {
        return $this->makeSubstitution($this->numbers, $code);
    }

    private function lastDigit(string $code): int
    {
        $numbers = $this->numbersReversed ?? (
            $this->numbersReversed = array_map(
                fn (string $number) => strrev($number),
                $this->numbers
            )
        );

       return $this->makeSubstitution($numbers, strrev($code));
    }

    private function makeSubstitution(array $search, string $code): int
    {
        $subs = '';
        foreach(str_split($code, 2) as $letters) {
            $subs = str_replace(
                $search,
                range(1, 9),
                $subs .= $letters
            );

            preg_match_all('/\d/', $subs, $matches);

            if(isset($matches[0][0])) {
                return $matches[0][0];
            }
        }

        throw new Exception('u goofed up kid');
    }
}
