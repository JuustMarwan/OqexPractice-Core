<?php

namespace xSuper\OqexPractice\duel\special;

use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\GameMode;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\CancelTaskException;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\world\Position;
use xSuper\OqexPractice\duel\Duel;
use xSuper\OqexPractice\duel\generator\MapGenerator;
use xSuper\OqexPractice\duel\generator\maps\SurvivalGamesMap;
use xSuper\OqexPractice\utils\ItemUtils;
use xSuper\OqexPractice\utils\scoreboard\Scoreboards;

class SurvivalGamesDuel extends Duel
{
    private const PVP_TIME = 15;

    public function init(PluginBase $plugin): void
    {
        /** @var SurvivalGamesMap $map */
        $map = $this->map;

        MapGenerator::genMap($plugin, $plugin->getServer()->getDataPath(), $this->getWorldName(), $plugin->getServer()->getDataPath() . '/worlds/' . $this->getWorldName(), function() use ($plugin, $map): void{
			if(!$plugin->getServer()->getWorldManager()->loadWorld($this->getWorldName())){
				foreach($this->getPlayers() as $player){
					$player->sendMessage('§r§cYour duel failed to generate a map, if you believe this is an error please contact a staff member!');
				}

				$this->end($plugin);
				return;
			}

            $world = $plugin->getServer()->getWorldManager()->getWorldByName($this->getWorldName());
            $world->setTime(6000);
            $world->stopTime();

			foreach($this->getPlayers() as $i => $player){
				$player->setDuel($this);
				$player->extinguish();
				$player->getInventory()->clearAll();
				$player->getArmorInventory()->clearAll();
				$player->showPlayer($this->opposite($player));
				$player->getCursorInventory()->clearAll();
				$player->getHungerManager()->setFood($player->getHungerManager()->getMaxFood());
				$player->setHealth($player->getMaxHealth());
				$player->getEffects()->clear();
				$player->setAbsorption(0);
				$player->setGamemode(GameMode::SURVIVAL());
				$player->setNoClientPredictions();
				Scoreboards::DUEL()->send($player);

				if($i === 0) $cords = $map->getSpawn1();
				else $cords = $map->getSpawn2();

				$pos = new Position($cords->getX(), $cords->getY(), $cords->getZ(), $world);
				$player->preTeleport($pos);
			}

			$this->countdown($plugin);

		}, $plugin->getDataFolder() . 'maps/' . $map->getRealName());
    }

    public function start(PluginBase $plugin): void
    {
        $this->started = true;

        foreach ($this->getPlayers() as $player) {
            $player->setNoClientPredictions(false);
        }

        $plugin->getScheduler()->scheduleRepeatingTask(new ClosureTask(function () use ($plugin): void {
            if ($this->time >= self::MAX_TIME) {
                $this->end($plugin);
                throw new CancelTaskException();
            }

            if ($this->time === self::PVP_TIME) {
                foreach ($this->getPlayers() as $p) {
                    if ($p->isOnline()) {
                        $p->setCanBeDamaged(true);
                        $p->sendTitle('§l§cPVP ENABLED');
                    }
                }
            }

            if ($this->time === self::PVP_TIME + 2) {
                foreach ($this->getPlayers() as $p) {
                    if ($p->isOnline()) {
                        $p->sendTitle('§l');
                    }
                }
            }

            if ($this->ended) {
                throw new CancelTaskException();
            }

            $e = false;

            foreach ($this->getPlayers() as $p) {
                if (!$p->isOnline() && $this->winner === null) {
                    $e = true;
                    $oP = $this->opposite($p);
                    if ($oP->isOnline()) $this->winner = $oP;
                } else {
                    Scoreboards::DUEL()->send($p);
                }
            }


            if ($e) {
                $this->end($plugin);
                throw new CancelTaskException();
            }

            $this->time++;
        }), 20);
    }

    protected function getWorldName(): string
    {
        return 'survivalGames_' . $this->id;
    }

	/** @return list<Item> */
    public static function lootPool(Position $position, SurvivalGamesDuel $duel): array
    {
        $map = $duel->getMap();
        /** @var SurvivalGamesMap $map */
        $middle = $map->getMiddle();
        $dist = $position->distance($middle);

        $low = $map->getLow();
        $mid = $map->getMid();
        $high = $map->getHigh();

        if ($dist <= $low) $t = 0;
        else if ($dist > $low && $dist <= $mid) $t = 1;
        else if ($dist > $mid && $dist <= $high) $t = 2;
        else $t = 3;

        $count = rand(2, 4);

        $pool = self::pools($t);
        $p = [];

        if ($t === 3) return [$pool[rand(0, count($pool) - 1)]];

		$keys = array_rand($pool, $count);
		if(!is_array($keys)){
			throw new AssumptionFailedError('This should never happen');
		}
        foreach ($keys as $key) {
            $p[] = $pool[$key];
        }

        return $p;
    }

	/** @return list<Item> */
    private static function pools(int $type): array
    {
        switch ($type) {
            case 0:
                return [
                    VanillaItems::STONE_AXE(),
                    VanillaItems::STONE_SWORD(),
                    VanillaItems::LEATHER_TUNIC(),
                    VanillaItems::LEATHER_PANTS(),
                    VanillaItems::CHAINMAIL_BOOTS(),
                    VanillaItems::CHAINMAIL_HELMET(),
                    VanillaItems::CHAINMAIL_LEGGINGS(),
                    VanillaItems::CHAINMAIL_CHESTPLATE(),
                    VanillaItems::BOW(),
                    VanillaItems::ARROW()->setCount(32)
                ];
            case 1:
                return [
                    VanillaItems::IRON_SWORD(),
                    VanillaItems::DIAMOND_AXE(),
                    VanillaItems::IRON_BOOTS(),
                    VanillaItems::IRON_HELMET(),
                    ItemUtils::enchant(VanillaItems::FISHING_ROD(), [VanillaEnchantments::FLAME()], [10])->setCustomName("§r§l§Super's Hot Catch")->setLore([
                        '§r§l§cThis is xSupers fishing',
                        '§r§l§cRod! Use at your own risk!',
                        '',
                        '§o§l§csizzle crack pop'

                    ]),
                    VanillaItems::FLINT_AND_STEEL(),
                    VanillaItems::LAVA_BUCKET()
                ];
            case 2:
                return [
                    ItemUtils::enchant(VanillaItems::GOLDEN_AXE(), [VanillaEnchantments::SHARPNESS(), VanillaEnchantments::UNBREAKING()], [5, 10])->setCustomName("§r§l§6Founders Axe")->setLore([
                        '§r§l§aThis Axe is owned by The',
                        '§r§l§aFounder of the server, JoshyM44!',
                        '',
                        "§r§l§aDon't loose it or he will be mad!"
                    ]),
                    VanillaItems::DIAMOND_SWORD(),
                    VanillaItems::IRON_CHESTPLATE(),
                    VanillaItems::IRON_LEGGINGS(),
                    VanillaItems::DIAMOND_BOOTS(),
                    VanillaItems::DIAMOND_HELMET(),
                    ItemUtils::enchant(VanillaItems::BOW(), [VanillaEnchantments::POWER()], [2]),
                    VanillaItems::GOLDEN_APPLE()->setCount(rand(2, 4)),
                    VanillaItems::FLINT_AND_STEEL(),
                    VanillaItems::ARROW()->setCount(64)
                ];
            case 3:
                return [
                    ItemUtils::enchant(VanillaItems::IRON_SWORD(), [VanillaEnchantments::SHARPNESS()], [2])->setCustomName('§r§l§bSuper Slayer')->setLore([
                        '§r§l§bThis Sword is named after the',
                        '§r§l§bCo-Founder and Developer of the server, xSuper!',
                        '',
                        '§r§l§This is the best Sword in the game!'
                    ]),
                    ItemUtils::enchant(VanillaItems::DIAMOND_HELMET(), [VanillaEnchantments::PROTECTION(), VanillaEnchantments::UNBREAKING()], [1, 3])->setCustomName('§rt§l§4Founders Crown')->setLore([
                        '§r§l§cThis Crown is named',
                        '§r§l§cafter the Founder of the server, JoshyM44!',
                        '',
                        '§r§l§cThis is the best Helmet in the game!'
                    ]),
                    ItemUtils::enchant(VanillaItems::DIAMOND_CHESTPLATE(), [VanillaEnchantments::UNBREAKING()], [3])->setCustomName('§r§l§4Founders Robe-set')->setLore([
                        '§r§l§cThis Robe-set is named',
                        '§r§l§cafter the Founder of the server, JoshyM44!',
                        '',
                        '§r§l§cThis is the best Chestplate in the game!'
                    ]),
                    ItemUtils::enchant(VanillaItems::DIAMOND_LEGGINGS(), [VanillaEnchantments::UNBREAKING()], [3])->setCustomName('§r§l§4Founders Platoons')->setLore([
                        '§r§l§cThese Platoons are named',
                        '§r§l§cafter the Founder of the server, JoshyM44!',
                        '',
                        '§r§l§cThese are the best Leggings in the game!'
                    ]),
                    ItemUtils::enchant(VanillaItems::DIAMOND_BOOTS(), [VanillaEnchantments::PROTECTION(), VanillaEnchantments::UNBREAKING()], [1, 3])->setCustomName('§r§l§4Founders Slippers')->setLore([
                        '§r§l§cThese Slippers are named',
                        '§r§l§cafter the Founder of the server, JoshyM44!',
                        '',
                        '§r§l§cThese are the best Pair of Boots in the game!'
                    ]),
                    ItemUtils::enchant(VanillaItems::SNOWBALL(), [VanillaEnchantments::THORNS()], [10])->setCustomName("§r§l§9Founders Freeze")->setLore([
                        '§r§l§bPack an extra punch with this',
                        '§r§l§bitem! It is very powerful and used',
                        '§r§l§bby the Founder of the server JoshyM44!',
                        '',
                        '§r§l§9Brrrrrrrrrrrrrr'
                    ])
                ];
        }

        return [];
    }
}