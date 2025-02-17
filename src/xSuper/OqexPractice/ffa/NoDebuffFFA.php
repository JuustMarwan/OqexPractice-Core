<?php

namespace xSuper\OqexPractice\ffa;

use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\PotionType;
use pocketmine\item\VanillaItems;
use pocketmine\player\GameMode;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\world\Position;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\player\settings\SettingIDS;

class NoDebuffFFA extends FFA
{
    public function fallDamage(): bool
    {
        return false;
    }

    public function getName(): string
    {
        return 'NoDebuff';
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
        return VanillaItems::SPLASH_POTION()->setType(PotionType::HEALING())->setCustomName($this->parseName([
            '§r§7NoDebuff is a game mode where you need to',
            '§r§7fight and use the splash potions given to you',
            '§r§7to heal yourself and prevent yourself from dying.'
        ]))->setLore([
            ' §r§8- §r§7Playing: §e' . FFA::getArena('NoDebuff')->getPlayers(),
            ' §r',
            '§r§l§aClick §r§7to join.'
        ]);
    }

	/** @param list<string> $data */
    public function parseName(array $data): string
    {
        $s = '§r§l§6NoDebuff';
        foreach ($data as $line) {
            $s .= "\n" . $line;
        }
        return $s;
    }

    public function getMap(): string
    {
        return 'NoDebuffFFA';
    }

    public function getSpawn(): Position
    {
        $spawns = [[931, 57, 771], [1072, 61, 1030]];

        $x = mt_rand($spawns[0][0], $spawns[1][0]);
        $y = mt_rand($spawns[0][1], $spawns[1][1]);
        $z = mt_rand($spawns[0][2], $spawns[1][2]);

        return new Position($x, $y, $z, Server::getInstance()->getWorldManager()->getWorldByName($this->getMap()));
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

        $player->giveKit('NoDebuff', true);
        $player->setCanBeDamaged(true);
    }

    public function doLeave(?PracticePlayer $killer, PracticePlayer $player): void
    {
        if (in_array($player->getName(), $this->dead, true)) return;

        $this->dead[] = $player->getName();

        if ($killer !== null && $killer->isLoaded() && $killer->isOnline()) {
            $player->addDeath();
            $killer->addKill();
            $killer->setHealth($killer->getMaxHealth());

            foreach ($this->getSpawn()->getWorld()->getPlayers() as $p) {
                if ($p instanceof PracticePlayer && $p->isLoaded() && $p->getData()->getSettings()->asBool(SettingIDS::KILL_MESSAGE)) {
                    $kPots = 0;
                    foreach ($killer->getInventory()->getContents() as $c) if ($c->getTypeId() === ItemTypeIds::SPLASH_POTION) $kPots++;

                    $pPots = 0;
                    foreach ($player->getInventory()->getContents() as $c) if ($c->getTypeId() === ItemTypeIds::SPLASH_POTION) $pPots++;

                    $p->sendMessage('§r§l§bNO-DEBUFF §r§8» §r§7' . $player->getName() . " ($pPots) was killed by " . $killer->getName() . "($kPots)");
                }
            }

            if ($killer->getFFA() instanceof NoDebuffFFA) $killer->giveKit('NoDebuff', true);
        } else {
            foreach ($this->getSpawn()->getWorld()->getPlayers() as $p) {
                if ($p instanceof PracticePlayer && $p->isLoaded() && $p->getData()->getSettings()->asBool(SettingIDS::KILL_MESSAGE)) $p->sendMessage('§r§l§bNO-DEBUFF §r§8» §r§7' . $player->getName() . ' died');
            }
        }

        OqexPractice::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player): void {
            unset($this->dead[array_search($player->getName(), $this->dead, true)]);
        }), 3);
    }
}