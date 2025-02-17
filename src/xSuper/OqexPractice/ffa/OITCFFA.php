<?php

namespace xSuper\OqexPractice\ffa;

use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\VanillaItems;
use pocketmine\player\GameMode;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\world\Position;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\player\settings\SettingIDS;
use xSuper\OqexPractice\utils\ItemUtils;

class OITCFFA extends FFA
{
    public function getName(): string
    {
        return 'OITC';
    }

    public function getHorizontalKnockBack(): float
    {
        return $this->getKB()['xzKb'];
    }

    public function getVerticalKnockBack(): float
    {
        return $this->getKB()['yKb'];
    }

    public function getMenuItem(): Item
    {
        return VanillaItems::BOW()->setCustomName($this->parseName([
            '§r§7One In The Chamber is a gamemode where you spawn',
            '§r§7in a large map. Arrows will one-shot you and will',
            '§r§7regenerate every 5 seconds, or on kill.',
            '§r',
            ' §r§8- §r§7Playing: §e' . FFA::getArena('OITC')->getPlayers(),
            ' §r',
            '§r§l§aClick §r§7to join.'
        ]));
    }

	/** @param list<string> $data */
    public function parseName(array $data): string
    {
        $s = '§r§l§6OITC';
        foreach ($data as $line) {
            $s .= "\n" . $line;
        }
        return $s;
    }

    public function getMap(): string
    {
        return 'OITCFFA';
    }

    public function fallDamage(): bool
    {
        return false;
    }

    public function getSpawn(): Position
    {
        $spawns = [
            [49, -46, -44],
            [41, -46, -27],
            [54, -52, -41],
            [35, -35, -42],
            [40, -47, -58],
            [20, -47, -62],
            [19, -47, -30],
            [18, -34, -22],
            [2, -35, -28],
            [3, -47, -31],
            [-17, -50, -34],
            [-5, -49, -53],
            [-18, -51, -44]
        ];

        $world = Server::getInstance()->getWorldManager()->getWorldByName($this->getMap());

        $vec = self::getBestSpawn($world, $spawns);


        return new Position($vec->x, $vec->y, $vec->z, Server::getInstance()->getWorldManager()->getWorldByName($this->getMap()));
    }

    public function doJoin(PracticePlayer $player): void
    {
        $player->extinguish();
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getCursorInventory()->clearAll();
        $player->getOffHandInventory()->clear(0);
        $player->getHungerManager()->setFood($player->getHungerManager()->getMaxFood());
        $player->setHealth($player->getMaxHealth());
        $player->getEffects()->clear();
        $player->setAbsorption(0);
        $player->setGamemode(GameMode::ADVENTURE());
        $player->getInventory()->setContents([
            0 => ItemUtils::enchant(VanillaItems::STONE_SWORD()->setUnbreakable(), [VanillaEnchantments::UNBREAKING()], [10]),
            1 => ItemUtils::enchant(VanillaItems::BOW()->setUnbreakable(), [VanillaEnchantments::POWER()], [10]),
            8 => VanillaItems::ARROW(),
        ]);

        $player->setCanBeDamaged(true);
        $player->setNameTagAlwaysVisible(false);
        $player->setNameTagVisible(false);
    }

    public function doLeave(?PracticePlayer $killer, PracticePlayer $player): void
    {
        if (in_array($player->getName(), $this->dead, true)) return;

        $this->dead[] = $player->getName();

        if ($killer !== null) {
            $player->addDeath();
            $killer->addKill();
            $a = false;
            $killer->setHealth($killer->getMaxHealth());
            foreach ($killer->getInventory()->getContents(false) as $i) {
                if ($i->getTypeId() === ItemTypeIds::ARROW) $a = true;
            }
            if ($killer->getOffHandInventory()->getItem(0)->getTypeId() === ItemTypeIds::ARROW) $a = true;

            if (!$a && $killer->getFFA() instanceof OITCFFA) $killer->getInventory()->setItem(8, VanillaItems::ARROW());
            $killer->rmArrow();
            foreach ($this->getSpawn()->getWorld()->getPlayers() as $p) {
                if ($p instanceof PracticePlayer && $p->isLoaded() && $p->getData()->getSettings()->asBool(SettingIDS::KILL_MESSAGE)) $p->sendMessage('§r§l§bOITC §r§8» §r§7' . $player->getName() . ' was sniped by ' . $killer->getName());
            }
        } else {
            foreach ($this->getSpawn()->getWorld()->getPlayers() as $p) {
                if ($p instanceof PracticePlayer && $p->isLoaded() && $p->getData()->getSettings()->asBool(SettingIDS::KILL_MESSAGE)) $p->sendMessage('§r§l§bOITC §r§8» §r§7' . $player->getName() . ' died');
            }
        }

        OqexPractice::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player): void {
            unset($this->dead[array_search($player->getName(), $this->dead, true)]);
        }), 3);
    }
}