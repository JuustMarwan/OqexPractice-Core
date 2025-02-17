<?php

namespace xSuper\OqexPractice\commands\misc;

use pocketmine\plugin\PluginBase;
use xSuper\OqexPractice\commands\arguments\OfflinePlayerArgument;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use xSuper\OqexPractice\player\PlayerSqlHelper;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\ui\menu\Menus;

class StatsCommand extends BaseCommand
{
    public function __construct(private array $values, PluginBase $plugin, string $name, string $description = "", array $aliases = [])
    {
        parent::__construct($plugin, $name, $description, $aliases);
    }

    /** @param array<array-key, mixed> $args */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof PracticePlayer) {
            if (!$sender->isLoaded()) return;

            $p = $args['player'] ?? null;

            if ($p === null) {
                $p = $sender->getName();
            }

            PlayerSqlHelper::getStatsByLowerName($p, function (?string $name, array $stats) use ($sender, $args): void {
                if ($name === null) {
                    $sender->sendMessage('§r§l§c(!) §r§cThat player has never joined the server!');
                    return;
                }

                if ($sender->isOnline()) {
                    $sender->sendMessage('§r§aDone loading ' . $name . "'s stats!");
                    Menus::PLAYER_STATS()->create($sender, ['target' => $name, 'stats' => $stats]);
                }
            });
        }
    }

    protected function prepare(): void
    {
        $this->registerArgument(0, new OfflinePlayerArgument('player', $this->values, true));
        $this->setPermission('oqex');
    }
}