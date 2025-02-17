<?php

namespace xSuper\OqexPractice\ui\menu\event;

use muqsit\customsizedinvmenu\CustomSizedInvMenu;
use muqsit\customsizedinvmenu\libs\muqsit\invmenu\InvMenu;
use muqsit\customsizedinvmenu\libs\muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\item\PotionType;
use pocketmine\item\VanillaItems;
use pocketmine\utils\AssumptionFailedError;
use xSuper\OqexPractice\events\EventManger;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\data\RankMap;
use xSuper\OqexPractice\player\PracticePlayer;

class EventMenu
{
	public static function create(bool $hasPermission): InvMenu
	{
		$menu = CustomSizedInvMenu::create(27)
			->setName('Join or Start Events')
			->setListener(InvMenu::readonly(static function(DeterministicInvMenuTransaction $transaction): void{
				$player = $transaction->getPlayer();
				if (!$player instanceof PracticePlayer){
					throw new AssumptionFailedError('$player should be a PracticePlayer');
				}
				$player->removeCurrentWindow();
				$current = OqexPractice::getInstance()->getEventManager()->getCurrent();

				$slot = $transaction->getAction()->getSlot();
				if ($slot === 16) {
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

				switch ($slot) {
					case 11:
						$e = OqexPractice::getInstance()->getEventManager()->createEvent(EventManger::JUGGERNAUT, $player->getName());
						$player->sendMessage('§r§aYour event is being created!');
						$e?->attemptJoin($player);
						break;
					case 10:
					case 12:
						$player->sendMessage('§r§cThis event are currently in-development!');
						break;
					case 14:
						$e = OqexPractice::getInstance()->getEventManager()->createEvent(EventManger::BRACKET, $player->getName());
						$player->sendMessage('§r§aYour event is being created!');
						$e?->attemptJoin($player);
						break;
					case 13:
						$e = OqexPractice::getInstance()->getEventManager()->createEvent(EventManger::SUMO, $player->getName());
						$player->sendMessage('§r§aYour event is being created!');
						$e?->attemptJoin($player);
				}
			}));
		//TODO: Update the items in real-time

		$current = OqexPractice::getInstance()->getEventManager()->getCurrent();

		$additionalLore = [];

		if ($current !== null) {
			$additionalLore = [
				'§r',
				'§r§cThere is already an event running, try again later!'
			];
		}

		if (!$hasPermission) {
			$additionalLore = [
				'§r',
				'§r§cYou do not have permission to host this event.',
				'§r§cPurchase a rank at §6' . OqexPractice::STORE_LINK,
				'§r§cto get permission.'
			];
		}

        $lore = [
            '§r',
            '§r§7By purchasing a §cVersai §7rank, you will be able to host',
            '§r§7your own events (with a cooldown).',
            '§r',
        ];

		if ($current === null) {
			$lore = array_merge($lore, [
				'§r§cThere is no event running right now!'
			]);
		} else {
			$lore = array_merge($lore, [
				'§r§l§6Current Event:',
				'§r§8 - §r§7Type: §e' . $current->getType(),
				'§r§8 - §r§7Time: §e' . gmdate("i:s", $current->getTime()),
				'§r',
				'§r§l§aClick §r§7to join'
			]);
		}

		$menu->getInventory()->setContents([
			10 => VanillaItems::CLOWNFISH()->setCustomName('§r§l§6King of The Hill')->setLore(array_merge([
				'§r',
				'§r§7Your goal is to attempt to stay on top of',
				"§r§7the 'hill' as other players try to knock",
				'§r§7knock you off. If you get knocked off or',
				'§r§7leave the hill, the player with the most',
				'§r§7time on the point will start to capture',
				'§r§7capture it. Stay on the hill for 5 minutes',
				'§r§7to win the event and be deemed king!'
			], $additionalLore)),

			11 => VanillaItems::SPLASH_POTION()->setType(PotionType::HEALING())->setCustomName('§r§l§6Juggernaut')->setLore(array_merge([
				"§r§7A random player will be chosen to be the 'juggernaut'.",
				'§r§7That player will receive more health and a better kit',
				'§r§7than the rest of the players. Everyone else has to try',
				'§r§7to kill the juggernaut in the given time frame. If time',
				'§r§7runs out, or the juggernaut kills everyone, the juggernaut',
				'§r§7wins. If the juggernaut is killed, the players win.'
			], $additionalLore)),

			12 => VanillaItems::NETHER_STAR()->setCustomName('§r§l§6Last Man Standing')->setLore(array_merge([
				'§r',
				'§r§7All players will spawn in a large pvp arena, with a',
				'§r§7predetermined kit. Killing a player will refill your',
				'§r§7kit and health. Kill all other players to win the event',
				'§r§7and be the Last Man Standing.'
			], $additionalLore)),

			13 => VanillaItems::STICK()->setCustomName('§r§l§6Sumo')->setLore(array_merge([
				'§r',
				'§r§7Play Sumo in a bracket styled event, where two players',
				'§r§7are put against each other. The player that gets knocked',
				'§r§7off the platform will get disqualified, while the other',
				'§r§7continues. Be the last one alive to win!'
			], $additionalLore)),
			14 => VanillaItems::BLAZE_POWDER()->setCustomName('§r§l§6Bracket')->setLore(array_merge([
				'§r',
				'§r§7Similar to Sumo, you will fight in a bracket styled',
				'§r§7tournament with a predetermined kit. The player that',
				'§r§7looses will be disqualified, while the other continues',
				'§r§7Will you come out on top?'
			], $additionalLore)),
			16 => VanillaItems::EGG()->setCustomName('§r§l§6Information')->setLore($lore)
		]);
		return $menu;
	}
}