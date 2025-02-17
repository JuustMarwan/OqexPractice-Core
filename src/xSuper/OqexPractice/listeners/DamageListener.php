<?php

namespace xSuper\OqexPractice\listeners;

use pocketmine\entity\animation\HurtAnimation;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use xSuper\OqexPractice\duel\special\BotDuel;
use xSuper\OqexPractice\duel\special\PartyDuel;
use xSuper\OqexPractice\duel\special\PartyScrimDuel;
use xSuper\OqexPractice\duel\special\TheBridgeDuel;
use xSuper\OqexPractice\entities\pathfinder\entity\ArcherEntity;
use xSuper\OqexPractice\entities\pathfinder\entity\GappleEntity;
use xSuper\OqexPractice\entities\pathfinder\entity\SmartEntity;
use xSuper\OqexPractice\events\BracketEvent;
use xSuper\OqexPractice\events\JuggernautEvent;
use xSuper\OqexPractice\ffa\BuildFFA;
use xSuper\OqexPractice\ffa\NoDebuffFFA;
use xSuper\OqexPractice\ffa\SumoFFA;
use xSuper\OqexPractice\items\custom\CustomItem;
use xSuper\OqexPractice\items\custom\staff\FreezeItem;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\data\RankMap;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\player\settings\SettingIDS;

class DamageListener implements Listener
{
    public function __construct(private PluginBase $plugin)
    {

    }

    /** @priority HIGH
     * @handleCancelled true
     */
    public function cancelDamage(EntityDamageEvent $ev): void
    {
        $entity = $ev->getEntity();
        if ($ev instanceof EntityDamageByEntityEvent) {
            $attacker = $ev->getDamager();
            if ($attacker instanceof PracticePlayer && $entity instanceof PracticePlayer) {
                if ($attacker->getStaffMode()) {
                    $i = $attacker->getInventory()->getItemInHand();
                    if ($i->getNamedTag()->getTag('customItem') !== null) {
                        $name = $i->getNamedTag()->getString('customItem');
                        $item = CustomItem::getItem($name);
                        if ($item instanceof FreezeItem) {
                            $rankmap = new RankMap();
                            if ($rankmap->permissionMap($ev->getDamager()->getData()->getHighestRank()) < $rankmap->permissionMap($ev->getEntity()->getData()->getHighestRank())) {
                                $ev->getDamager()->sendMessage("§r§cYou can not freeze this person. This action has been reported to adminstration.");
                                $ev->cancel();
                            }
                            if ($entity->isFrozen()) {
                                $entity->unFreeze();
                                $attacker->sendTip('§r§7Thawed §a' . $entity->getName());
                                $staff = '§r§l§eiSPY §r§8» §e' . $ev->getDamager()->getName() . ' §7has unfrozen §e' . $entity->getName();
                            } else {
                                $entity->freeze();
                                $attacker->sendTip('§r§7Froze §a' . $entity->getName());
                                $staff = '§r§l§eiSPY §r§8» §e' . $ev->getDamager()->getName() . ' §7has frozen §e' . $entity->getName();
                            }

                            foreach (OqexPractice::getOnlineStaff() as $s) $s->sendMessage($staff);

                            $ev->cancel();
                            return;
                        }
                    }
                }

                if ($attacker->getSpectator() || $attacker->getVanished()) {
                    $ev->cancel();
                    return;
                }
            }
        }
        if ($entity instanceof PracticePlayer && !$entity->canBeDamaged()) $ev->cancel();
    }

    /** @priority HIGHEST */
    public function handleDamage(EntityDamageEvent $ev): void
    {
        $entity = $ev->getEntity();
        $cause = $ev->getCause();

        if ($ev->getModifier(EntityDamageEvent::MODIFIER_PREVIOUS_DAMAGE_COOLDOWN) !== 0.0) {
            $ev->cancel();
            return;
        }

        if ($entity instanceof SmartEntity | $entity instanceof GappleEntity | $entity instanceof ArcherEntity && !$ev->isCancelled()) {
            if ($ev instanceof EntityDamageByEntityEvent) {
                $p = $ev->getDamager();

                if ($p instanceof PracticePlayer) {
                    $d = $p->getDuel();

                    if ($d instanceof BotDuel) {
                        if ($d->isEnded()) $ev->cancel();
                        else if ($entity->getHealth() + $entity->getAbsorption() <= $ev->getFinalDamage()) {
                            $d->setWinner($p);
                            $entity->broadcastAnimation(new HurtAnimation($entity));
                            $entity->flagForDespawn();
                            $d->end($this->plugin);
                            $ev->cancel();
                            return;
                        }

                        $ev->setAttackCooldown(10);
                        return;
                    }

                    $ev->cancel();
                    return;
                }
            }
        }

        if ($entity instanceof PracticePlayer && !$ev->isCancelled()) {
            $ffa = $entity->getFFA();
            if ($ffa !== null) {
                $ev->setAttackCooldown($ffa->getAttackCoolDown());
            }

            if ($ev instanceof EntityDamageByEntityEvent) {
                $attacker = $ev->getDamager();
                if ($attacker instanceof PracticePlayer) {
                    if ($entity->isFrozen()) {
                        $attacker->sendTip('§r§cThat player is frozen!');
                        $ev->cancel();
                        return;
                    }

                    if ($attacker->isFrozen()) {
                        $attacker->sendTip('§r§cYou are frozen!');
                        $ev->cancel();
                        return;
                    }

                    if ($ffa instanceof SumoFFA || $ffa instanceof NoDebuffFFA) {
                        if (!$entity->getData()->getSettings()->asBool(SettingIDS::INTERRUPTING) || !$attacker->getData()->getSettings()->asBool(SettingIDS::INTERRUPTING)) {
                            if ($entity->getTagger() !== null && $entity->getTagger() !== $attacker) {
                                $attacker->sendTip('§r§cThat player is fighting someone!');
                                $ev->cancel();
                                return;
                            }

                            if ($attacker->getTagger() !== null && $attacker->getTagger() !== $entity) {
                                $attacker->sendTip('§r§cYou are fighting someone!');
                                $ev->cancel();
                                return;
                            }
                        }
                    }

                    if (($d = $entity->getDuel()) !== null) {
                        if ($d instanceof PartyScrimDuel) {
                            if ($d->areTeamed($entity, $attacker)) {
                                $attacker->sendTip('§r§cThat is your teammate!');
                                $ev->cancel();
                                return;
                            }
                        }

                        $type = $d->getType();
                        $ev->setAttackCooldown($type->getAttackCoolDown());
                    }

                    if (($e = $entity->getEvent()) !== null) {
                        if ($e instanceof JuggernautEvent) {
                            if ($e->isTeamed($entity, $attacker)) {
                                $attacker->sendTip('§r§cThat is your teammate!');
                                $ev->cancel();
                                return;
                            }
                        }
                        $ev->setAttackCooldown($e->getAttackCoolDown());
                    }

                    $entity->giveCombatTag($this->plugin, $attacker);
                    $attacker->giveCombatTag($this->plugin, $entity);
                } else if ($attacker instanceof Projectile) {
                    $owner = $attacker->getOwningEntity();
                    if ($owner instanceof PracticePlayer) {
                        if ($entity->isFrozen()) {
                            $owner->sendTip('§r§cThat player is frozen!');
                            $ev->cancel();
                            return;
                        }

                        if ($owner->isFrozen()) {
                            $owner->sendTip('§r§cYou are frozen!');
                            $ev->cancel();
                            return;
                        }

                        if ($ffa instanceof SumoFFA || $ffa instanceof NoDebuffFFA) {
                            if (!$entity->getData()->getSettings()->asBool(SettingIDS::INTERRUPTING) || !$owner->getData()->getSettings()->asBool(SettingIDS::INTERRUPTING)) {
                                if ($entity->getTagger() !== null && $entity->getTagger() !== $owner) {
                                    $owner->sendTip('§r§cThat player is fighting someone!');
                                    $ev->cancel();
                                    return;
                                }

                                if ($owner->getTagger() !== null && $owner->getTagger() !== $entity) {
                                    $owner->sendTip('§r§cYou are fighting someone!');
                                    $ev->cancel();
                                    return;
                                }
                            }
                        }

                        $entity->giveCombatTag($this->plugin, $owner);
                        $owner->giveCombatTag($this->plugin, $entity);
                    }
                }
            }

            if ($entity->isFrozen()) {
                $ev->cancel();
                return;
            }

            $cause = $ev->getCause();

            if (($ffa = $entity->getFFA()) !== null) {
                if ($cause === EntityDamageEvent::CAUSE_FALL) {
                    if (!$ffa->fallDamage()) {
                        $ev->cancel();
                        return;
                    }
                }
            }

            if ($ev->getFinalDamage() >= $entity->getHealth() + $entity->getAbsorption()) {
                if (($ffa = $entity->getFFA()) !== null) {
                    if ($entity->isLoaded() && $entity->getData()->getSettings()->getSetting(SettingIDS::FFA_RESPAWN)) $ffa->reset($entity->getTagger(), $entity);
                    else {
                        $ffa->leave($entity->getTagger(), $entity);
                        $entity->reset($this->plugin);
                    }

                    $entity->broadcastAnimation(new HurtAnimation($entity));
                    $ev->cancel();
                    return;
                }
            }

            if (($event = $entity->getEvent()) !== null) {
                if ($event->isEnded()) {
                    $ev->cancel();;
                    return;
                }

                if ($event->getType() === 'Juggernaut' || $event->getType() === 'Bracket') {
                    if ($ev->getFinalDamage() >= $entity->getHealth() + $entity->getAbsorption()) {
                        $ev->cancel();
                        $entity->broadcastAnimation(new HurtAnimation($entity));
                        $event->disqualify($entity);
                        if ($event instanceof BracketEvent) $event->resetFight();
                        return;
                    }
                }
            }

            if (($duel = $entity->getDuel()) !== null) {
                if ($cause === EntityDamageEvent::CAUSE_FALL) {
                    if (!$duel->getType()->fallDamage()) {
                        $ev->cancel();
                        return;
                    }
                }

                if ($ev->getFinalDamage() >= $entity->getHealth() + $entity->getAbsorption()) {
                    if ($duel instanceof TheBridgeDuel) {
                        $ev->cancel();
                        $duel->resetPlayer($entity);
                        return;
                    }

                    if ($duel instanceof BotDuel) {
                        $duel->end($this->plugin);
                        $entity->setCanBeDamaged(false);
                    } else if (!$duel instanceof PartyDuel && !$duel instanceof PartyScrimDuel) {
                        $duel->setWinner($duel->opposite($entity));
                        $entity->broadcastAnimation(new HurtAnimation($entity));
                        $duel->end($this->plugin);
                    } else {
                        $duel->killPlayer($entity);
                    }
                    $ev->cancel();
                }
            }
        }
    }
}