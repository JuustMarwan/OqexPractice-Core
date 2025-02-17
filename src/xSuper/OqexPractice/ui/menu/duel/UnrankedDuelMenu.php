<?php

namespace xSuper\OqexPractice\ui\menu\duel;

use muqsit\customsizedinvmenu\libs\muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use xSuper\OqexPractice\duel\queue\QueueManager;
use xSuper\OqexPractice\duel\type\Type;
use xSuper\OqexPractice\duel\type\Types;
use xSuper\OqexPractice\duel\utils\Elo;
use xSuper\OqexPractice\items\custom\CustomItem;
use xSuper\OqexPractice\items\custom\InteractiveItems;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\ui\menu\CustomInventory;
use xSuper\OqexPractice\ui\menu\Menus;

class UnrankedDuelMenu extends CustomInventory
{
    public function __construct()
    {
        parent::__construct(45);
    }

    public function getTitle(Player $player): string
    {
        return 'Unranked Queue';
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
            20 => Type::getType('Survival Games'),
            21 => Type::getType('Soup'),
            22 => Type::getType('Bridge'),
            default => null
        };

        if ($slot === 39) {
            $rankName = Elo::getLadderRank($player->getData()->getAverageElo());
            if($rankName === null){
                $player->removeCurrentWindow();
                $player->sendMessage('You haven\'t played any games yet');
                return;
            }
            $elos = $player->getData()->getElos();
            unset($elos['average']);
            Menus::RANKED_DUEL()->create($player, ['elos' => $elos, 'rankName' => $rankName, 'rankedGames' => $player->getData()->getTotalRankedGames()]);
            return;
        }

        if ($slot === 41) {
            Menus::BOT_SELECTION()->create($player, ['stage' => 1]);
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
        if (QueueManager::getInstance()->addQueue($player, $type)) {
            return;
        }
        $player->getInventory()->setContents([
            8 => InteractiveItems::LEAVE_QUEUE()->getActualItem($player)
        ]);
    }

    public function render(Player $player): void
    {
        $data = $this->getData($player);

        $this->getMenu($player)->getInventory()->setContents([
            10 => Types::NO_DEBUFF()->getMenuItem(),
            11 => Types::DEBUFF()->getMenuItem(),
            12 => Types::GAPPLE()->getMenuItem(),
            13 => Types::BUILD_UHC()->getMenuItem(),
            14 => Types::COMBO()->getMenuItem(),
            15 => Types::SUMO()->getMenuItem(),
            16 => Types::VANILLA()->getMenuItem(),
            19 => Types::ARCHER()->getMenuItem(),
            20 => Types::SURVIVAL_GAMES()->getMenuItem(),
            21 => Types::SOUP()->getMenuItem(),
            22 => Types::BRIDGE()->getMenuItem(),
            39 => VanillaItems::DIAMOND()->setCustomName('§r§l§6Ranked matches')->setLore([
                '§r',
                '§r§7Play ranked matches to increase your elo',
                '§r§7and put yourself on the top of the leaderboards.',
                '§r',
                '§r§7Gain ranked matches by:',
                ' §r§8- §7Voting at ' . OqexPractice::VOTE_LINK,
                ' §r§8- §7Purchasing a rank',
                ' §r§8- §7Ranked Matches: §5' . $data['rankedGames'],
                '§r',
                '§r§l§aClick §r§7here to play ranked matches'
            ]),
            41 => CustomItem::getHead('normal')->asItem()->setCustomName('§r§l§cPvP Bots')->setLore([
                '§r§7Fight a super smart PvP bot with an array',
                '§r§7of difficulties to choose from, or create',
                '§r§7a custom one to your liking!',
                '§r',
                '§r§l§aClick §r§7here to open bot duels'
            ])
        ]);
    }
}