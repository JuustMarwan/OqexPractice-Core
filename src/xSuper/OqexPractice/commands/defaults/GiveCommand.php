<?php

namespace xSuper\OqexPractice\commands\defaults;

use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\args\IntegerArgument;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\BaseCommand;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\exception\ArgumentOrderException;
use pocketmine\command\CommandSender;
use pocketmine\item\StringToItemParser;
use pocketmine\player\Player;
use xSuper\OqexPractice\commands\arguments\ItemArgument;
use xSuper\OqexPractice\commands\arguments\PlayerArgument;
use xSuper\OqexPractice\player\PracticePlayer;

class GiveCommand extends BaseCommand
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

		/** @var ?string $item */
        $item = $args['item'] ?? null;
        if ($item === null) {
            $sender->sendMessage('§r§cYou need to specify an item!');
            return;
        }

        if (($item = StringToItemParser::getInstance()->parse($item)) === null) {
            $sender->sendMessage('§r§cYou need to specify a valid item!');
            return;
        }

        $count = $args['count'] ?? 1;
        $max = $item->getMaxStackSize() * 36;
        if ($count < 0 || $count > $max) {
            $sender->sendMessage('§r§cThe max amount of that type of item is ' . $max . '!');
            return;
        }

        $item->setCount($count);
        $player = $args['player'] ?? null;
        if ($player === null) {
            if (!$sender instanceof Player) {
                $sender->sendMessage('§r§cYou need to specify a player!');
                return;
            }

            $player = $sender;
        }

        if (!$player instanceof Player) {
            $sender->sendMessage('§r§cThat player is not online!');
            return;
        }

        $player->getInventory()->addItem($item);
        $sender->sendMessage('§r§7You gave ' . $item->getName() . ' x' . $item->getCount() . ' to ' . $player->getName() . '!');
    }

    /**
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->setPermission('oqex');
        $this->registerArgument(0, new ItemArgument('item', true));
        $this->registerArgument(1, new IntegerArgument('count', true));
        $this->registerArgument(2, new PlayerArgument('player', true));
    }
}