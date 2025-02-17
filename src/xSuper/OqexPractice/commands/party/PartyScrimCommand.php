<?php

namespace xSuper\OqexPractice\commands\party;

use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use xSuper\OqexPractice\commands\arguments\PlayerArgument;
use xSuper\OqexPractice\party\Party;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\player\settings\SettingIDS;
use xSuper\OqexPractice\ui\form\Forms;
use xSuper\OqexPractice\ui\menu\duel\DuelRequestMenu;
use xSuper\OqexPractice\ui\menu\Menus;

class PartyScrimCommand extends BaseSubCommand
{
	/** @param array<array-key, mixed> $args */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof PracticePlayer) {
            $player = $args['player'] ?? null;

            if ($player === null) {
                $sender->sendMessage('§r§cYou need to specify a player!');
                return;
            }

            if (!$player instanceof PracticePlayer || !$player->isOnline()) {
                $sender->sendMessage('§r§cThat player is not online!');
                return;
            }

            if ($sender->getParty() === null) {
                $sender->sendMessage('§r§cYou are not in a party!');
                return;
            }

            $oParty = $player->getParty();

            if ($oParty === null) {
                $sender->sendMessage('§r§cThat player is not in a party!');
                return;
            }

            $party = Party::getParty($sender->getParty());
            if ($party === null) return;

            if ($oParty->getId() === $party->getId()) {
                $player->sendMessage('§r§cYou can not send a scrim invite to your party!');
                return;
            }

            if ($party->getOwner() !== $sender->getUniqueId()->toString()) {
                $sender->sendMessage('§r§cYou are not the party owner!');
                return;
            }

            if ($party->hasScrimRequest($player)) {
                $party->acceptScrimRequest($player);
                return;
            }

            if($player->getData()->getSettings()->getSetting(SettingIDS::UI_TYPE) === SettingIDS::UI_TYPE_CHEST){
                Menus::DUEL_REQUEST()->create($sender, ['recipient' => $oParty]);
            }else{
                $player->sendForm(Forms::DUEL_REQUEST()->create($sender, $oParty));
            }
            return;
        }

        $sender->sendMessage('§r§cThis command can only be ran in-game!');
    }

    protected function prepare(): void
    {
        $this->setPermission('oqex');
        $this->registerArgument(0, new PlayerArgument('player', true));
    }
}