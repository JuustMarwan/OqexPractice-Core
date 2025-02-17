<?php

namespace xSuper\OqexPractice\commands\defaults;

use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\args\IntegerArgument;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\BaseCommand;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\exception\ArgumentOrderException;
use pocketmine\command\CommandSender;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\entity\effect\Effect;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use xSuper\OqexPractice\commands\arguments\ArgumentUtils;
use xSuper\OqexPractice\commands\arguments\EffectArgument;
use xSuper\OqexPractice\commands\arguments\PlayerArgument;
use xSuper\OqexPractice\player\PracticePlayer;

class EffectCommand extends BaseCommand
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

        $oEffect = $args['effect'] ?? null;
        $effect = null;
        if ($oEffect !== null && $oEffect !== 'clear') {
            $effect = ArgumentUtils::effectMap()[$oEffect] ?? null;
        }

        if (!$effect instanceof Effect && $oEffect !== 'clear') {
            $sender->sendMessage('§r§cYou need to specify a valid effect!');
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
        $duration = $args['duration'] ?? 60;

        if ($level - 1 > 255) {
            $sender->sendMessage('§r§cThe max level for effects is 255!');
            return;
        }

        if ($duration * 20 > 2147483647) {
            $sender->sendMessage('§r§cThe max duration for effects is 68 years! (2147483647 seconds)');
            return;
        }



        if ($oEffect === 'clear') {
            $player->getEffects()->clear();
            $sender->sendMessage('§r§7Successfully cleared all of ' . $player->getName() . "'s" . ' effects!');
        } else{
            $player->getEffects()->add(new EffectInstance($effect ??
				throw new AssumptionFailedError('This should not be null at this point'),
				$duration * 20, $level - 1));
            $sender->sendMessage('§r§7Successfully gave ' . $player->getName() . ' the ' . $oEffect . ' effect!');
        }
    }

    /**
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->setPermission('oqex');
        $this->registerArgument(0, new EffectArgument('effect', true));
        $this->registerArgument(1, new PlayerArgument('player', true));
        $this->registerArgument(2, new IntegerArgument('level', true));
        $this->registerArgument(3, new IntegerArgument('duration', true));
    }
}