<?php

namespace xSuper\OqexPractice\threads;

use pmmp\thread\ThreadSafeArray;
use pocketmine\snooze\SleeperNotifier;
use pocketmine\thread\Thread;
use pocketmine\utils\AssumptionFailedError;

/**
 * @phpstan-type Stats array{'kills': int<0, max>, 'deaths': int<0, max>, 'parkour': int}
 */
class LeaderboardUpdateThread extends Thread
{
    public const LIFETIME = 'lifetime';
    public const MONTHLY = 'monthly';
    public const WEEKLY = 'weekly';
    public const DAILY = 'daily';
	/** @var ThreadSafeArray<int, string> */
    private ThreadSafeArray $queue;
	/** @var ThreadSafeArray<int, string> */
    public ThreadSafeArray $results;

    public function __construct(private readonly SleeperNotifier $notifier)
    {
        $this->queue = new ThreadSafeArray();
        $this->results = new ThreadSafeArray();
    }

	/** @param list<array{'elo': string, 'username': string, 'stats': string}> $data */
    public function addToQueue(array $data): void
    {
        $this->queue[] = igbinary_serialize($data) ?? throw new AssumptionFailedError('This should not return null');
    }

    protected function onRun(): void
    {
        while (!$this->isKilled) {
            while (is_string($queue = $this->queue->shift())) {
				/** @var list<array{'elo': string, 'username': string, 'stats': string}> $rows */
                $rows = igbinary_unserialize($queue);
                $data = [];
                
                foreach (['NoDebuff', 'Debuff', 'Gapple', 'BuildUHC', 'Combo', 'Sumo', 'Vanilla', 'Archer', 'Soup', 'Bridge'] as $ladder) {
                    $data['elo'][$ladder] = $this->elo($rows, $ladder); 
                }
                
                $data['averageElo'] = $this->averageElo($rows);
                
                foreach ([self::LIFETIME, self::MONTHLY, self::WEEKLY, self::DAILY] as $timeframe) {
                    $data['kills'][$timeframe] = $this->kills($rows, $timeframe);
                    $data['deaths'][$timeframe] = $this->deaths($rows, $timeframe);
                    $data['kd'][$timeframe] = $this->kd($rows, $timeframe);
                    $data['parkour'][$timeframe] = $this->parkour($rows, $timeframe);
                }

                $this->synchronized(function() use ($data): void{
                    $this->results[] = igbinary_serialize($data) ?? throw new AssumptionFailedError('This should not retunr null');

                    $this->notifier->wakeupSleeper();
                    $this->wait();
                });
            }
        }
    }

	/** @return ThreadSafeArray<int, string> */
    public function getResults(): ThreadSafeArray
    {
        return $this->results;
    }

	/**
	 * @param list<array{'elo': string, 'username': string, 'stats': string}> $rows
	 * @return list<array{string, int<0, max>}>
	 */
    private function elo(array $rows, string $ladder): array
    {
        usort($rows, function (array $a, array $b) use ($ladder): int {
			/** @var array<'NoDebuff'|'Debuff'|'Gapple'|'BuildUHC'|'Combo'|'Sumo'|'Vanilla'|'Archer'|'Soup'|'Bridge', int<0, max>> $eloA */
            $eloA = json_decode($a['elo'], true);
            $eloA = $eloA[$ladder];

			/** @var array<'NoDebuff'|'Debuff'|'Gapple'|'BuildUHC'|'Combo'|'Sumo'|'Vanilla'|'Archer'|'Soup'|'Bridge', int<0, max>> $eloB */
            $eloB = json_decode($b['elo'], true);
            $eloB = $eloB[$ladder];

            if ($eloA === $eloB) return 0;
            return ($eloB < $eloA  ? - 1:1);
        });

        $leaderboard = [];

        foreach ($rows as $player) {
			/** @var array<'NoDebuff'|'Debuff'|'Gapple'|'BuildUHC'|'Combo'|'Sumo'|'Vanilla'|'Archer'|'Soup'|'Bridge', int<0, max>> $elo */
            $elo = json_decode($player['elo'], true);
            $elo = $elo[$ladder];

            $leaderboard[] = [$player['username'], $elo];
        }

        return $leaderboard;
    }

	/**
	 * @param list<array{'elo': string, 'username': string, 'stats': string}> $rows
	 * @return list<array{string, float}>
	 */
    private function averageElo(array $rows): array
    {
        usort($rows, function (array $a, array $b): int {
			/** @var array<'NoDebuff'|'Debuff'|'Gapple'|'BuildUHC'|'Combo'|'Sumo'|'Vanilla'|'Archer'|'Soup'|'Bridge', int<0, max>> $eloA */
            $eloA = json_decode($a['elo'], true);

            $avgA = 0;
            foreach ($eloA as $e) {
                $avgA += $e;
            }

            $cA = count($eloA);

            if ($avgA !== 0 && $cA !== 0) $avgA = $avgA / $cA;
            else $avgA = 1000;

			/** @var array<'NoDebuff'|'Debuff'|'Gapple'|'BuildUHC'|'Combo'|'Sumo'|'Vanilla'|'Archer'|'Soup'|'Bridge', int<0, max>> $eloB */
            $eloB = json_decode($b['elo'], true);

            $avgB = 0;
            foreach ($eloB as $e) {
                $avgB += $e;
            }

            $cB = count($eloB);

            if ($avgB !== 0 && $cB !== 0) $avgB = $avgB / $cB;
            else $avgB = 1000;

            if ($avgA === $avgB) return 0;
            return ($avgB < $avgA  ? - 1:1);
        });

        $leaderboard = [];

        foreach ($rows as $player) {
			/** @var array<'NoDebuff'|'Debuff'|'Gapple'|'BuildUHC'|'Combo'|'Sumo'|'Vanilla'|'Archer'|'Soup'|'Bridge', int<0, max>> $elo */
            $elo = json_decode($player['elo'], true);

            $avg = 0;
            foreach ($elo as $e) {
                $avg += $e;
            }

            $c = count($elo);

            if ($avg !== 0 && $c !== 0) $avg = $avg / $c;
            else $avg = 1000;

            $leaderboard[] = [$player['username'], $avg];
        }

        return $leaderboard;
    }
	/**
	 * @param list<array{'elo': string, 'username': string, 'stats': string}> $rows
	 * @param self::LIFETIME|self::MONTHLY|self::WEEKLY|self::DAILY $timeframe
	 * @return list<array{string, int<0, max>}>
	 */
    private function kills(array $rows, string $timeframe): array
    {
        usort($rows, function (array $a, array $b) use ($timeframe): int {
			/** @var array{'lifetime': Stats, 'monthly': Stats, 'weekly': Stats, 'daily': Stats} $rawKillsA */
			$rawKillsA = json_decode($a['stats'], true);
			$killsA = $rawKillsA[$timeframe]['kills'];
			/** @var array{'lifetime': Stats, 'monthly': Stats, 'weekly': Stats, 'daily': Stats} $rawKillsB */
			$rawKillsB = json_decode($b['stats'], true);
			$killsB = $rawKillsB[$timeframe]['kills'];

            return $killsB - $killsA;
        });

        $leaderboard = [];

        foreach ($rows as $player) {
			/** @var array{'lifetime': Stats, 'monthly': Stats, 'weekly': Stats, 'daily': Stats} $rawKills */
			$rawKills = json_decode($player['stats'], true);
			$kills = $rawKills[$timeframe]['kills'];
            $leaderboard[] = [$player['username'], $kills];
        }

        return $leaderboard;
    }
	/**
	 * @param list<array{'elo': string, 'username': string, 'stats': string}> $rows
	 * @param self::LIFETIME|self::MONTHLY|self::WEEKLY|self::DAILY $timeframe
	 * @return list<array{string, int<0, max>}>
	 */
    private function deaths(array $rows, string $timeframe): array
    {
        usort($rows, function (array $a, array $b) use ($timeframe): int {
			/** @var array{'lifetime': Stats, 'monthly': Stats, 'weekly': Stats, 'daily': Stats} $rawDeathsA */
			$rawDeathsA = json_decode($a['stats'], true);
			$deathsA = $rawDeathsA[$timeframe]['deaths'];
			/** @var array{'lifetime': Stats, 'monthly': Stats, 'weekly': Stats, 'daily': Stats} $rawDeathsB */
			$rawDeathsB = json_decode($b['stats'], true);
			$deathsB = $rawDeathsB[$timeframe]['deaths'];

            return $deathsB - $deathsA;
        });

        $leaderboard = [];

        foreach ($rows as $player) {
			/** @var array{'lifetime': Stats, 'monthly': Stats, 'weekly': Stats, 'daily': Stats} $rawDeaths */
			$rawDeaths = json_decode($player['stats'], true);
			$deaths = $rawDeaths[$timeframe]['deaths'];
            $leaderboard[] = [$player['username'], $deaths];
        }

        return $leaderboard;
    }
	/**
	 * @param list<array{'elo': string, 'username': string, 'stats': string}> $rows
	 * @param self::LIFETIME|self::MONTHLY|self::WEEKLY|self::DAILY $timeframe
	 * @return list<array{string, float}>
	 */
    private function kd(array $rows, string $timeframe): array
    {
        usort($rows, function (array $a, array $b) use ($timeframe): int {
			/** @var array{'lifetime': Stats, 'monthly': Stats, 'weekly': Stats, 'daily': Stats} $rawDeathsA */
			$rawDeathsA = json_decode($a['stats'], true);
			$deathsA = $rawDeathsA[$timeframe]['deaths'];
			/** @var array{'lifetime': Stats, 'monthly': Stats, 'weekly': Stats, 'daily': Stats} $rawDeathsB */
			$rawDeathsB = json_decode($b['stats'], true);
			$deathsB = $rawDeathsB[$timeframe]['deaths'];
			/** @var array{'lifetime': Stats, 'monthly': Stats, 'weekly': Stats, 'daily': Stats} $rawKillsA */
			$rawKillsA = json_decode($a['stats'], true);
			$killsA = $rawKillsA[$timeframe]['kills'];
			/** @var array{'lifetime': Stats, 'monthly': Stats, 'weekly': Stats, 'daily': Stats} $rawKillsB */
			$rawKillsB = json_decode($b['stats'], true);
			$killsB = $rawKillsB[$timeframe]['kills'];

            if ($deathsA === 0) $ratioA = $killsA;
            else $ratioA = $killsA / $deathsA;

            if ($deathsB === 0) $ratioB = $killsB;
            else $ratioB = $killsB / $deathsB;

            if ($ratioA === $ratioB) return 0;
            return ($ratioB < $ratioA  ? - 1:1);
        });

        $leaderboard = [];

        foreach ($rows as $player) {
			/** @var array{'lifetime': Stats, 'monthly': Stats, 'weekly': Stats, 'daily': Stats} $rawKills */
			$rawKills = json_decode($player['stats'], true);
			$kills = $rawKills[$timeframe]['kills'];
			/** @var array{'lifetime': Stats, 'monthly': Stats, 'weekly': Stats, 'daily': Stats} $rawDeaths */
			$rawDeaths = json_decode($player['stats'], true);
			$deaths = $rawDeaths[$timeframe]['deaths'];

            if ($deaths === 0) $ratio = $kills;
            else $ratio = $kills / $deaths;

            $leaderboard[] = [$player['username'], round($ratio, 2, PHP_ROUND_HALF_EVEN)];
        }

        return $leaderboard;
    }
	/**
	 * @param list<array{'elo': string, 'username': string, 'stats': string}> $rows
	 * @param self::LIFETIME|self::MONTHLY|self::WEEKLY|self::DAILY $timeframe
	 * @return list<array{string, string}>
	 */
    private function parkour(array $rows, string $timeframe): array
    {
        usort($rows, function (array $a, array $b) use ($timeframe): int {
			/** @var array{'lifetime': Stats, 'monthly': Stats, 'weekly': Stats, 'daily': Stats} $rawParkourA */
			$rawParkourA = json_decode($a['stats'], true);
			$parkourA = $rawParkourA[$timeframe]['parkour'];
			/** @var array{'lifetime': Stats, 'monthly': Stats, 'weekly': Stats, 'daily': Stats} $rawParkourB */
			$rawParkourB = json_decode($b['stats'], true);
			$parkourB = $rawParkourB[$timeframe]['parkour'];

            if ($parkourA === -1) $parkourA = PHP_FLOAT_MAX;
            if ($parkourB === -1) $parkourB = PHP_FLOAT_MAX;

            if ($parkourA === $parkourB) return 0;
            return ($parkourB > $parkourA  ? - 1:1);
        });

        $leaderboard = [];

        foreach ($rows as $player) {
			/** @var array{'lifetime': Stats, 'monthly': Stats, 'weekly': Stats, 'daily': Stats} $rawTime */
			$rawTime = json_decode($player['stats'], true);
			$time = $rawTime[$timeframe]['parkour'];;
            if ($time === -1) $time = 'No Score.';
            else $time = gmdate('i:s', $time);

            $leaderboard[] = [$player['username'], $time];
        }

        return $leaderboard;
    }
}