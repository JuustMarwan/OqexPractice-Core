<?php
declare(strict_types=1);

namespace xSuper\OqexPractice\portal;

use Closure;
use pocketmine\event\Listener;
use pocketmine\Server;
use UnexpectedValueException;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\portal\exception\PortalAuthException;
use xSuper\OqexPractice\portal\packet\AuthResponsePacket;
use xSuper\OqexPractice\portal\packet\FindPlayerRequestPacket;
use xSuper\OqexPractice\portal\packet\FindPlayerResponsePacket;
use xSuper\OqexPractice\portal\packet\Packet;
use xSuper\OqexPractice\portal\packet\PacketPool;
use xSuper\OqexPractice\portal\packet\PlayerInfoRequestPacket;
use xSuper\OqexPractice\portal\packet\PlayerInfoResponsePacket;
use xSuper\OqexPractice\portal\packet\ProtocolInfo;
use xSuper\OqexPractice\portal\packet\RegisterServerPacket;
use xSuper\OqexPractice\portal\packet\ServerListRequestPacket;
use xSuper\OqexPractice\portal\packet\ServerListResponsePacket;
use xSuper\OqexPractice\portal\packet\TransferRequestPacket;
use xSuper\OqexPractice\portal\packet\TransferResponsePacket;
use xSuper\OqexPractice\portal\packet\UnknownPacket;
use xSuper\OqexPractice\portal\packet\UpdatePlayerLatencyPacket;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\Player;
use pocketmine\utils\Binary;
use pocketmine\utils\Internet;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use xSuper\OqexPractice\threads\SocketThread;
use xSuper\OqexPractice\utils\LocalAC;
use function strtolower;

class Portal implements Listener
{
    private static self $instance;

    private SocketThread $thread;

    /** @var int[] */
    private array $playerLatencies = [];

    private OqexPractice $plugin;

    public function init(OqexPractice $plugin): void
    {
        $this->plugin = $plugin;

        self::$instance = $this;

        $plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);

        $config = $plugin->getConfig();

        $host = $config->get("proxy-address", "127.0.0.1");
        $port = (int)$config->getNested("socket.port", 47007);

        $sleeperHandlerEntry = $plugin->getServer()->getTickSleeper()->addNotifier(
            function (): void {
                while (($receive = $this->thread->getReceive()) !== null) {
                    $ar = json_decode($receive, true);

                    $event = $ar['event'] ?? '';

                    switch ($event) {
                        case 'heartbeat':
                            break;
                        case 'latency_update':
                            $this->playerLatencies[$ar['player'] ?? ''] = $ar['oomph_calculated'] ?? -1;
                            break;
                        case 'flag':
                            LocalAC::flag($ar['player'], $ar['check_name'] . $ar['check_type']);
                            break;
                        default:
                            print_r($ar);
                    }
                }
            }
        );

	    $this->thread = new SocketThread($sleeperHandlerEntry, $host, $port);
        $this->thread->start();
    }

    public function stop(): void
    {
        $this->thread->quit();
    }

    public function onPlayerJoin(PlayerJoinEvent $event): void
    {
        $this->playerLatencies[$event->getPlayer()->getName()] = 0;
    }

    public function onPlayerQuit(PlayerQuitEvent $event): void
    {
        unset($this->playerLatencies[$event->getPlayer()->getName()]);
    }

    public static function getInstance(): Portal
    {
        return self::$instance;
    }

    public function getPlayerLatency(Player $player): int
    {
        return $this->playerLatencies[$player->getName()] ?? -1;
    }

    public function getThread(): SocketThread
    {
        return $this->thread;
    }
}