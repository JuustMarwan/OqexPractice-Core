<?php

namespace xSuper\OqexPractice\commands\defaults;

use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\BaseCommand;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\exception\ArgumentOrderException;
use pocketmine\command\CommandSender;
use pocketmine\player\OfflinePlayer;
use pocketmine\player\Player;
use xSuper\OqexPractice\commands\arguments\PlayerArgument;
use xSuper\OqexPractice\player\PlayerSqlHelper;

class OpCommand extends BaseCommand
{
	/** @param array<array-key, mixed> $args */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof Player) {
            $sender->sendMessage('§r§cThat command can only be ran from console!');
            return;
        }

        $player = $args['player'] ?? null;
        if ($player === null) {
            $sender->sendMessage('§r§cYou have to specify a player to give operator to!');
            return;
        }

        if ($player instanceof OfflinePlayer) $name = $player->getName();
        else if ($player instanceof Player) $name = $player->getName();
        else return;
		if($player instanceof OfflinePlayer && !$player->hasPlayedBefore()){
			$sender->sendMessage('§r§cThat player has never joined the server!');
			return;
		}

        PlayerSqlHelper::setOpByName($name, true, function (bool $changed) use ($name, $sender): void
        {
            if (!$changed) {
                $sender->sendMessage('§r§cThat player is already an operator!');
                return;
            }

            $sender->sendMessage('§r§7' . $name . ' is now an operator!');
        });
    }

    /**
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->setPermission('oqex');
        $this->registerArgument(0, new PlayerArgument('player', true));
    }
}