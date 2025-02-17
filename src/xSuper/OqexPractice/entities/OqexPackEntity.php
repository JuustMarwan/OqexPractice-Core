<?php

namespace xSuper\OqexPractice\entities;

use pocketmine\block\Block;
use pocketmine\entity\Location;
use pocketmine\entity\object\FallingBlock;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Server;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\SOFe\AwaitGenerator\Await;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\SOFe\AwaitGenerator\GeneratorUtil;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\player\settings\SettingIDS;
use xSuper\OqexPractice\ui\menu\pack\PackMenu;

class OqexPackEntity extends FallingBlock
{
    /** @var PracticePlayer[] */
    private array $hidden = [];
    public bool $gravityEnabled = false;

    public function __construct(Location $location, Block $block, ?CompoundTag $nbt = null)
    {
        parent::__construct($location, $block, $nbt);
        $this->setNoClientPredictions();

        $this->setNameTagVisible(false);
        $this->setNameTag('');
    }

    public function onUpdate(int $currentTick): bool
    {
        $radius = 2 * 0.75;
        $entities = $this->getWorld()->getNearbyEntities($this->getBoundingBox()->expandedCopy($radius, $radius, $radius), $this);
        $in = [];
        foreach ($entities as $entity) {
            if ($entity instanceof PracticePlayer) {
                if (!$entity->getVanished()) {
                    $in[] = $entity->getName();
                    if (!in_array($entity, $this->hidden, true)) {
                        if (!$entity->isOnline()) return true;
                        $this->hidden[] = $entity;
                        foreach (Server::getInstance()->getOnlinePlayers() as $p) {
                            $p->hidePlayer($entity);
                        }
                    }
                }
            }
        }

        foreach ($this->hidden as $p) {
            if (!in_array($p->getName(), $in, true)) {
                if (!$p->getVanished()) {
                    if (!$p->isOnline()) return true;
                    unset($this->hidden[array_search($p, $this->hidden, true)]);
                    foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                        $player->showPlayer($p);
                    }
                }
            }
        }

        return true;
    }

    public function attack(EntityDamageEvent $source): void
    {
        $source->cancel();

        if ($source instanceof EntityDamageByEntityEvent) {
            $attacker = $source->getDamager();
            if ($attacker instanceof PracticePlayer) {
                if($attacker->getData()->getSettings()->getSetting(SettingIDS::UI_TYPE) === SettingIDS::UI_TYPE_CHEST){
					/** @phpstan-var \WeakReference<PracticePlayer> $weakAttacker */
					$weakAttacker = \WeakReference::create($attacker);
					Await::f2c(static function() use ($weakAttacker): \Generator{
						yield from GeneratorUtil::empty();
						$page = 1;
						$elements = 21;
						/*
						$iIndex = 0;
						$rows = yield from OqexPractice::getDatabase()->asyncSelect('oqex-practice.packs.all', [
							'uuid' => $attacker->getUniqueId()->toString(),
							'offset' => $iIndex,
							'limit' => $elements
						]);
						$packs = array_column($rows, 'pack');
						*/
						$attacker = $weakAttacker->get();
						if($attacker === null){
							return;
						}
						$packs = [];
						PackMenu::create(1, $packs)->send($attacker);
					});
                }else{
                    //TODO
                }
            }
        }
    }
}