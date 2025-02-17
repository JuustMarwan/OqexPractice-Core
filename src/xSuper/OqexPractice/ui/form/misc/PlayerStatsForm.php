<?php

declare(strict_types=1);

namespace xSuper\OqexPractice\ui\form\misc;

use pocketmine\player\Player;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\BaseForm;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\FormIcon;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\MenuOption;
use xSuper\OqexPractice\ui\form\MenuForm;

/** @phpstan-type Stats array{'kills': int<0, max>, 'deaths': int<0, max>} */
class PlayerStatsForm extends MenuForm{
	private string $playerName;
	/** @var array{'daily': Stats, 'weekly': Stats, 'monthly': Stats, 'lifetime': Stats} */
	private array $stats;
	/** @param array{'daily': Stats, 'weekly': Stats, 'monthly': Stats, 'lifetime': Stats} $stats */
	public function create(Player $player, string $playerName, array $stats): BaseForm
	{
		$this->playerName = $playerName;
		$this->stats = $stats;
		return $this->createForm($player);
	}
    public function getTitle(Player $player): string
    {
        return "$this->playerName's Stats";
    }

    public function getBody(Player $player): string
    {
        return '';
    }

    public function getOptions(Player $player): array
    {
        return [
            new MenuOption('§r§l§6Kills', new FormIcon('texture/items/experience_bottle.png', FormIcon::IMAGE_TYPE_PATH)),
            new MenuOption('§r§l§6K.D Ratio', new FormIcon('texture/items/experience_bottle.png', FormIcon::IMAGE_TYPE_PATH)),
            new MenuOption('§r§l§6Deaths', new FormIcon('texture/items/experience_bottle.png', FormIcon::IMAGE_TYPE_PATH)),
        ];
    }

    public function handle(Player $player, int $selected): void
    {
        switch($selected){
            case 0:
                /*Forms::DISPLAY_STATS()->create($player, [
                    'Kills',
                    [
                        $this->stats['daily']['kills'],
                        $this->stats['weekly']['kills'],
                        $this->stats['monthly']['kills'],
                        $this->stats['lifetime']['kills']
                    ],
                    $this->playerName,
                    $this->stats
                ]);*/
                break;
            case 1:
                $ratio = [];

                foreach (['daily', 'weekly', 'monthly', 'lifetime'] as $time) {
                    $kills = $this->stats[$time]['kills'];
                    $deaths = $this->stats[$time]['deaths'];

                    if ($deaths === 0) $ratio[$time] = $kills;
                    else $ratio[$time] = $kills / $deaths;
                }

                /*Forms::DISPLAY_STATS()->create($player, [
                    'K.D Ratio',
                    [
                        $ratio['daily'],
                        $ratio['weekly'],
                        $ratio['monthly'],
                        $ratio['lifetime']
                    ],
                    $this->playerName,
                    $this->stats
                ]);*/
                break;
            case 2:
                /*Forms::DISPLAY_STATS()->create($player, [
                    'Deaths',
                    [
                        $this->stats['daily']['deaths'],
                        $this->stats['weekly']['deaths'],
                        $this->stats['monthly']['deaths'],
                        $this->stats['lifetime']['deaths']
                    ],
                    $this->playerName,
                    $this->stats
                ]);*/
        }
    }
}