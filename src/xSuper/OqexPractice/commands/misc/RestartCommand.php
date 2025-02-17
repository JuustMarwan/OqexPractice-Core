<?php

namespace xSuper\OqexPractice\commands\misc;

use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\args\BooleanArgument;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\BaseCommand;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\exception\ArgumentOrderException;
use pocketmine\command\CommandSender;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\PracticePlayer;

class RestartCommand extends BaseCommand
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

        $instant = $args['instant'] ?? false;

        $server = Server::getInstance();

        if (!$instant) {
            $tick = 60;
            OqexPractice::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function () use ($server, &$tick): void {
                if ($tick === 60 || $tick === 45 || $tick === 35 || $tick === 30 || $tick === 25 || $tick === 20 || $tick === 15 || $tick === 10 || $tick <= 5) {
                    if ($tick === 1) $s = ' §r§7second';
                    else $s = ' §r§7seconds';

                    foreach ($server->getOnlinePlayers() as $p) {
                        /** @var PracticePlayer $p */
                        $p->sendMessage('§r§l§4REBOOT §r§8» §7This server will be rebooting in §l§4' . $tick . $s);
                        $p->sendSound('random.click');
                        if ($tick === 0) $p->kick('§r§cServer rebooting...');
                    }
                }

                if ($tick === 0) {
                    register_shutdown_function(function () {
                        pcntl_exec("./start.sh");
                    });

                    $server->shutdown();
                }

                $tick--;
            }), 20);
        } else {
            foreach ($server->getOnlinePlayers() as $p) {
                $p->sendMessage('§r§cServer rebooting in 5s...');
            }

            OqexPractice::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($server, &$tick): void {
                foreach ($server->getOnlinePlayers() as $p) {
                    if ($tick === 0) $p->kick('§r§cServer rebooting...');
                }

                register_shutdown_function(function () {
                    pcntl_exec("./start.sh");
                });

                $server->shutdown();
            }), 20 * 5);
            }
    }

    /**
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->setPermission('oqex');
        $this->registerArgument(0, new BooleanArgument('instant', true));
    }
}