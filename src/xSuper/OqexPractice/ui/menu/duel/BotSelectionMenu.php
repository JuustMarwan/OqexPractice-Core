<?php

namespace xSuper\OqexPractice\ui\menu\duel;

use muqsit\customsizedinvmenu\libs\muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\PotionType;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use xSuper\OqexPractice\bot\BotType;
use xSuper\OqexPractice\duel\Duel;
use xSuper\OqexPractice\duel\type\Type;
use xSuper\OqexPractice\items\custom\CustomItem;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\ui\menu\CustomInventory;
use xSuper\OqexPractice\utils\ItemUtils;

class BotSelectionMenu extends CustomInventory
{
    public function __construct()
    {
        parent::__construct(27);
    }

    private static function getBotType(string $diff, string $type): BotType {
        return match ($diff) {
            'dummy' => match ($type) {
                'NoDebuff' => BotType::DummyNoDebuff,
                'Archer' => BotType::DummyArcher,
                'Sumo' => BotType::DummySumo,
                'Gapple' => BotType::DummyGapple,
                'Soup' => BotType::DummySoup
            },
            'easy' => match ($type) {
                'NoDebuff' => BotType::EasyNoDebuff,
                'Archer' => BotType::EasyArcher,
                'Sumo' => BotType::EasySumo,
                'Gapple' => BotType::EasyGapple,
                'Soup' => BotType::EasySoup
            },
            'normal' => match ($type) {
                'NoDebuff' => BotType::NormalNoDebuff,
                'Archer' => BotType::NormalArcher,
                'Sumo' => BotType::NormalSumo,
                'Gapple' => BotType::NormalGapple,
                'Soup' => BotType::NormalSoup
            },
            'hard' => match ($type) {
                'NoDebuff' => BotType::HardNoDebuff,
                'Archer' => BotType::HardArcher,
                'Sumo' => BotType::HardSumo,
                'Gapple' => BotType::HardGapple,
                'Soup' => BotType::HardSoup
            },
            'godly' => match ($type) {
                'NoDebuff' => BotType::GodlyNoDebuff,
                'Archer' => BotType::GodlyArcher,
                'Sumo' => BotType::GodlySumo,
                'Gapple' => BotType::GodlyGapple,
                'Soup' => BotType::GodlySoup
            },
            default => BotType::DummyNoDebuff,
        };
    }

    public function getTitle(Player $player): string
    {
        return 'PvP Bots';
    }

    public function handle(DeterministicInvMenuTransaction $transaction): void
    {
        $player = $transaction->getPlayer();
        if (!$player instanceof PracticePlayer){
            throw new AssumptionFailedError('$player should be a PracticePlayer');
        }

        $stage = $this->getData($player)['stage'] ?? 1;

        if ($stage === 1) {
            if ($transaction->getAction()->getSlot() === 16 && $player->getDuel() === null) {
                $botType = self::getBotType($player->getBotDiff(), $player->getBotType());

                Duel::createBotDuel(OqexPractice::getInstance(), $player, Type::getType($player->getBotType()), $botType);
                return;
            }

            $type = match ($transaction->getAction()->getSlot()) {
                10 => 'NoDebuff',
                11 => 'Archer',
                12 => 'Sumo',
                13 => 'Soup',
                14 => 'Gapple',
                default => null
            };

            if ($type !== null) {
                $player->setBotType($type);
                $this->data[$player->getUniqueId()->getBytes()] = ['stage' => 0];
                $this->render($player);
            }
        } else if ($stage === 0) {
            $diff = match ($transaction->getAction()->getSlot()) {
                10 => 'dummy',
                11 => 'easy',
                12 => 'normal',
                13 => 'hard',
                14 => 'godly',
                default => null
            };

            if ($diff !== null) {
                $player->setBotDiff($diff);
                $player->removeCurrentWindow();

                $botType = self::getBotType($player->getBotDiff(), $player->getBotType());

                Duel::createBotDuel(OqexPractice::getInstance(), $player, Type::getType($player->getBotType()), $botType);
            }
        }
    }

    public function render(Player $player): void {
        $stage = $this->getData($player)['stage'] ?? 1;

        if ($stage === 1) {
            /** @var PracticePlayer $player */
            $this->getMenu($player)->getInventory()->setContents([
                10 => VanillaItems::SPLASH_POTION()->setType(PotionType::HEALING())->setCustomName('§r§l§6NoDebuff Bot')->setLore([
                ]),
                11 => VanillaItems::BOW()->setCustomName('§r§l§6Archer Bot')->setLore([
                ]),
                12 => VanillaItems::SUSPICIOUS_STEW()->setCustomName('§r§l§6Sumo Bot')->setLore([
                ]),
                13 => VanillaItems::MUSHROOM_STEW()->setCustomName('§r§l§6Soup Bot')->setLore([
                ]),
                14 => VanillaItems::GOLDEN_APPLE()->setCustomName('§r§l§6Gapple Bot')->setLore([
                ]),
                16 => VanillaItems::EGG()->setCustomName('§r§l§aQuick Fight')->setLore([
                    '§r§7Fight your last fought bot again!'
                ]),
            ]);
        } else if ($stage === 0) {
            $this->getMenu($player)->getInventory()->setContents([
                10 => CustomItem::getHead('dummy')->asItem()->setCustomName('§r§l§aDummy Bot')->setLore([
                    '§r§7Fight against a smart PvP bot to warm-up',
                    '§r§7or for general practice!',
                    '§r',
                    '§r§l§aInfo:',
                    '§r§8 - §fCPS: §a5',
                    '§r§8 - §fAccuracy: §a20%',
                ]),
                11 => CustomItem::getHead('easy')->asItem()->setCustomName('§r§l§bEasy Bot')->setLore([
                    '§r§7Fight against a smart PvP bot to warm-up',
                    '§r§7or for general practice!',
                    '§r',
                    '§r§l§bInfo:',
                    '§r§8 - §fCPS: §b10',
                    '§r§8 - §fAccuracy: §b40%',
                ]),
                12 => CustomItem::getHead('normal')->asItem()->setCustomName('§r§l§eNormal Bot')->setLore([
                    '§r§7Fight against a smart PvP bot to warm-up',
                    '§r§7or for general practice!',
                    '§r',
                    '§r§l§eInfo:',
                    '§r§8 - §fCPS: §e15',
                    '§r§8 - §fAccuracy: §e60%',
                ]),
                13 => CustomItem::getHead('hard')->asItem()->setCustomName('§r§l§6Hard Bot')->setLore([
                    '§r§7Fight against a smart PvP bot to warm-up',
                    '§r§7or for general practice!',
                    '§r',
                    '§r§l§6Info:',
                    '§r§8 - §fCPS: §620',
                    '§r§8 - §fAccuracy: §680%',
                ]),
                14 => CustomItem::getHead('godly')->asItem()->setCustomName('§r§l§cGodly Bot §r§7(3.1 block reach)')->setLore([
                    '§r§7Fight against a smart PvP bot to warm-up',
                    '§r§7or for general practice!',
                    '§r',
                    '§r§l§cInfo:',
                    '§r§8 - §fCPS: §c20',
                    '§r§8 - §fAccuracy: §c100%',
                ]),
                16 => VanillaBlocks::REDSTONE_COMPARATOR()->asItem()->setCustomName('§r§l§dCustomize')->setLore(['§r§cComing soon...'])
            ]);
        }
    }
}