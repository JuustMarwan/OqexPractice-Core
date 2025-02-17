<?php

namespace xSuper\OqexPractice\ffa;

use pocketmine\block\VanillaBlocks;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\GameMode;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\world\Position;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\player\settings\SettingIDS;
use xSuper\OqexPractice\utils\ItemUtils;

class BuildFFA extends FFA
{
    public function getName(): string
    {
        return 'Build';
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
        return VanillaBlocks::SANDSTONE()->asItem()->setCustomName($this->parseName([
            '§r§7Dominate your opponents by either hitting them off',
            '§r§7the map, or besting them in combat. Use your blocks',
            '§r§7and pearls to clutch up.',
            '§r',
            ' §r§8- §r§7Playing: §e' . FFA::getArena('Build')->getPlayers(),
            ' §r',
            '§r§l§aClick §r§7to join.'
        ]));
    }

	/** @param list<string> $data */
    public function parseName(array $data): string
    {
        $s = '§r§l§6Build';
        foreach ($data as $line) {
            $s .= "\n" . $line;
        }
        return $s;
    }

    public function getMap(): string
    {
        return 'BuildFFA';
    }

    public function fallDamage(): bool
    {
        return false;
    }

    public function getSpawn(): Position
    {
        $spawns = [[298.5, 88, 294.5], [287.5, 88, 283.5], [298.5, 88, 272.5], [309.5, 88, 283.5], [373.5, 88, 375.5], [363.5, 88, 365.5], [352.5, 88, 376.5], [363.5, 88, 387.5]];
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
        $player->setGamemode(GameMode::SURVIVAL());
        $player->getArmorInventory()->setHelmet(ItemUtils::enchant(VanillaItems::IRON_HELMET()->setUnbreakable(), [VanillaEnchantments::PROTECTION()], [2]));
        $player->getArmorInventory()->setChestplate(ItemUtils::enchant(VanillaItems::IRON_CHESTPLATE()->setUnbreakable(), [VanillaEnchantments::PROTECTION()], [2]));
        $player->getArmorInventory()->setLeggings(ItemUtils::enchant(VanillaItems::IRON_LEGGINGS()->setUnbreakable(), [VanillaEnchantments::PROTECTION()], [2]));
        $player->getArmorInventory()->setBoots(ItemUtils::enchant(VanillaItems::IRON_BOOTS()->setUnbreakable(), [VanillaEnchantments::PROTECTION()], [2]));
        $player->getInventory()->setContents([
            0 => ItemUtils::enchant(VanillaItems::GOLDEN_SWORD()->setUnbreakable(), [VanillaEnchantments::SHARPNESS()], [1]),
            1 => ItemUtils::enchant(VanillaItems::IRON_PICKAXE()->setUnbreakable(), [VanillaEnchantments::EFFICIENCY()], [1]),
            2 => VanillaBlocks::SANDSTONE()->asItem()->setCount(64),
            3 => VanillaItems::GOLDEN_APPLE(),
            4 => VanillaItems::ENDER_PEARL(),
        ]);

        $player->setCanBeDamaged(true);
    }

    public function doLeave(?PracticePlayer $killer, PracticePlayer $player): void
    {
        if (in_array($player->getName(), $this->dead, true)) return;

        $this->dead[] = $player->getName();

        if ($killer !== null) {
            $killer->addKill();
            $killer->setHealth($killer->getMaxHealth());
            $killer->getArmorInventory()->setHelmet(ItemUtils::enchant(VanillaItems::IRON_HELMET()->setUnbreakable(), [VanillaEnchantments::PROTECTION()], [2]));
            $killer->getArmorInventory()->setChestplate(ItemUtils::enchant(VanillaItems::IRON_CHESTPLATE()->setUnbreakable(), [VanillaEnchantments::PROTECTION()], [2]));
            $killer->getArmorInventory()->setLeggings(ItemUtils::enchant(VanillaItems::IRON_LEGGINGS()->setUnbreakable(), [VanillaEnchantments::PROTECTION()], [2]));
            $killer->getArmorInventory()->setBoots(ItemUtils::enchant(VanillaItems::IRON_BOOTS()->setUnbreakable(), [VanillaEnchantments::PROTECTION()], [2]));
            $killer->getInventory()->setContents([
                0 => ItemUtils::enchant(VanillaItems::GOLDEN_SWORD()->setUnbreakable(), [VanillaEnchantments::SHARPNESS()], [1]),
                1 => ItemUtils::enchant(VanillaItems::IRON_PICKAXE()->setUnbreakable(), [VanillaEnchantments::EFFICIENCY()], [1]),
                2 => VanillaBlocks::SANDSTONE()->asItem()->setCount(64),
                3 => VanillaItems::GOLDEN_APPLE(),
                4 => VanillaItems::ENDER_PEARL(),
            ]);
            foreach ($this->getSpawn()->getWorld()->getPlayers() as $p) {
                if ($p instanceof PracticePlayer && $p->isLoaded() && $p->getData()->getSettings()->asBool(SettingIDS::KILL_MESSAGE)) $p->sendMessage('§r§l§bBUILD §r§8» §r§7' . $player->getName() . ' was knocked off the edge by ' . $killer->getName());
            }
        } else {
            foreach ($this->getSpawn()->getWorld()->getPlayers() as $p) {
                if ($p instanceof PracticePlayer && $p->isLoaded() && $p->getData()->getSettings()->asBool(SettingIDS::KILL_MESSAGE)) $p->sendMessage('§r§l§bBUILD §r§8» §r§7' . $player->getName() . ' fell off the edge');
            }
        }

        $player->addDeath();

        OqexPractice::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player): void {
            unset($this->dead[array_search($player->getName(), $this->dead, true)]);
        }), 3);
    }
}