<?php

namespace xSuper\OqexPractice\duel\generator;

use Closure;
use pocketmine\plugin\PluginBase;
use pocketmine\promise\PromiseResolver;
use pocketmine\Server;
use pocketmine\utils\Filesystem;
use Symfony\Component\Filesystem\Path;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\tasks\AsyncCopyTask;

class MapGenerator
{
    public static function genMap(PluginBase $plugin, string $path, string $name, string $dst, ?Closure $task, string $src): void
    {
        $resolver = new PromiseResolver();
        if($task !== null){
            $resolver->getPromise()->onCompletion(function () use ($task, $name): void {
                if(Server::getInstance()->getWorldManager()->loadWorld($name)){
                    $world = Server::getInstance()->getWorldManager()->getWorldByName($name);
                    $world->setTime(6000);
                    $world->stopTime();
                }
                $task();
            }, static function(): void{});
        }
        $plugin->getServer()->getAsyncPool()->submitTask(new AsyncCopyTask($src, $dst, $resolver));
    }

    public static function deleteMap(string $world): void
    {
        $server = Server::getInstance();
        $path = $server->getDataPath();
        $dir = Path::join($path, 'worlds', $world);
        if (!is_dir($dir)) return;
        if (($w = $server->getWorldManager()->getWorldByName($world)) !== null) {
            foreach ($w->getPlayers() as $p) {
                if ($p instanceof PracticePlayer) $p->reset(OqexPractice::getInstance());
            }
            $server->getWorldManager()->unloadWorld($w);
        }

        Filesystem::recursiveUnlink($dir);
    }
}