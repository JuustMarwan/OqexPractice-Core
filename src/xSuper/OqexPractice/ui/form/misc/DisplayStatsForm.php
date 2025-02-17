<?php

declare(strict_types=1);

namespace xSuper\OqexPractice\ui\form\misc;

use pocketmine\player\Player;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\BaseForm;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\MenuOption;
use xSuper\OqexPractice\ui\form\MenuForm;

class DisplayStatsForm extends MenuForm{
	private string $type;
	/** @var array{int<0, max>, int<0, max>, int<0, max>, int<0, max>} */
	private array $stats;
	private string $playerName;
	//private mixed $unknown;

	/** @param array{int<0, max>, int<0, max>, int<0, max>, int<0, max>} $stats */
	public function create(Player $player, string $type, array $stats, string $playerName, mixed $unknown): BaseForm{
		$this->type = $type;
		$this->stats = $stats;
		$this->playerName = $playerName;
		//$this->unknown = $unknown;
		return $this->createForm($player);
	}
    public function getTitle(Player $player): string
    {
        return "$this->playerName's $this->type";
    }

    public function getBody(Player $player): string
    {
        return
            '§r§8 - §7Daily: §a' . $this->stats[0] . PHP_EOL .
            '§r§8 - §7Weekly: §e' . $this->stats[1] . PHP_EOL .
            '§r§8 - §7Monthly: §c' . $this->stats[2] . PHP_EOL .
            '§r§8 - §7Lifetime: §4' . $this->stats[3];
    }

    public function getOptions(Player $player): array
    {
        return [new MenuOption('Back')];
    }

    public function handle(Player $player, int $selected): void
    {
        //Forms::PLAYER_STATS()->create($player, [$this->playerName, $this->unknown]);
    }
}