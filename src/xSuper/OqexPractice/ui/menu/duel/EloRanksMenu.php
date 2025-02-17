<?php

namespace xSuper\OqexPractice\ui\menu\duel;

use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use xSuper\OqexPractice\duel\utils\Elo;
use xSuper\OqexPractice\duel\utils\LeaderboardIds;
use xSuper\OqexPractice\ui\menu\CustomInventory;

class EloRanksMenu extends CustomInventory
{
    public function __construct()
    {
        parent::__construct(54);
    }

    public function getTitle(Player $player): string
    {
        return 'Elo Ranks';
    }

    public function render(Player $player): void
    {
        $data = $this->getData($player);

        $leaderboard = Elo::leaderboard(LeaderboardIds::AVERAGE_ELO);

        $name = Elo::getLadderRank($data['avgElo']);
        if ($name === null){
            throw new \InvalidArgumentException("Unknown rank name for average elo");
        }
        $color = Elo::getColorCode($name);

        $ranks = Elo::RANKS;

        $s = [
            '§r'
        ];

        $c = 0;
        foreach ($leaderboard as $l) {
            if(!is_numeric($l[1])){
                throw new AssumptionFailedError('Elo should be an int\float');
            }
            if ($c === 5) break;
            if ($l[1] < $ranks['Obsidian'][0]) $s[] = ' §r§e' . ($c + 1) . '. §7Unknown: §e?';
            else $s[] = ' §r§e' . ($c + 1) . '. §7 ' . $l[0] . ': §e' . $l[1];
            $c++;
        }

        if ($c !== 5) {
            for ($x = $c + 1; $x <= 5; $x++) {
                $s[] = ' §r§e' . $x . '. §7Unknown: §e?';
            }
        }

        $this->getMenu($player)->getInventory()->setContents([
            49 => VanillaBlocks::REDSTONE_TORCH()->asItem()->setCustomName('§r§l§6Ranked Matches')->setLore([
                '§r',
                '§r§7Play ranked matches to increase your elo',
                '§r§7and put yourself on the top of the leaderboards.',
                '§r',
                '§r§7Your stats:',
                ' §r§8- §7Average Elo: §e' . $data['avgElo'],
                ' §r§8- §7Elo Rank: ' . $color . $name,
                ' §r§8- §7Ranked Matches: §5' . $data['rankedGames'],
            ]),
            47 => VanillaBlocks::OBSIDIAN()->asItem()->setCustomName('§r§l§5Obsidian §r§7(' . $ranks['Obsidian'][0] . '+)'),
            51 => VanillaBlocks::BEDROCK()->asItem()->setCustomName('§r§l§9Bedrock §r§7(Top 5 Obsidian Players)')->setLore($s),
            11 => VanillaItems::IRON_INGOT()->setCustomName('§r§l§7Iron I §r§7(' . $ranks['Iron I'][0] . ' - ' . $ranks['Iron I'][1] . ')'),
            12 => VanillaItems::IRON_INGOT()->setCount(2)->setCustomName('§r§l§7Iron II §r§7(' . $ranks['Iron II'][0] . ' - ' . $ranks['Iron II'][1] . ')'),
            13 => VanillaItems::IRON_INGOT()->setCount(3)->setCustomName('§r§l§7Iron III §r§7(' . $ranks['Iron III'][0] . ' - ' . $ranks['Iron III'][1] . ')'),
            14 => VanillaItems::IRON_INGOT()->setCount(4)->setCustomName('§r§l§7Iron IV §r§7(' . $ranks['Iron IV'][0] . ' - ' . $ranks['Iron IV'][1] . ')'),
            15 => VanillaBlocks::IRON()->asItem()->setCount(5)->setCustomName('§r§l§7Iron V §r§7(' . $ranks['Iron V'][0] . ' - ' . $ranks['Iron V'][1] . ')'),
            20 => VanillaItems::GOLD_INGOT()->setCustomName('§r§l§eGold I §r§7(' . $ranks['Gold I'][0] . ' - ' . $ranks['Gold I'][1] . ')'),
            21 => VanillaItems::GOLD_INGOT()->setCount(2)->setCustomName('§r§l§eGold II §r§7(' . $ranks['Gold II'][0] . ' - ' . $ranks['Gold II'][1] . ')'),
            22 => VanillaItems::GOLD_INGOT()->setCount(3)->setCustomName('§r§l§eGold III §r§7(' . $ranks['Gold III'][0] . ' - ' . $ranks['Gold III'][1] . ')'),
            23 => VanillaItems::GOLD_INGOT()->setCount(4)->setCustomName('§r§l§eGold IV §r§7(' . $ranks['Gold IV'][0] . ' - ' . $ranks['Gold IV'][1] . ')'),
            24 => VanillaBlocks::GOLD()->asItem()->setCount(5)->setCustomName('§r§l§eGold V §r§7(' . $ranks['Gold V'][0] . ' - ' . $ranks['Gold V'][1] . ')'),
            29 => VanillaItems::DIAMOND()->setCustomName('§r§l§bDiamond I §r§7(' . $ranks['Diamond I'][0] . ' - ' . $ranks['Diamond I'][1] . ')'),
            30 => VanillaItems::DIAMOND()->setCount(2)->setCustomName('§r§l§bDiamond II §r§7(' . $ranks['Diamond II'][0] . ' - ' . $ranks['Diamond II'][1] . ')'),
            31 => VanillaItems::DIAMOND()->setCount(3)->setCustomName('§r§l§bDiamond III §r§7(' . $ranks['Diamond III'][0] . ' - ' . $ranks['Diamond III'][1] . ')'),
            32 => VanillaItems::DIAMOND()->setCount(4)->setCustomName('§r§l§bDiamond IV §r§7(' . $ranks['Diamond IV'][0] . ' - ' . $ranks['Diamond IV'][1] . ')'),
            33 => VanillaBlocks::DIAMOND()->asItem()->setCount(5)->setCustomName('§r§l§bDiamond V §r§7(' . $ranks['Diamond V'][0] . ' - ' . $ranks['Diamond V'][1] . ')'),
            38 => VanillaItems::EMERALD()->setCustomName('§r§l§aEmerald I §r§7(' . $ranks['Emerald I'][0] . ' - ' . $ranks['Emerald I'][1] . ')'),
            39 => VanillaItems::EMERALD()->setCount(2)->setCustomName('§r§l§aEmerald II §r§7(' . $ranks['Emerald II'][0] . ' - ' . $ranks['Emerald II'][1] . ')'),
            40 => VanillaItems::EMERALD()->setCount(3)->setCustomName('§r§l§aEmerald III §r§7(' . $ranks['Emerald III'][0] . ' - ' . $ranks['Emerald III'][1] . ')'),
            41 => VanillaItems::EMERALD()->setCount(4)->setCustomName('§r§l§aEmerald IV §r§7(' . $ranks['Emerald IV'][0] . ' - ' . $ranks['Emerald IV'][1] . ')'),
            42 => VanillaBlocks::EMERALD()->asItem()->setCount(5)->setCustomName('§r§l§aEmerald V §r§7(' . $ranks['Emerald V'][0] . ' - ' . $ranks['Emerald V'][1] . ')'),
        ]);
    }
}