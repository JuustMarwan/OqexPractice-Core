<?php

namespace xSuper\OqexPractice\duel\utils;

use pocketmine\utils\AssumptionFailedError;

/**
 * Class Elo
 * @package xSuper\Practice\duel
 *
 * Elo Calculating algorithm made by
 * https://github.com/konpa/elo-rating/tree/fide_k-factor.
 *
 * It takes into account both players Elo, the score of their game (In this case it will always
 * be by a 1 win margin), and the amount of ranked games both players have played.
 */

class Elo
{
    public const STARTING_ELO = 1000;

    protected int $ratingA;
    protected int $ratingB;
    protected int $gamesNumberA;
    protected int $gamesNumberB;

    protected int $scoreA;
    protected int $scoreB;

    protected int|float $expectedA;
    protected int|float $expectedB;

    protected int $KFactorA;
    protected int $KFactorB;

    protected int|float $newRatingA;
    protected int|float $newRatingB;

    public const LADDERS = [
        'NoDebuff',
        'Debuff',
        'Gapple',
        'BuildUHC',
        'Combo',
        'Sumo',
        'Vanilla',
        'Archer',
        'Soup',
        'Bridge'
    ];

    public const RANKS = [
        'Iron I' => [0, 960],
        'Iron II' => [961, 970],
        'Iron III' => [971, 980],
        'Iron IV' => [981, 990],
        'Iron V' => [991, 1000],
        'Gold I' => [1001, 1010],
        'Gold II' => [1011, 1020],
        'Gold III' => [1021, 1030],
        'Gold IV' => [1031, 1040],
        'Gold V' => [1041, 1050],
        'Diamond I' => [1051, 1060],
        'Diamond II' => [1061, 1070],
        'Diamond III' => [1071, 1080],
        'Diamond IV' => [1081, 1090],
        'Diamond V' => [1091, 1100],
        'Emerald I' => [1101, 1120],
        'Emerald II' => [1121, 1140],
        'Emerald III' => [1141, 1160],
        'Emerald IV' => [1161, 1180],
        'Emerald V' => [1181, 1200],
        'Obsidian' => [1201]
    ];

    public static function getColorCode(string $rank): string
    {
        $short = substr($rank, 0, 1);
        return match ($short) {
            'I' => '§r§7',
            'G' => '§r§e',
            'D' => '§r§b',
            'E' => '§r§a',
            'O' => '§r§5',
            'B' => '§r§9',
            default => '§r',
        };

    }

	/**
	 * @param int $type
	 * @return list<array{string, numeric|string}>
	 */
    public static function leaderboard(int $type): array
    {
        return Leaderboard::getLeaderboard($type)->getData();
    }

    public static function getLadderRank(int $elo): ?string
    {
        foreach (self::RANKS as $name => $data) {
            $lb = array_slice(self::leaderboard(LeaderboardIds::AVERAGE_ELO), 0, 5, true);

            $lMax = self::RANKS['Obsidian'][0];
            if ($elo >= $lMax) {
                $lowest = $lb[4][1] ?? 1201;

                if ($elo >= $lowest) return 'Bedrock';
                else return 'Obsidian';
            }

            $min = $data[0];
            $max = $data[1] ?? throw new AssumptionFailedError('This should be defined at this point');

            if ($elo >= $min && $elo <= $max) return $name;
        }

        return null;
    }

    public function __construct(int $ratingA, int $ratingB, int $scoreA, int $scoreB, int $gamesNumberA, int $gamesNumberB)
    {
        $this->ratingA = $ratingA;
        $this->ratingB = $ratingB;
        $this->gamesNumberA = $gamesNumberA;
        $this->gamesNumberB = $gamesNumberB;
        $this->scoreA = $scoreA;
        $this->scoreB = $scoreB;

        $expectedScores = $this->getExpectedScores($this->ratingA, $this->ratingB);
        $this->expectedA = $expectedScores['a'];
        $this->expectedB = $expectedScores['b'];

        $KFactor = $this->getKFactors($this->ratingA, $this->ratingB, $this->gamesNumberA, $this->gamesNumberB);
        $this->KFactorA = $KFactor['a'];
        $this->KFactorB = $KFactor['b'];

        $newRatings = $this->_getNewRatings($this->ratingA, $this->ratingB, $this->expectedA, $this->expectedB, $this->scoreA, $this->scoreB, $this->KFactorA, $this->KFactorB);
        $this->newRatingA = $newRatings['a'];
        $this->newRatingB = $newRatings['b'];
    }

    public function setNewSettings(int $ratingA, int $ratingB, int $scoreA, int $scoreB): void
    {
        $this->ratingA = $ratingA;
        $this->ratingB = $ratingB;
        $this->scoreA = $scoreA;
        $this->scoreB = $scoreB;

        $expectedScores = $this->getExpectedScores($this->ratingA, $this->ratingB);
        $this->expectedA = $expectedScores['a'];
        $this->expectedB = $expectedScores['b'];

        $newRatings = $this->_getNewRatings($this->ratingA, $this->ratingB, $this->expectedA, $this->expectedB, $this->scoreA, $this->scoreB, $this->gamesNumberA, $this->gamesNumberB);
        $this->newRatingA = $newRatings['a'];
        $this->newRatingB = $newRatings['b'];
    }

	/** @return array{a: float|int, b: float|int} */
    public function getNewRatings(): array
    {
        return [
            'a' => $this->newRatingA,
            'b' => $this->newRatingB
        ];
    }

	/** @return array{a: float|int, b: float|int} */
    protected function getExpectedScores(int $ratingA, int $ratingB): array
    {
        $difA = $ratingA - $ratingB;

        if ($difA > 400) $difA = 400;
        else if ($difA < -400) $difA = -400;

        $expectedScoreA = 1 / (1 + (pow(10, -($difA) / 400)));


        $difB = $ratingB - $ratingA;

        if ($difB > 400) $difB = 400;
        else if ($difB < -400) $difB = -400;

        $expectedScoreB = 1 / (1 + (pow(10, -($difB) / 400)));


        return [
            'a' => $expectedScoreA,
            'b' => $expectedScoreB
        ];
    }

	/** @return array{a: int, b: int} */
    protected function getKFactors(int $ratingA, int $ratingB, int $gamesNumberA, int $gamesNumberB): array
    {
        if ($gamesNumberA < 30 && $ratingA < 2300) $KFactorA = 40;
        else if ($ratingA >= 30 && $ratingA < 2400) $KFactorA = 20;
        else $KFactorA = 10;


        if ($gamesNumberB < 30 && $ratingB < 2300) $KFactorB = 40;
        else if ($ratingB >= 30 && $ratingB < 2400) $KFactorB = 20;
        else $KFactorB = 10;


        return [
            'a' => $KFactorA,
            'b' => $KFactorB
        ];
    }

	/** @return array{a: float|int, b: float|int} */
    protected function _getNewRatings(int $ratingA, int $ratingB, float $expectedA, float $expectedB, int $scoreA, int $scoreB, int $KFactorA, int $KFactorB): array
    {
        $newRatingA = $ratingA + ($KFactorA * ($scoreA - $expectedA));
        $newRatingB = $ratingB + ($KFactorB * ($scoreB - $expectedB));

        return [
            'a' => $newRatingA,
            'b' => $newRatingB
        ];
    }
}