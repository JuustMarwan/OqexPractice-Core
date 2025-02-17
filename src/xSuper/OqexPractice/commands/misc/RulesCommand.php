<?php

declare(strict_types=1);

namespace xSuper\OqexPractice\commands\misc;




use pocketmine\command\CommandSender;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\BaseCommand;

class RulesCommand extends BaseCommand {

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $sender->sendMessage("§r§u§lServer Rules");
        $sender->sendMessage("");
        $sender->sendMessage("§r§l§gGeneral Rules:");
        $sender->sendMessage("§r§cNo Game Modifications: Please do not use any modifications, hacks, or exploits that give you an unfair advantage.");
        $sender->sendMessage("§r§cNo Over-the-Top Toxicity: Please be respectful to other players. Avoid using offensive language or engaging in personal attacks.");
        $sender->sendMessage("§r§cNo Spamming: Please do not spam chat or repeatedly use commands.");
        $sender->sendMessage("§r§cNo Advertising: Please do not advertise other servers or products.");
        $sender->sendMessage("§r§cNo Impersonation: Please do not impersonate other players or staff members.");
        $sender->sendMessage("§r");
        $sender->sendMessage("§r§g§lScamming and Server Interactions:");
        $sender->sendMessage("§r§cNo Stealing: Please do not steal items from other players lockers.");
        $sender->sendMessage("§r§cNo Hacking: Please do not use any tools or methods to gain unauthorized access to other players' accounts or inventories.");
        $sender->sendMessage("§r");
        $sender->sendMessage("§r§l§gGameplay:");
        $sender->sendMessage("§r§cNo Game Modifications: Please do not use any cheats or exploits to gain an unfair advantage in gameplay.");
        $sender->sendMessage("§r");
        $sender->sendMessage("§r§r§g§lStaff:");
        $sender->sendMessage("§r§cRespect Staff: Please be respectful to all staff members.");
        $sender->sendMessage("§r§cFollow Staff Instructions: Please follow the instructions of staff members.");
        $sender->sendMessage("§r§cDo Not Abuse Staff: Please do not abuse the staff system or report players falsely.");
        $sender->sendMessage("§r");
        $sender->sendMessage("§r§g§lViolations:");
        $sender->sendMessage("§r§cViolations of these rules may result in warnings, temporary bans, or permanent bans.");
        $sender->sendMessage("§r§cStaff decisions are final.");
    }

    public function prepare(): void
    {
        $this->setPermission('oqex');
    }
}