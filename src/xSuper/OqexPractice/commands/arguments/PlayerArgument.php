<?php

namespace xSuper\OqexPractice\commands\arguments;

use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\args\BaseArgument;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\player\IPlayer;
use pocketmine\Server;
use pocketmine\utils\AssumptionFailedError;

class PlayerArgument extends BaseArgument
{

    public function getNetworkType(): int
    {
        return AvailableCommandsPacket::ARG_TYPE_TARGET;
    }

    public function getTypeName(): string
    {
        return 'player';
    }

    public function canParse(string $testString, CommandSender $sender): bool
    {
        return (bool) preg_match("/^(?!rcon|console)[a-zA-Z0-9_ ]{1,16}$/i", $testString);
    }

    public function parse(string $argument, CommandSender $sender): IPlayer
    {
        return Server::getInstance()->getOfflinePlayer($argument) ?? throw new AssumptionFailedError('This should not return null');
    }
}