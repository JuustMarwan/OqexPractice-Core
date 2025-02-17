<?php

namespace xSuper\OqexPractice\ui\form\event;

use pocketmine\player\Player;
use xSuper\OqexPractice\events\EventManger;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\BaseForm;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\FormIcon;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\dktapps\pmforms\MenuOption;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\data\RankMap;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\ui\form\MenuForm;

class EventForm extends MenuForm
{

	public function create(Player $player): BaseForm
	{
		return $this->createForm($player);
	}

    public function getTitle(Player $player): string
    {
        return 'Join or start events';
    }

    public function getBody(Player $player): string
    {
        return '';
    }

    public function getOptions(Player $player): array
    {
        return [
            new MenuOption('§r§l§6King of The Hill', new FormIcon('texture/items/clownfish.png', FormIcon::IMAGE_TYPE_PATH)),
            new MenuOption('§r§l§6Juggernaut', new FormIcon('texture/items/potion_bottle_splash_heal.png', FormIcon::IMAGE_TYPE_PATH)),
            new MenuOption('§r§l§6Last Man Standing', new FormIcon('texture/items/nether_star.png', FormIcon::IMAGE_TYPE_PATH)),
            new MenuOption('§r§l§6Sumo', new FormIcon('texture/items/lead.png', FormIcon::IMAGE_TYPE_PATH)),
            new MenuOption('§r§l§6Bracket', new FormIcon('texture/items/blaze_powder.png', FormIcon::IMAGE_TYPE_PATH)),
            new MenuOption('§r§l§6Information', new FormIcon('texture/items/egg.png', FormIcon::IMAGE_TYPE_PATH))
        ];
    }

    public function handle(Player $player, int $selected): void
    {
        if(!$player instanceof PracticePlayer){
            return;
        }

        $current = OqexPractice::getInstance()->getEventManager()->getCurrent();

        if ($selected === 5) {
            if ($current === null) {
                $player->sendMessage('§r§cThere is no event running right now!');
                return;
            }

            if ($player->getEvent() !== null) return;
            $current->join($player);
            return;
        }

        if ($player->getData()->getRankPermission() < RankMap::permissionMap('ultra')) {
            $player->sendMessage('§r§cYou do not have permission to host that event.');
            return;
        }

        if ($current !== null) {
            $player->sendMessage('§r§cThere is already an event running, try again later!');
            return;
        }
        switch($selected){
            case 1:
                $e = OqexPractice::getInstance()->getEventManager()->createEvent(EventManger::JUGGERNAUT, $player->getName());
                $player->sendMessage('§r§aYour event is being created!');
                $e?->attemptJoin($player);
                return;
            case 0:
            case 2:
                $player->sendMessage('§r§cThis event are currently in-development!');
                return;
            case 3:
                $e = OqexPractice::getInstance()->getEventManager()->createEvent(EventManger::SUMO, $player->getName());
                $player->sendMessage('§r§aYour event is being created!');
                $e?->attemptJoin($player);
                return;
            case 4:
                $e = OqexPractice::getInstance()->getEventManager()->createEvent(EventManger::BRACKET, $player->getName());
                $player->sendMessage('§r§aYour event is being created!');
                $e?->attemptJoin($player);
        }
    }
}