<?php

namespace xSuper\OqexPractice\player;

use Closure;
use DateTime;
use Generator;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Server;
use Ramsey\Uuid\UuidInterface;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\poggit\libasynql\result\SqlSelectResult;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\poggit\libasynql\SqlThread;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\SOFe\AwaitGenerator\Await;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\utils\ItemUtils;
use xSuper\OqexPractice\utils\TimeUtils;

class PlayerSqlHelper
{

    public static function create(UuidInterface $uuid, string $name, ?Closure $then = null): void
    {
        $joined = (new DateTime())->format('Y-m-d H-i-s');
		if ($then === null) $then = function (): void {};

		$uuidString = $uuid->toString();
		OqexPractice::getDatabase()->executeInsert('oqex-practice.players.create', [
			'uuid' => $uuidString,
			'username' => $name,
			'joined' => $joined,
		], $then);
    }

    public static function getStats(UuidInterface $uuid, Closure $function): void
    {
        Await::f2c(function () use ($uuid, $function): Generator {
            $stringUUID = $uuid->toString();

			/** @var array{
			 *     'lifetime': array{'kills': int<0, max>, 'deaths': int<0, max>, 'parkour': float},
			 *     'monthly': array{'kills': int<0, max>, 'deaths': int<0, max>, 'parkour': float},
			 *     'weekly': array{'kills': int<0, max>, 'deaths': int<0, max>, 'parkour': float},
			 *     'daily': array{'kills': int<0, max>, 'deaths': int<0, max>, 'parkour': float}
			 *     } $stats */
            $stats = [];
			/** @var array{
			 *      0: array{0: array{'username': string}},
			 *     	1: array{0: array{
			 *     		'lifetime': int<0, max>,
			 *     		'monthly': int<0, max>,
			 *     		'weekly': int<0, max>,
			 *     		'daily': int<0, max>
			 *         }
			 *	 	},
			 *     	2: array{0: array{
			 *     		'lifetime': int<0, max>,
			 *     		'monthly': int<0, max>,
			 *     		'weekly': int<0, max>,
			 *     		'daily': int<0, max>
			 *         }
			 *	 	},
			 *     	3: array{0: array{
			 *     		 'lifetime': float,
			 *     		 'monthly': float,
			 *     		 'weekly': float,
			 *     		 'daily': float
			 * 			}
			 *     	}
			 *     } $statsRows
			 */
			$statsRows = array_map(fn(SqlSelectResult $select) => $select->getRows(), yield from Await::promise(
				static fn(Closure $resolve, Closure $reject) => OqexPractice::getDatabase()->executeMulti(
					'oqex-practice.stats.get',
					['uuid' => $stringUUID],
					SqlThread::MODE_SELECT,
					$resolve,
					$reject
				)));
			foreach([1 => 'kills', 2 => 'deaths', 3 => 'parkour'] as $index => $stat){
				foreach (['lifetime', 'monthly', 'weekly', 'daily'] as $time) {
					$stats[$time][$stat] = $statsRows[$index][0][$time];
				}
			}

            $function($statsRows[0][0]['username'], $stats);
        });
    }

	/**
	 * @param UuidInterface $uuid
	 * @param string $duration
	 * @param string $staff
	 * @param string|null $reason
	 * @param bool $internal
	 * @param \Closure|null $function
	 *
	 * Usage: function(?string): void
	 * @throws \Exception
	 */
    public static function banIfBannedAliasExists(UuidInterface $uuid, string $duration, string $staff, ?string $reason, bool $internal = false, ?Closure $function = null): void
    {
		if (!$internal && $duration !== 'perm') $duration = (TimeUtils::stringToTimestampAdd($duration, new DateTime()))[0]->format('Y-m-d H-i-s');
		if ($reason === null) $reason = 'No reason given!';
		OqexPractice::getDatabase()->executeChange('oqex-practice.players.ban_if_banned_alias_exists', [
			'uuid' => $uuid->toString(),
			'duration' => $duration,
			'staff' => $staff,
			'reason' => $reason
		], static function(int $changedRows) use
		(
				$staff,
				$reason,
				$duration,
				$uuid,
				$function
		): void{
			if($changedRows > 0){
				$p = Server::getInstance()->getPlayerByUUID($uuid);
				if ($p !== null && $p->isOnline()) {
					if ($duration === 'perm') {
						$s = '§r§cYou are permanently banned!';
					} else {
						$to = new DateTime();
						$from = date_create_from_format('Y-m-d H-i-s', $duration);
						if ($from <= $to) return;
						else $s = '§r§cYou are banned for ' . TimeUtils::formatDate(new DateTime(), $from);
					}


					$s .= "\n" . ' §fReason: ' . $reason . ' [' . $staff . "]\n";
					$p->kick($s);
				}
			}
			if($function !== null){
				$function($changedRows > 0);
			}
		});
    }

    public static function getByLowerName(string $name, Closure $function): void
    {
		OqexPractice::getDatabase()->executeSelect('oqex-practice.players.fetch_lowercase', ['username' => $name], function (array $rows)  use ($function): void {
			$player = $rows[0] ?? null;

			$function($player);
		});
    }

    public static function saveKit(UuidInterface $uuid, string $name, Inventory $inventory): void
    {
        $contents = array_map(static fn(Item $item) => ItemUtils::encode($item), $inventory->getContents(true));

        OqexPractice::getDatabase()->executeInsert('oqex-practice.kits.save', [
            'uuid' => $uuid->toString(),
            'name' => $name,
            'contents' => bin2hex(igbinary_serialize($contents))
        ]);

        /** @var PracticePlayer $p */
        if (($p = Server::getInstance()->getPlayerByUUID($uuid)) instanceof PracticePlayer && $p->isLoaded()) {
            $p->getData()->setKit($name, $inventory->getContents(true));
        }
    }

    public static function unBan(string $uuid, bool $unbanAll = true): void
    {
		OqexPractice::getDatabase()->executeChange('oqex-practice.players.unban', ['uuid' => $uuid, 'includingAliases' => $unbanAll]);
    }

    public static function delete(UuidInterface $uuid): void
    {
        OqexPractice::getDatabase()->executeGeneric('oqex-practice.players.delete', ['uuid' => $uuid->toString()]);
    }

	public static function getLowercaseUsernames(Closure $then): void{
		OqexPractice::getDatabase()->executeSelect('oqex-practice.players.get_lowercase_usernames', [], static function (array $rows) use
		(
			$then
		): void{
			$then(array_column($rows, 'lowerUsername'));
		});
	}

	public static function getStatsByLowerName(string $name, Closure $function): void
	{
		Await::f2c(function () use ($name, $function): Generator {
			/** @var array{
			 *     'lifetime': array{'kills': int<0, max>, 'deaths': int<0, max>, 'parkour': float},
			 *     'monthly': array{'kills': int<0, max>, 'deaths': int<0, max>, 'parkour': float},
			 *     'weekly': array{'kills': int<0, max>, 'deaths': int<0, max>, 'parkour': float},
			 *     'daily': array{'kills': int<0, max>, 'deaths': int<0, max>, 'parkour': float}
			 *     } $stats */
			$stats = [];
			/** @var array{
			 *      0: array{0: array{'username': string}},
			 *     	1: array{0: array{
			 *     		'lifetime': int<0, max>,
			 *     		'monthly': int<0, max>,
			 *     		'weekly': int<0, max>,
			 *     		'daily': int<0, max>
			 *         }
			 *	 	},
			 *     	2: array{0: array{
			 *     		'lifetime': int<0, max>,
			 *     		'monthly': int<0, max>,
			 *     		'weekly': int<0, max>,
			 *     		'daily': int<0, max>
			 *         }
			 *	 	},
			 *     	3: array{0: array{
			 *     		 'lifetime': float,
			 *     		 'monthly': float,
			 *     		 'weekly': float,
			 *     		 'daily': float
			 * 			}
			 *     	}
			 *     } $statsRows
			 */
			$statsRows = array_map(fn(SqlSelectResult $result) => $result->getRows(), yield from Await::promise(
				static fn(Closure $resolve, Closure $reject) => OqexPractice::getDatabase()->executeMulti(
					'oqex-practice.stats.get_by_lowercase_name',
					['username' => $name],
					SqlThread::MODE_SELECT,
					$resolve,
					$reject
				)));
			foreach([1 => 'kills', 2 => 'deaths', 3 => 'parkour'] as $index => $stat){
				foreach ($statsRows[$index][0] as $time => $amount) {
					$stats[$time][$stat] = $amount;
				}
			}

			$function($statsRows[0][0]['username'] ?? null, $stats);
		});
	}

	public static function setOpByName(string $name, bool $op, Closure $function): void{
		OqexPractice::getDatabase()->executeChange('oqex-practice.players.set_op_by_name', ['username' => $name, 'op' => $op], static fn(int $changedRows) => $function($changedRows > 0));
	}

	public static function addEGamesByName(string $name, int $eGames): void{
		OqexPractice::getDatabase()->executeChange('oqex-practice.players.add_egames_by_name', ['username' => $name, 'eGames' => $eGames]);
	}

	public static function addCoinsByLowerName(string $name, int $coins): void{
		OqexPractice::getDatabase()->executeChange('oqex-practice.players.add_coins_by_lowercase_name', ['username' => $name, 'coins' => $coins]);
	}

	public static function banByLowerName(?UuidInterface $staffUuid, string $username, string $duration, string $staff, ?string $reason, ?Closure $function = null): void{
		OqexPractice::getDatabase()->executeMulti('oqex-practice.players.ban_by_lowercase_username', [
			'username' => $username,
			'duration' => $duration,
			'staff' => $staff,
			'reason' => $reason,
			'staffUuid' => $staffUuid?->toString()
		], SqlThread::MODE_SELECT, static fn(array $results) => $function($results[0]->getRows()[0]['ret']));
	}
}