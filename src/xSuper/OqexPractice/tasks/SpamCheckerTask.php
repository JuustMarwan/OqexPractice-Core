<?php

namespace xSuper\OqexPractice\tasks;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\thread\NonThreadSafeValue;
use Ramsey\Uuid\Uuid;
use xSuper\OqexPractice\player\ChatHandler;
use xSuper\OqexPractice\player\PracticePlayer;

class SpamCheckerTask extends AsyncTask {
    /** @var NonThreadSafeValue<list<string>> */
    private NonThreadSafeValue $previous;
    /** @param list<string> $previous */
    public function __construct(private string $message, string $sender, array $previous)
    {
        self::storeLocal('sender', $sender);
        $this->previous = new NonThreadSafeValue($previous);
    }

    public function onRun(): void
    {
        $words = explode(' ', mb_strtolower($this->message));
        $same = 0;
        $total = 0;

        foreach ($this->previous->deserialize() as $previous) {
            $pWords = explode(' ', $previous);
            foreach ($pWords as $pWord) {
                if (in_array(mb_strtolower($pWord), $words, true)) $same++;
                $total++;
            }
        }

        $total += count($words);

        $score = 0;

        if ($same !== 0) $score = round(($same / $total * 100), 0, PHP_ROUND_HALF_DOWN);

        $this->setResult($score);
    }

    public function onCompletion(): void
    {
        $server = Server::getInstance();
		/** @var string $uuid */
		$uuid = $this->fetchLocal('sender');
		$sender = $server->getPlayerByUUID(Uuid::fromString($uuid));
        if ($sender instanceof PracticePlayer) {
            if ($this->getResult() >= ChatHandler::SCORE_THRESHOLD) $sender->getChatHandler()->warn();
        }
    }
}