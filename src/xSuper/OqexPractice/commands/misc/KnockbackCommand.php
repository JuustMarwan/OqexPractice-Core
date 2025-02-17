<?php

namespace xSuper\OqexPractice\commands\misc;

use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\args\BooleanArgument;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\args\FloatArgument;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\args\IntegerArgument;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\args\RawStringArgument;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\BaseCommand;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\exception\ArgumentOrderException;
use pocketmine\command\CommandSender;
use xSuper\OqexPractice\duel\type\Type;
use xSuper\OqexPractice\ffa\FFA;
use xSuper\OqexPractice\player\PracticePlayer;

class KnockbackCommand extends BaseCommand
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

		/** @var ?string $gamemode */
        $gamemode = $args['gamemode'] ?? null;

        if ($gamemode === null) {
            $sender->sendMessage('§r§cYou need to specify a gamemode!');
            return;
        }

        $ffa = null;
        $type = Type::getType($gamemode);
        if ($type === null) {
            $ffa = FFA::getArena($gamemode);
            if ($ffa === null) {
                $sender->sendMessage('§r§cThat was not a valid gamemode!');
                return;
            }
        }

        if ($type !== null) {
            $info = $type->getKB();
            $cd = $type->getAttackCoolDown();
        }
        else {
            $info = $ffa->getKB();
            $cd = $ffa->getAttackCoolDown();
        }
        $exists = $args['cooldown'] ?? null;

        if ($exists === null) {
            $name = $type === null ? $ffa->getName() : $type->getName();
            $sender->sendMessage("\n§r§8» §r§l§6KNOCKBACK §r§8«\n" . "§f Type: §b" . $name . "\n§f Cooldown: §b" . $cd . "\n§f Vertical: §b" . $info['yKb'] . "\n§f Horizontal: §b" . $info['xzKb'] . "\n§f Max Height: §b" . $info['maxHeight'] . "\n§f Revert: §b" . $info['revert'] . "\n\n");
            return;
        }

        $cooldown = $args['cooldown'] ?? $cd;
        $y = $args['y'] ?? $info['yKb'];
        $xz = $args['xz'] ?? $info['xzKb'];
        $maxHeight = $args['maxHeight'] ?? $info['maxHeight'];
        $revert = $args['revert'] ?? $info['revert'];

        Type::set($gamemode, $cooldown, $y, $xz, $maxHeight, $revert);
        $sender->sendMessage('§r§aKnockback for gamemode: ' . $gamemode . ' has been successfully saved and reloaded.');
    }

    /**
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->setPermission('oqex');
        $this->registerArgument(0, new RawStringArgument('gamemode', true));
        $this->registerArgument(1, new IntegerArgument('cooldown', true));
        $this->registerArgument(2, new FloatArgument('y', true));
        $this->registerArgument(3, new FloatArgument('xz', true));
        $this->registerArgument(4, new FloatArgument('maxHeight', true));
        $this->registerArgument(5, new FloatArgument('revert', true));
    }
}