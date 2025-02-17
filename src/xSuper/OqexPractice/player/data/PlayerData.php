<?php

namespace xSuper\OqexPractice\player\data;

use Closure;
use DateTime;
use Generator;
use pocketmine\item\Item;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Utils;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\poggit\libasynql\result\SqlSelectResult;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\poggit\libasynql\SqlThread;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\SOFe\AwaitGenerator\Await;
use xSuper\OqexPractice\duel\utils\Elo;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\cosmetics\Cosmetics;
use xSuper\OqexPractice\player\kit\Kit;
use xSuper\OqexPractice\player\settings\Settings;
use xSuper\OqexPractice\utils\ItemUtils;
use xSuper\OqexPractice\utils\LocalAC;

class PlayerData
{
    private const TIME_FRAMES = [
        'lifetime',
        'monthly',
        'weekly',
        'daily'
    ];

    private UuidInterface $uuid;
    private PlayerInfo $info;

    //private string $joined;
    //private int $joinedPos;
    private string $rank;
    private ?string $tempRank;
    private ?string $expires;
    private int $op;
    private int $rankedGames;
    private int $extraGames;
    private int $coins;

    private ?array $banned = [];
    private ?array $muted = [];

	/** @var array{'NoDebuff': int<0, max>, 'Debuff': int<0, max>, 'Gapple': int<0, max>, 'BuildUHC': int<0, max>, 'Combo': int<0, max>, 'Sumo': int<0, max>, 'Vanilla': int<0, max>, 'Archer': int<0, max>, 'Soup': int<0, max>, 'Bridge': int<0, max>, 'average': int<-1, max>} */
    private array $elo;
	/** @var array{'NoDebuff': int<0, max>, 'Debuff': int<0, max>, 'Gapple': int<0, max>, 'BuildUHC': int<0, max>, 'Combo': int<0, max>, 'Sumo': int<0, max>, 'Vanilla': int<0, max>, 'Archer': int<0, max>, 'Soup': int<0, max>, 'Bridge': int<0, max>} */
    private array $games;
	/** @var array{'lifetime': int<0, max>, 'monthly': int<0, max>, 'weekly': int<0, max>, 'daily': int<0, max>} */
    private array $kills;
	/** @var array{'lifetime': int<0, max>, 'monthly': int<0, max>, 'weekly': int<0, max>, 'daily': int<0, max>} */
    private array $deaths;
	/** @var array{'lifetime': float, 'monthly': float, 'weekly': float, 'daily': float} */
    private array $parkour;
	/** @var array<string, array<int, Item>> */
    private array $kits;

    private Cosmetics $cosmetics;
    private Settings $settings;

    private ?Closure $onLoad = null;

    public function __construct()
    {
    }

    public function finish(Closure $closure): void
    {
        $this->onLoad = $closure;
    }

    public function load(UuidInterface $uuid): void
    {
        $this->uuid = $uuid;
        $this->settings = new Settings($uuid);

        Await::f2c(function () use ($uuid): Generator {
            $stringUUID = $uuid->toString();
			/** @var array{
			 *     0: array{0: array{'rank': string, 'op': int<0, 1>, 'rGames': int, 'eGames': int, 'coins': int<0, max>, 'tempRank': ?string, 'expires': ?string, 'muted': ?string}},
			 *     1: array{0?: array{'duration': string, 'staff': string, 'reason': ?string}},
			 *     2: array{0: array{'lifetime': int<0, max>, 'monthly': int<0, max>, 'weekly': int<0, max>, 'daily': int<0, max>}},
			 *     3: array{0: array{'lifetime': int<0, max>, 'monthly': int<0, max>, 'weekly': int<0, max>, 'daily': int<0, max>}},
			 *     4: array{0: array{'lifetime': float, 'monthly': float, 'weekly': float, 'daily': float}},
			 *     5: list<array{'setting': key-of<Settings::DEFAULTS>, 'value': int<0, 2>}>,
			 *     6: list<array{'ladder': value-of<Elo::LADDERS>, 'elo': int<0, max>}>,
			 *     7: list<array{'game': value-of<Elo::LADDERS>, 'played': int<0, max>}>,
			 *     8: list<array{'name': string, 'contents': string}>,
			 *     9: array{0: array{'hat': ?string, 'backpack': ?string, 'belt': ?string, 'cape': ?string, 'tag': ?string, 'trail': ?string, 'potColor': ?string, 'chatColor': ?string, 'killPhrase': ?string}},
			 *     10: list<array{'hat': string}>,
			 *     11: list<array{'backpack': string}>,
			 *     12: list<array{'belt': string}>,
			 *     13: list<array{'cape': string}>,
			 *     14: list<array{'tag': string}>,
			 *     15: list<array{'trail': string}>,
			 *     16: list<array{'killPhrase': string}>,
			 *     17: list<array{'color': string}>,
			 *     18: list<array{'color': string}>
			 *         } $dataRows */
			$dataRows = array_map(fn(SqlSelectResult $select) => $select->getRows(), yield from Await::promise(
				static fn(Closure $resolve, Closure $reject) => OqexPractice::getDatabase()->executeMulti(
					'oqex-practice.players.get_data_to_load',
					['uuid' => $stringUUID],
					SqlThread::MODE_SELECT,
					$resolve,
					$reject
				)
			));
			$this->rank = $dataRows[0][0]['rank'];
			$this->op = $dataRows[0][0]['op'];
			$this->rankedGames = $dataRows[0][0]['rGames'];
			$this->extraGames = $dataRows[0][0]['eGames'];
			$this->coins = $dataRows[0][0]['coins'];
			$this->tempRank = $dataRows[0][0]['tempRank'];
			$this->expires = $dataRows[0][0]['expires'];
			$this->muted = isset($dataRows[0][0]['muted']) ? json_decode($dataRows[0][0]['muted'], true) : [];

			$this->banned = $dataRows[1][0] ?? [];

			foreach($dataRows[2][0] as $time => $kills){
				$this->kills[$time] = $kills;
			}
			foreach($dataRows[3][0] as $time => $deaths){
				$this->deaths[$time] = $deaths;
			}
			foreach($dataRows[4][0] as $time => $record){
				$this->parkour[$time] = $record;
			}

			foreach($dataRows[5] as ['setting' => $setting, 'value' => $value]){
				$this->settings->setSetting($setting, $value, false);
			}

			foreach($dataRows[6] as ['ladder' => $ladder, 'elo' => $elo]){
				$this->elo[$ladder] = $elo;
			}
			foreach($dataRows[7] as ['game' => $game, 'played' => $played]){
				$this->games[$game] = $played;
			}

			$this->calculateAverageElo();

			foreach(Kit::getKits() as $kit){
				$this->kits[$kit->getName()] = $kit->getContents();
			}
			foreach($dataRows[8] as ['name' => $kitName, 'contents' => $contents]){
				$this->kits[$kitName] = array_map(static fn(string $data) => ItemUtils::decode($data), igbinary_unserialize(Utils::assumeNotFalse(hex2bin($contents))));
			}

			$this->cosmetics = new Cosmetics(Uuid::fromString($stringUUID));
			$this->cosmetics->init($dataRows[9][0], [
				'hats' => array_column($dataRows[10], 'hat'),
				'backpacks' => array_column($dataRows[11], 'backpack'),
				'belts' => array_column($dataRows[12], 'belt'),
				'capes' => array_column($dataRows[13], 'cape'),
				'tags' => array_column($dataRows[14], 'tag'),
				'trails' => array_column($dataRows[15], 'trail'),
				'killPhrases' => array_column($dataRows[16], 'killPhrase'),
				'chatColors' => array_column($dataRows[17], 'color'),
				'potColors' => array_column($dataRows[18], 'color')
			]);

            $this->info = PlayerInfo::getData($stringUUID) ?? throw new AssumptionFailedError('This should not return null at this point');

            if ($this->onLoad !== null) ($this->onLoad)();
        });
    }

    public function calculateAverageElo(): void
    {
        $total = 0;

        foreach ($this->elo as $amount) {
            $total += $amount;
        }

        $count = count($this->elo);

        if ($total === 0) $this->elo['average'] = -1;
        else{
			$average = (int)floor($total / $count);
			if($average < 0){
				throw new AssumptionFailedError('Average should not be below zero');
			}
			$this->elo['average'] = $average;
		}
    }

    public function getCosmetics(): Cosmetics
    {
        return $this->cosmetics;
    }

    public function getInfo(): PlayerInfo
    {
        return $this->info;
    }

    public function getHighestRank(): string
    {
        if (($oldRank = $this->tempRank) !== null && RankMap::permissionMap($oldRank) > RankMap::permissionMap($this->rank)) {
            $from = date_create_from_format('Y-m-d H-i-s', $this->expires ?? throw new AssumptionFailedError('This should not be null'));
            $to = new DateTime();

            if ($from <= $to) {
                $this->tempRank = null;
                $this->expires = null;

				OqexPractice::getDatabase()->executeGeneric('oqex-practice.players.temp_rank_expired', [
					'uuid' => $this->uuid->toString()
				]);

                return $this->rank;
            }

            return $oldRank;
        }

        return $this->rank;
    }

    public function getRankPermission(): int
    {
        return RankMap::permissionMap($this->getHighestRank());
    }

    public function isOP(): bool
    {
        return (bool) $this->op;
    }

    public function getTotalRankedGames(): int
    {
        return $this->rankedGames + $this->extraGames;
    }

    public function getAverageElo(): int
    {
        return $this->elo['average'] ?? -1;
    }

    public function getElo(string $ladder): int
    {
        return $this->elo[$ladder] ?? -1;
    }

	/** @return array{'NoDebuff': int<0, max>, 'Debuff': int<0, max>, 'Gapple': int<0, max>, 'BuildUHC': int<0, max>, 'Combo': int<0, max>, 'Sumo': int<0, max>, 'Vanilla': int<0, max>, 'Archer': int<0, max>, 'Soup': int<0, max>, 'Bridge': int<0, max>, 'average': int<-1, max>} */
    public function getElos(): array
    {
        return $this->elo;
    }

    public function getRankedGames(): int
    {
        return $this->rankedGames;
    }

    public function getExtraRankedGames(): int
    {
        return $this->extraGames;
    }

    public function getCoins(): int
    {
        return $this->coins;
    }

    public function getKills(string $time): int
    {
        return $this->kills[$time] ?? -1;
    }

    public function getDeaths(string $time): int
    {
        return $this->deaths[$time] ?? -1;
    }

    public function setRank(string $rank): void
    {
        $this->rank = $rank;
    }

    public function setOP(int $op): void
    {
        $this->op = $op;
    }

    public function setExtraRankedGames(int $games): void
    {
        $this->extraGames = $games;
    }

    public function setCoins(int $coins): void
    {
        $this->coins = $coins;
    }

    public function setRankedGames(int $games): void
    {
        $this->rankedGames = $games;
    }

    public function addKill(): void
    {
        foreach (self::TIME_FRAMES as $time) {
            $this->kills[$time]++;
        }
    }

    public function addDeath(): void
    {
        foreach (self::TIME_FRAMES as $time) {
            $this->deaths[$time]++;
        }
    }

	/** @return array{int<-1, max>, int<-1, max>} */
    public function getEloAndGames(string $ladder): array{
        $elo = $this->elo[$ladder] ?? -1;
        $games = $this->games[$ladder] ?? -1;

        return [$elo, $games];
    }

    public function getParkour(string $time): float
    {
        return $this->parkour[$time] ?? -1;
    }

    public function setParkour(float $time): void
    {
        foreach (self::TIME_FRAMES as $frame) {
            if ($this->parkour[$frame] > $time) $this->parkour[$frame] = $time;
        }
    }

	/** @return array<int, Item> */
    public function getKit(string $name): array
    {
        return $this->kits[$name] ?? [];
    }

    /** @return array<int, Item> */
    public function setKit(string $name, array $contents): array
    {
        return $this->kits[$name] = $contents;
    }

    public function getSettings(): Settings
    {
        return $this->settings;
    }

    public function setBanned(?array $banned): void
    {
        $this->banned = $banned;
    }

    public function getBanned(): ?array
    {
        return $this->banned;
    }

    public function setMuted(?array $muted): void
    {
        $this->muted = $muted;
    }

    public function getMuted(): ?array
    {
       return $this->muted;
    }
}