<?php

namespace xSuper\OqexPractice\duel\utils;

use Generator;
use pocketmine\entity\Location;
use pocketmine\Server;
use xSuper\OqexPractice\entities\leaderboard\LeaderboardEntity;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\SOFe\AwaitGenerator\Await;
use xSuper\OqexPractice\OqexPractice;

class Leaderboard implements LeaderboardIds
{
    /** @var LeaderboardCache[] */
    private static array $leaderboards = [];

    /** @var LeaderboardEntity[] */
    private static array $entities = [];

    public static function init(): void
    {
        $ladders = [self::AVERAGE_ELO, self::NO_DEBUFF_ELO, self::DEBUFF_ELO, self::GAPPLE_ELO, self::BUILD_UHC_ELO, self::COMBO_ELO, self::SUMO_ELO, self::VANILLA_ELO, self::ARCHER_ELO, self::SOUP_ELO, self::BRIDGE_ELO];

        foreach ($ladders as $ladder) self::$leaderboards[$ladder] = new LeaderboardCache();

        self::$leaderboards[self::KILLS_DAILY] = new LeaderboardCache();
        self::$leaderboards[self::KILLS_WEEKLY] = new LeaderboardCache();
        self::$leaderboards[self::KILLS_MONTHLY] = new LeaderboardCache();
        self::$leaderboards[self::KILLS_LIFETIME] = new LeaderboardCache();

        self::$leaderboards[self::DEATHS_DAILY] = new LeaderboardCache();
        self::$leaderboards[self::DEATHS_WEEKLY] = new LeaderboardCache();
        self::$leaderboards[self::DEATHS_MONTHLY] = new LeaderboardCache();
        self::$leaderboards[self::DEATHS_LIFETIME] = new LeaderboardCache();

        self::$leaderboards[self::KD_DAILY] = new LeaderboardCache();
        self::$leaderboards[self::KD_WEEKLY] = new LeaderboardCache();
        self::$leaderboards[self::KD_MONTHLY] = new LeaderboardCache();
        self::$leaderboards[self::KD_LIFETIME] = new LeaderboardCache();

        self::$leaderboards[self::PARKOUR_LIFETIME] = new LeaderboardCache();
        self::$leaderboards[self::PARKOUR_MONTHLY] = new LeaderboardCache();
        self::$leaderboards[self::PARKOUR_WEEKLY] = new LeaderboardCache();
        self::$leaderboards[self::PARKOUR_DAILY] = new LeaderboardCache();

        self::$entities[self::KILLS] = new LeaderboardEntity(self::KILLS, new Location(13.5, 66, -11.5, Server::getInstance()->getWorldManager()->getDefaultWorld(), 0, 0));
        self::$entities[self::DEATHS] = new LeaderboardEntity(self::DEATHS, new Location(13.5, 66, 12.5, Server::getInstance()->getWorldManager()->getDefaultWorld(), 0, 0));
        self::$entities[self::KD] = new LeaderboardEntity(self::KD, new Location(3.5, 66, 0.5, Server::getInstance()->getWorldManager()->getDefaultWorld(), 0, 0));
        self::$entities[self::ELO] = new LeaderboardEntity(self::ELO, new Location(25.5, 66, 0.5, Server::getInstance()->getWorldManager()->getDefaultWorld(), 0, 0));
        self::$entities[self::PARKOUR] = new LeaderboardEntity(self::PARKOUR, new Location(-40.5, 67, -6.5, Server::getInstance()->getWorldManager()->getDefaultWorld(), 0, 0));

        foreach (['NoDebuff', 'Debuff', 'Gapple', 'BuildUHC', 'Combo', 'Sumo', 'Vanilla', 'Archer', 'Soup', 'Bridge'] as $ladder) {
            self::updateElo($ladder);
        }
        self::updateAverageElo();
        $timeframes = [self::DAILY, self::WEEKLY, self::MONTHLY, self::LIFETIME];
        foreach ($timeframes as $timeframe) {
            self::updateParkour($timeframe);
            self::updateKills($timeframe);
            self::updateDeaths($timeframe);
            self::updateKD($timeframe);
        }
    }

    public static function getLeaderboard(int $id): LeaderboardCache
    {
        return self::$leaderboards[$id];
    }

	/** @param 'NoDebuff'|'Debuff'|'Gapple'|'BuildUHC'|'Combo'|'Sumo'|'Vanilla'|'Archer'|'Soup'|'Bridge' $ladder */
    public static function updateElo(string $ladder): void
    {
        Await::f2c(function () use($ladder): Generator{
			/** @var list<array{'name': string, 'elo': int}> $rows */
			$rows = yield from OqexPractice::getDatabase()->asyncSelect('oqex-practice.stats.get_top_ten_elo_by_ladder', [
				'ladder' => $ladder
			]);
			$playerElos = array_map(fn(array $row) => [$row['name'], $row['elo']], $rows);
            (match($ladder){
               "NoDebuff" => self::$leaderboards[self::NO_DEBUFF_ELO],
               "Debuff" => self::$leaderboards[self::DEBUFF_ELO],
               "Gapple" => self::$leaderboards[self::GAPPLE_ELO],
               "BuildUHC" => self::$leaderboards[self::BUILD_UHC_ELO],
               "Combo" => self::$leaderboards[self::COMBO_ELO],
               "Sumo" => self::$leaderboards[self::SUMO_ELO],
               "Vanilla" => self::$leaderboards[self::VANILLA_ELO],
               "Archer" => self::$leaderboards[self::ARCHER_ELO],
               "Soup" => self::$leaderboards[self::SOUP_ELO],
               "Bridge" => self::$leaderboards[self::BRIDGE_ELO]
            })->update($playerElos);
            $entity = self::$entities[self::ELO];
            foreach ($entity->getViewers() as $viewer) {
                $entity->despawnFrom($viewer);
                $entity->spawnTo($viewer);
            }
        });
    }

    public static function updateAverageElo(): void
    {
        Await::f2c(function (): Generator{
			/** @var list<array{'name': string, 'avg': int}> $rows */
			$rows = yield from OqexPractice::getDatabase()->asyncSelect('oqex-practice.stats.get_top_ten_average_elos');
			$playerAvgElos = array_map(fn(array $row) => [$row['name'], $row['avg']], $rows);
            self::$leaderboards[self::AVERAGE_ELO]->update($playerAvgElos);
            $entity = self::$entities[self::ELO];
            foreach ($entity->getViewers() as $viewer) {
                $entity->despawnFrom($viewer);
                $entity->spawnTo($viewer);
            }
        });
    }

    public static function updateParkour(int $timeframe): void
    {
        Await::f2c(function() use($timeframe): Generator{
			/** @var list<array{'name': string, 'record': int<0, max>}> $rows */
			$rows = yield from OqexPractice::getDatabase()->asyncSelect('oqex-practice.stats.get_top_ten_parkour_records_by_timeframe', [
				'timeframe' => $timeframe
			]);
			$playerParkours = array_map(fn(array $row) => [$row['name'], $row['record']], $rows);
            (match($timeframe){
				default => self::$leaderboards[self::PARKOUR_DAILY],
                self::WEEKLY => self::$leaderboards[self::PARKOUR_WEEKLY],
                self::MONTHLY => self::$leaderboards[self::PARKOUR_MONTHLY],
                self::LIFETIME => self::$leaderboards[self::PARKOUR_LIFETIME]
            })->update(array_map(static fn(array $playerParkour) => [$playerParkour[0], gmdate('i:s', $playerParkour[1])], $playerParkours));
            $entity = self::$entities[self::PARKOUR];
            foreach ($entity->getViewers() as $viewer) {
                $entity->despawnFrom($viewer);
                $entity->spawnTo($viewer);
            }
        });
    }

    public static function updateKills(int $timeframe): void
    {
        Await::f2c(function() use($timeframe): Generator{
			/** @var list<array{'name': string, 'amount': int<0, max>}> $rows */
			$rows = yield from OqexPractice::getDatabase()->asyncSelect('oqex-practice.stats.get_top_ten_kills_by_timeframe', [
				'timeframe' => $timeframe
			]);
			$playerKills = array_map(fn(array $row) => [$row['name'], $row['amount']], $rows);
            (match($timeframe){
                default => self::$leaderboards[self::KILLS_DAILY],
                self::WEEKLY => self::$leaderboards[self::KILLS_WEEKLY],
                self::MONTHLY => self::$leaderboards[self::KILLS_MONTHLY],
                self::LIFETIME => self::$leaderboards[self::KILLS_LIFETIME]
            })->update($playerKills);
            $entity = self::$entities[self::KILLS];
            foreach ($entity->getViewers() as $viewer) {
                $entity->despawnFrom($viewer);
                $entity->spawnTo($viewer);
            }
        });
    }

    public static function updateDeaths(int $timeframe): void
    {
        Await::f2c(function() use($timeframe): Generator{
			/** @var list<array{'name': string, 'amount': int<0, max>}> $rows */
			$rows = yield from OqexPractice::getDatabase()->asyncSelect('oqex-practice.stats.get_top_ten_deaths_by_timeframe', [
				'timeframe' => $timeframe
			]);
			$playerDeaths = array_map(fn(array $row) => [$row['name'], $row['amount']], $rows);
            (match($timeframe){
                default => self::$leaderboards[self::DEATHS_DAILY],
                self::WEEKLY => self::$leaderboards[self::DEATHS_WEEKLY],
                self::MONTHLY => self::$leaderboards[self::DEATHS_MONTHLY],
                self::LIFETIME => self::$leaderboards[self::DEATHS_LIFETIME]
            })->update($playerDeaths);
            $entity = self::$entities[self::DEATHS];
            foreach ($entity->getViewers() as $viewer) {
                $entity->despawnFrom($viewer);
                $entity->spawnTo($viewer);
            }
        });
    }

    public static function updateKD(int $timeframe): void
    {
        Await::f2c(function() use($timeframe): Generator{
			/** @var list<array{'name': string, 'amount': float}> $rows */
			$rows = yield from OqexPractice::getDatabase()->asyncSelect('oqex-practice.stats.get_top_ten_kdrs_by_timeframe', [
				'timeframe' => $timeframe
			]);
			$playerKDRs = array_map(fn(array $row) => [$row['name'], $row['amount']], $rows);
            (match($timeframe){
                default => self::$leaderboards[self::KD_DAILY],
                self::WEEKLY => self::$leaderboards[self::KD_WEEKLY],
                self::MONTHLY => self::$leaderboards[self::KD_MONTHLY],
                self::LIFETIME => self::$leaderboards[self::KD_LIFETIME]
            })->update($playerKDRs);
            $entity = self::$entities[self::KD];
            foreach ($entity->getViewers() as $viewer) {
                $entity->despawnFrom($viewer);
                $entity->spawnTo($viewer);
            }
        });
    }

	/** @param list<array{'name': string, 'amount': int<0, max>}> $data */
	public static function updateKillsWithData(int $timeframe, array $data): void{
		$playerKills = array_map(fn(array $row) => [$row['name'], $row['amount']], $data);
		(match($timeframe){
			default => self::$leaderboards[self::KILLS_DAILY],
			self::WEEKLY => self::$leaderboards[self::KILLS_WEEKLY],
			self::MONTHLY => self::$leaderboards[self::KILLS_MONTHLY],
			self::LIFETIME => self::$leaderboards[self::KILLS_LIFETIME]
		})->update($playerKills);
		$entity = self::$entities[self::KILLS];
		foreach ($entity->getViewers() as $viewer) {
			$entity->despawnFrom($viewer);
			$entity->spawnTo($viewer);
		}
	}

	/** @param list<array{'name': string, 'amount': int<0, max>}> $data */
	public static function updateDeathsWithData(int $timeframe, array $data): void{
		$playerDeaths = array_map(fn(array $row) => [$row['name'], $row['amount']], $data);
		(match($timeframe){
			default => self::$leaderboards[self::DEATHS_DAILY],
			self::WEEKLY => self::$leaderboards[self::DEATHS_WEEKLY],
			self::MONTHLY => self::$leaderboards[self::DEATHS_MONTHLY],
			self::LIFETIME => self::$leaderboards[self::DEATHS_LIFETIME]
		})->update($playerDeaths);
		$entity = self::$entities[self::DEATHS];
		foreach ($entity->getViewers() as $viewer) {
			$entity->despawnFrom($viewer);
			$entity->spawnTo($viewer);
		}
	}

	/** @param list<array{'name': string, 'amount': float}> $data */
	public static function updateKDWithData(int $timeframe, array $data): void{
		$playerKDRs = array_map(fn(array $row) => [$row['name'], $row['amount']], $data);
		(match($timeframe){
			default => self::$leaderboards[self::KD_DAILY],
			self::WEEKLY => self::$leaderboards[self::KD_WEEKLY],
			self::MONTHLY => self::$leaderboards[self::KD_MONTHLY],
			self::LIFETIME => self::$leaderboards[self::KD_LIFETIME]
		})->update($playerKDRs);
		$entity = self::$entities[self::KD];
		foreach ($entity->getViewers() as $viewer) {
			$entity->despawnFrom($viewer);
			$entity->spawnTo($viewer);
		}
	}

	/** @param list<array{'name': string, 'record': int<0, max>}> $data */
	public static function updateParkourWithData(int $timeframe, array $data): void{
		$playerParkours = array_map(fn(array $row) => [$row['name'], $row['record']], $data);
		(match($timeframe){
			default => self::$leaderboards[self::PARKOUR_DAILY],
			self::WEEKLY => self::$leaderboards[self::PARKOUR_WEEKLY],
			self::MONTHLY => self::$leaderboards[self::PARKOUR_MONTHLY],
			self::LIFETIME => self::$leaderboards[self::PARKOUR_LIFETIME]
		})->update(array_map(static fn(array $playerParkour) => [$playerParkour[0], gmdate('i:s', $playerParkour[1])], $playerParkours));
		$entity = self::$entities[self::PARKOUR];
		foreach ($entity->getViewers() as $viewer) {
			$entity->despawnFrom($viewer);
			$entity->spawnTo($viewer);
		}
	}
}