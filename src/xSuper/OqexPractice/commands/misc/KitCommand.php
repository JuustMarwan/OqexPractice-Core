<?php

namespace xSuper\OqexPractice\commands\misc;

use xSuper\OqexPractice\commands\arguments\KitTypeArgument;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use xSuper\OqexPractice\player\kit\Kit;
use xSuper\OqexPractice\player\PracticePlayer;

class KitCommand extends BaseCommand
{
    protected function prepare(): void
    {
        $this->setPermission('oqex');
        $this->registerArgument(0, new KitTypeArgument('kit', true));
    }

    /** @param array<array-key, mixed> $args */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof PracticePlayer || !$sender->isLoaded()) return;

        if ($sender->getDuel() !== null || $sender->getFFA() !== null || $sender->getEvent() !== null) {
            $sender->sendMessage('§r§cYou can only edit kits at spawn!');
            return;
        }

        $kit = $args['kit'] ?? null;
        if ($kit === null || ($k = Kit::getKit($kit)) === null) {
            $sender->sendMessage('§r§cPlease specify a valid kit!');
            return;
        }

        if ($sender->editKit !== null) {
            $sender->sendMessage('§r§cPlease stop editing your current kit!');
            return;
        }

        $name = $k->getName();

        $sender->editKit = $name;
        $sender->giveKit($name);
        $sender->sendMessage("\n§r§l§6Editing Kit: $name\n\n§r§7Type §l§cCANCEL §r§for §l§aSAVE");
    }
}