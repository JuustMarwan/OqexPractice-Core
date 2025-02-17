<?php

namespace xSuper\OqexPractice\commands\defaults;

use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\args\IntegerArgument;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\BaseCommand;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\exception\ArgumentOrderException;
use pocketmine\command\CommandSender;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\player\Player;
use xSuper\OqexPractice\commands\arguments\ArgumentUtils;
use xSuper\OqexPractice\commands\arguments\EnchantArgument;
use xSuper\OqexPractice\commands\arguments\PlayerArgument;
use xSuper\OqexPractice\player\PracticePlayer;

class EnchantCommand extends BaseCommand
{
	/** @param array<array-key, mixed> $args */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof PracticePlayer) {
            if (!$sender->isLoaded()) return;
            if (!$sender->getData()->isOP()) {
                $sender->sendMessage('§r§cYou do not have permission to run this command!');
                return;
            }
        }

        $oEnchant = $args['enchantment'] ?? null;
        $enchant = null;

        if ($oEnchant !== null) {
            $enchant = ArgumentUtils::enchantMap()[$oEnchant] ?? null;
        }

        if (!$enchant instanceof Enchantment) {
            $sender->sendMessage('§r§cYou need to specify a valid enchantment!');
            return;
        }

        $player = $args['player'] ?? null;

        if ($sender instanceof ConsoleCommandSender && $player === null) {
            $sender->sendMessage('§r§cYou need to specify a player!');
            return;
        }

        if ($sender instanceof Player && $player === null) $player = $sender;

        if (!$player instanceof Player) {
            $sender->sendMessage('§r§cThat player is not online!');
            return;
        }

        $level = $args['level'] ?? 1;

        if ($level > $enchant->getMaxLevel()) {
            $sender->sendMessage('§r§cThe max level for that enchantment is ' . $enchant->getMaxLevel() . '!');
            return;
        }

        $player->getInventory()->getItemInHand()->addEnchantment(new EnchantmentInstance($enchant, $level));
        $sender->sendMessage('§r§7Successfully added the ' . $oEnchant . ' enchantment to ' . $player->getName() . "'s" . ' held item!');
    }

    /**
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->setPermission('oqex');
        $this->registerArgument(0, new EnchantArgument('enchantment', true));
        $this->registerArgument(1, new PlayerArgument('player', true));
        $this->registerArgument(2, new IntegerArgument('level', true));
    }
}