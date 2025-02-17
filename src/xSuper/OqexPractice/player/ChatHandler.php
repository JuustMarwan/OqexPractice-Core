<?php

namespace xSuper\OqexPractice\player;

use DateTime;
use pocketmine\Server;
use pocketmine\utils\AssumptionFailedError;
use xSuper\OqexPractice\player\data\RankMap;
use xSuper\OqexPractice\tasks\SpamCheckerTask;
use xSuper\OqexPractice\utils\TimeUtils;

class ChatHandler
{
    public const MUTED = 0;
    public const COOL_DOWN = 1;
    public const LOCK = 2;

    private const STORE_AMOUNT = 5;
    public const SCORE_THRESHOLD = 65;
    private const WARNING_EXPIRE = '1m 30s';

    private ?DateTime $lastSent = null;
    private ?DateTime $muted = null;

	/** @var list<DateTime> */
    private array $warnings = [];

    /** @var list<string> */
    private array $lastMessages = [];

    public function __construct(private PracticePlayer $player) {}

	/** @return ?array{self::MUTED|self::COOL_DOWN, DateTime} */
    public function canSendMessage(string $message): ?array
    {
        if ($this->muted !== null) {
            if ($this->muted <= new DateTime()) $this->muted = null;
            else return [self::MUTED, $this->muted];
        }

        if ($this->lastSent !== null) {
            if ($this->lastSent <= new DateTime()) $this->lastSent = null;
            else return [self::COOL_DOWN, $this->lastSent];
        }

        if ($this->player->getData()->getRankPermission() < RankMap::permissionMap('helper')) Server::getInstance()->getAsyncPool()->submitTask(new SpamCheckerTask($message, $this->player->getUniqueId()->toString(), $this->lastMessages));

        $this->lastMessages[] = $message;

        if (count($this->lastMessages) >= self::STORE_AMOUNT) {
            array_shift($this->lastMessages);
        }

        $this->applyCoolDown();
        return null;
    }

    public function warn(): void
    {
        $now = new DateTime();
        foreach ($this->warnings as $k => $warn) {
            if ($warn <= $now) unset($this->warnings[$k]);
        }

        $this->warnings = array_merge($this->warnings);

        $this->warnings[] = (TimeUtils::stringToTimestampAdd(self::WARNING_EXPIRE, $now) ??
			throw new AssumptionFailedError('This should not return null')
		)[0];

        $total = count($this->warnings);

        if (count($this->warnings) >= 2) $this->player->sendMessage('§r§l§cWARNING! §r§cRepeated spam will result in a temporary mute!');

        if ($total > 10) $this->mute('10m');
        else if ($total > 6) $this->mute('5m');
        else if ($total > 3) $this->mute('1m');
    }

    public function mute(string|DateTime $time, string $player = 'Console', string $reason = 'Spam'): void
    {
        if (is_string($time)){
			$time = (TimeUtils::stringToTimestampAdd($time, new DateTime()) ??
				throw new \InvalidArgumentException("Invalid time $time")
			)[0];
		}
        $this->muted = $time;

        $this->player->sendMessage('§r§l§cMUTE §r§8» §r§7You have been muted for §c' . TimeUtils::formatDate(new DateTime(), $this->muted) . ' §7for §c' . $reason . '§8 - §c' . $player);
    }

    public function applyCoolDown(): void
    {
        $pem = $this->player->getData()->getRankPermission();
        if ($pem < RankMap::permissionMap('ultra')) $this->lastSent = (TimeUtils::stringToTimestampAdd('3s', new DateTime()) ??
			throw new AssumptionFailedError('This should not return null')
		)[0];
        else if ($pem < RankMap::permissionMap('helper')) $this->lastSent = (TimeUtils::stringToTimestampAdd('1s', new DateTime()) ??
			throw new AssumptionFailedError('This should not return null')
		)[0];
    }
}