<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
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

        $wins = collect(explode("\r".PHP_EOL, $file))
            ->mapWithKeys($this->getWinningNumbers(...))
            ->reverse();

        $prizeCards = $this->getPrizeCards($wins);

        $this->info('Sum: '.$prizeCards);
    }

    private function getPrizeCards(Collection $wins): int
    {
        return array_sum(
                $wins->reduce(function (array $carry, int $winCount, int $game) use ($wins) {
                $carry[$game] = $carry[$game] ?? 1;

                $this->tallyChildCardPrizes($game, $game, $carry, $wins);

                return $carry;
            }, [])
        );
    }

    private function tallyChildCardPrizes(int $game, int $original, array &$gameCardPrizes, Collection $wins): void
    {
        if (! $wins->get($game)) {
            return;
        }

        foreach (range($game + 1, $game + $wins->get($game)) as $prizeGame) {
            if (isset($gameCardPrizes[$prizeGame])) {
                $gameCardPrizes[$original] += $gameCardPrizes[$prizeGame];

                continue;
            }

            $this->tallyChildCardPrizes($prizeGame, $original, $gameCardPrizes, $wins);
        }
    }

    private function getWinningNumbers(string $card): array
    {
        $game = explode(':', $card);

        [$winning, $chosen] = explode('|', $game[1]);

        return [
            str_replace(['Card', ' '], ['', ''], trim($game[0])) =>
            count(
                array_filter(
                    array_unique(
                        array_intersect(
                            explode(' ', trim($winning)), 
                            explode(' ', trim($chosen))
                        )
                    )
                )
            )
        ];
    }
}
