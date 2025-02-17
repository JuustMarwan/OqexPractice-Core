<?php

namespace xSuper\OqexPractice\ui\menu\duel;

use muqsit\customsizedinvmenu\libs\muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use xSuper\OqexPractice\duel\queue\QueueManager;
use xSuper\OqexPractice\duel\type\Type;
use xSuper\OqexPractice\duel\type\Types;
use xSuper\OqexPractice\duel\utils\Elo;
use xSuper\OqexPractice\items\custom\InteractiveItems;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\ui\menu\CustomInventory;
use xSuper\OqexPractice\ui\menu\Menus;

class RankedDuelMenu extends CustomInventory
{
    public function __construct()
    {
        parent::__construct(45);
    }

    public function getTitle(Player $player): string
    {
        return 'Ranked Queue';
    }

    public function handle(DeterministicInvMenuTransaction $transaction): void
    {
        $player = $transaction->getPlayer();
        if (!$player instanceof PracticePlayer) {
            throw new AssumptionFailedError('$player should be a PracticePlayer');
        }

        $type = match ($slot = $transaction->getAction()->getSlot()) {
            10 => Type::getType('NoDebuff'),
            11 => Type::getType('Debuff'),
            12 => Type::getType('Gapple'),
            13 => Type::getType('BuildUHC'),
            14 => Type::getType('Combo'),
            15 => Type::getType('Sumo'),
            16 => Type::getType('Vanilla'),
            19 => Type::getType('Archer'),
            20 => Type::getType('Soup'),
            21 => Type::getType('Bridge'),
            default => null
        };

        if ($slot === 39) {
            Menus::ELO_RANKS()->create($player, ['avgElo' => $player->getData()->getAverageElo(), 'rankedGames' => $player->getData()->getTotalRankedGames()]); // TODO: Load data in-menu
            return;
        }

        if ($slot === 43) {
            Menus::ELO_LEADERBOARD()->create($player);
            return;
        }

        $player->removeCurrentWindow();
        if ($type === null) {
            return;
        }

        if (QueueManager::getInstance()->isInQueue($player)) {
            $player->sendMessage('§r§cYou are already in a queue!');
            return;
        }
        if ($player->getData()->getTotalRankedGames() <= 0) {
            $player->sendMessage("\n§r§cYou have ran out of ranked games for the day! You can gain more by: \n §8- §7Purchasing more ranked games\n §8- §7Purchasing an in-game rank\n §8- §7Voting for us daily\n\n§cRun the /links command for more information!\n\n");
            return ;
        }

        if (QueueManager::getInstance()->addQueue($player, $type, true)) {
            return;
        }
        $player->getInventory()->setContents([
            8 => InteractiveItems::LEAVE_QUEUE()->getActualItem($player)
        ]);
    }

    public function render(Player $player): void
    {
        $data = $this->getData($player);

        $rankName = $data['rankName'];
        $elos = $data['elos'];

        $color = Elo::getColorCode($rankName);

        $this->getMenu($player)->getInventory()->setContents([
            10 => Types::NO_DEBUFF()->getMenuItem(true),
            11 => Types::DEBUFF()->getMenuItem(true),
            12 => Types::GAPPLE()->getMenuItem(true),
            13 => Types::BUILD_UHC()->getMenuItem(true),
            14 => Types::COMBO()->getMenuItem(true),
            15 => Types::SUMO()->getMenuItem(true),
            16 => Types::VANILLA()->getMenuItem(true),
            19 => Types::ARCHER()->getMenuItem(true),
            20 => Types::SOUP()->getMenuItem(true),
            21 => Types::BRIDGE()->getMenuItem(true),
            37 => VanillaItems::DIAMOND()->setCustomName('§r§l§6Information')->setLore([
                '§r',
                '§r§7Play ranked matches to increase your elo',
                '§r§7and put yourself on the top of the leaderboards.',
                '§r',
                '§r§7Gain ranked matches by:',
                ' §r§8- §7Voting at ' . OqexPractice::VOTE_LINK,
                ' §r§8- §7Purchasing a rank',
                ' §r§8- §7Ranked Matches: §5' . $data['rankedGames'],
            ]),
            39 => VanillaItems::PAPER()->setCustomName('§r§l§6Elo Ranks')->setLore([
                '§r',
                '§r§7Practice has a range of different',
                '§r§7elo ranks. Gain elo to boost your',
                '§r§7elo rank for this season!',
                '§r',
                ' §r§8- §r§7Your Elo Rank: ' . $color . $rankName,
                '§r',
                '§r§l§aClick §r§7here to view elo ranks'
            ]),
            41 => VanillaBlocks::BEACON()->asItem()->setCustomName('§r§l§6Your Ranked Stats')->setLore([
                '§r',
                ' §r§8- §7NoDebuff: §e' . $elos['NoDebuff'],
                ' §r§8- §7Debuff: §e' . $elos['Debuff'],
                ' §r§8- §7Gapple: §e' . $elos['Gapple'],
                ' §r§8- §7BuildUHC: §e' . $elos['BuildUHC'],
                ' §r§8- §7Combo: §e' . $elos['Combo'],
                ' §r§8- §7Sumo: §e' . $elos['Sumo'],
                ' §r§8- §7Vanilla: §e' . $elos['Vanilla'],
                ' §r§8- §7Archer: §e' . $elos['Archer'],
                ' §r§8- §7Soup: §e' . $elos['Soup'],
                ' §r§8- §7Bridge: §e' . $elos['Bridge']

            ]),
            43 => VanillaItems::IRON_CHESTPLATE()->setCustomName('§r§l§6Ranked Leaderboards')->setLore([
                '§r',
                '§r§7Are you on the top of the server',
                '§r§7leaderboard? Check the top players',
                '§r§7here, they can also be checked at',
                '§r§7the leaderboards in the server spawn.',
                '§r',
                '§r§l§aClick §r§7here to view leaderboards'
            ])
        ]);
    }
}