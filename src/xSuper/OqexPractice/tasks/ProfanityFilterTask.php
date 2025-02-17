<?php

namespace xSuper\OqexPractice\tasks;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Filesystem;
use Ramsey\Uuid\Uuid;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\data\RankMap;
use xSuper\OqexPractice\player\PracticePlayer;

class ProfanityFilterTask extends AsyncTask{
    private static bool $setup = false;
	/** @var list<string> */
    private static array $blacklisted = [];
	/** @var array<string, true> */
    private static array $whitelisted = [];


    public function setUp(): void
    {
        if(self::$setup){
            return;
        }
        self::$setup = true;
		$rawBlacklisted = json_decode(Filesystem::fileGetContents($this->folder . 'blacklisted_words.json'), true, flags: JSON_THROW_ON_ERROR);
		if(!is_array($rawBlacklisted)){
			throw new \TypeError('Expected array, got ' . gettype($rawBlacklisted));
		}
		self::$blacklisted = $rawBlacklisted;
		$rawWhitelisted = json_decode(Filesystem::fileGetContents($this->folder . 'whitelisted_words.json'), true, flags: JSON_THROW_ON_ERROR);
		if(!is_array($rawWhitelisted)){
			throw new \TypeError('Expected array, got ' . gettype($rawBlacklisted));
		}
		self::$whitelisted = $rawWhitelisted;
    }

	/** @param list<string> $players */
    public function __construct(private string $message, string $sender, array $players, private string $folder)
    {
        $this->storeLocal('sender', $sender);
        $this->storeLocal('players', $players);
    }

    public function onRun(): void
    {
        $this->setUp();

        $text = $this->message;

        $text = preg_replace("/[∂άαáàâãªä]/u", "a", $text);
        $text = preg_replace("/[∆лДΛдАÁÀÂÃÄ]/u", "A", $text);
        $text = preg_replace("/[ЂЪЬБъь]/u", "b", $text);
        $text = preg_replace("/[βвВ]/u", "B", $text);
        $text = preg_replace("/[çς©с]/u", "c", $text);
        $text = preg_replace("/[ÇС]/u", "C", $text);
        $text = preg_replace("/[δ]/u", "d", $text);
        $text = preg_replace("/[éèêëέëèεе℮ёєэЭ]/u", "e", $text);
        $text = preg_replace("/[ÉÈÊË€ξЄ€Е∑]/u", "E", $text);
        $text = preg_replace("/[₣]/u", "F", $text);
        $text = preg_replace("/[НнЊњ]/u", "H", $text);
        $text = preg_replace("/[ђћЋ]/u", "h", $text);
        $text = preg_replace("/[ÍÌÎÏ]/u", "I", $text);
        $text = preg_replace("/[íìîïιίϊі]/u", "i", $text);
        $text = preg_replace("/[Јј]/u", "j", $text);
        $text = preg_replace("/[ΚЌК]/u", 'K', $text);
        $text = preg_replace("/[ќк]/u", 'k', $text);
        $text = preg_replace("/[ℓ∟]/u", 'l', $text);
        $text = preg_replace("/[Мм]/u", "M", $text);
        $text = preg_replace("/[ñηήηπⁿ]/u", "n", $text);
        $text = preg_replace("/[Ñ∏пПИЙийΝЛ]/u", "N", $text);
        $text = preg_replace("/[óòôõºöοФσόо]/u", "o", $text);
        $text = preg_replace("/[ÓÒÔÕÖθΩθОΩ]/u", "O", $text);
        $text = preg_replace("/[ρφрРф]/u", "p", $text);
        $text = preg_replace("/[®яЯ]/u", "R", $text);
        $text = preg_replace("/[ГЃгѓ]/u", "r", $text);
        $text = preg_replace("/[Ѕ]/u", "S", $text);
        $text = preg_replace("/[ѕ]/u","s", $text);
        $text = preg_replace("/[Тт]/u", "T", $text);
        $text = preg_replace("/[τ†‡]/u", "t", $text);
        $text = preg_replace("/[úùûüџμΰµυϋύ]/u", "u", $text);
        $text = preg_replace("/[√]/u", "v", $text);
        $text = preg_replace("/[ÚÙÛÜЏЦц]/u", "U", $text);
        $text = preg_replace("/[Ψψωώẅẃẁщш]/u", "w", $text);
        $text = preg_replace("/[ẀẄẂШЩ]/u", "W", $text);
        $text = preg_replace("/[ΧχЖХж]/u", "x", $text);
        $text = preg_replace("/[ỲΫ¥]/u", "Y", $text);
        $text = preg_replace("/[ỳγўЎУуч]/u", "y", $text);
        $text = preg_replace("/[ζ]/u", "Z", $text);


        $words = explode(' ', $text);
        foreach ($words as $key => $word) {
            $lWord = mb_strtolower($word);

            $whitelisted = [];
            foreach (self::$whitelisted as $whitelistedWord) {
                if(str_contains($lWord, $whitelistedWord)) {
                    $words[$key] = $lWord;
                    $whitelisted[$lWord] = true;
                }
            }

            foreach (self::$blacklisted as $blacklistedWord){
                $w = $whitelisted[$lWord] ?? false;
                if(str_contains($lWord, $blacklistedWord)){
					$words[$key] = $w ? $lWord : str_repeat('*', strlen($word));
                }
            }
        }

        $this->setResult(implode(' ', $words));
    }

    public function onCompletion(): void
    {
        $server = Server::getInstance();
		/** @var string $uuid */
		$uuid = $this->fetchLocal('sender');
		$sender = $server->getPlayerByUUID(Uuid::fromString($uuid));
        if ($sender instanceof PracticePlayer) {
			/** @var string $filteredMessage */
			$filteredMessage = $this->getResult();
			$message = RankMap::formatChat($sender, $filteredMessage);
			/** @var list<string> $players */
			$players = $this->fetchLocal('players');

            foreach ($players as $player) {
                if ($sender->getStaffChat()) {
                    if ($server->getPlayerByUUID(Uuid::fromString($player))->getData()->getRankPermission() >= RankMap::permissionMap('helper')) {
                        $server->getPlayerByUUID(Uuid::fromString($player))?->sendMessage('§r§c§l[STAFF]§r ' . $message);
                    }
                }
                else {
                    $server->getPlayerByUUID(Uuid::fromString($player))?->sendMessage($message);
                }

            }
        }
    }
}