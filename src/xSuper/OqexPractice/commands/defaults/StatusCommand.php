<?php

namespace xSuper\OqexPractice\commands\defaults;

use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\utils\Process;

class StatusCommand extends BaseCommand
{
	/** @param array<array-key, mixed> $args */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $mUsage = Process::getAdvancedMemoryUsage();

        $server = $sender->getServer();

        $time = (int) (microtime(true) - $server->getStartTime());

        $seconds = $time % 60;
        $minutes = null;
        $hours = null;
        $days = null;

        if($time >= 60){
            $minutes = floor(($time % 3600) / 60);
            if($time >= 3600){
                $hours = floor(($time % (3600 * 24)) / 3600);
                if($time >= 3600 * 24){
                    $days = floor($time / (3600 * 24));
                }
            }
        }

        $uptime = ($minutes !== null ?
                ($hours !== null ?
                    ($days !== null ?
                        "$days days "
                        : "") . "$hours hours "
                    : "") . "$minutes minutes "
                : "") . "$seconds seconds";

        $s = "\n§r§8» §r§l§6STATUS §r§8«\n" . "§f Uptime: §b" . $uptime . "\n§f Online: §b" . count($server->getOnlinePlayers()) . "\n§f";

        $tpsColor = '§a';
        if($server->getTicksPerSecond() < 17){
            $tpsColor = '§6';
        }elseif($server->getTicksPerSecond() < 12){
            $tpsColor = '§c';
        }

        $s .= " Current TPS: {$tpsColor}{$server->getTicksPerSecond()} ({$server->getTickUsage()}%)\n§f";
        $s .= " Average TPS: {$tpsColor}{$server->getTicksPerSecondAverage()} ({$server->getTickUsageAverage()}%)\n§f";

        $bandwidth = $server->getNetwork()->getBandwidthTracker();
        $s .= " Network upload: §d" . round($bandwidth->getSend()->getAverageBytes() / 1024, 2) . " kB/s\n§f";
        $s .= " Network download: §d" . round($bandwidth->getReceive()->getAverageBytes() / 1024, 2) . " kB/s\n§f";

        $s .= " Threads: §c" . Process::getThreadCount() . "\n§f";

        $s .= " Memory usage: §a" . number_format(round(($mUsage[1] / 1024) / 1024, 2), 2) . " MB\n\n";
        $sender->sendMessage($s);
    }

    protected function prepare(): void
    {
        $this->setPermission('oqex');
    }
}