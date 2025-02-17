<?php

namespace xSuper\OqexPractice\commands\arguments;

use pocketmine\entity\effect\Effect;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\VanillaEnchantments;

class ArgumentUtils
{
    // Bad Omen, Hero of the Village, and Slow Falling are missing + some of these don't work
    /** @return Effect[] */
    public static function effectMap(): array
    {
        return [
            'absorption' => VanillaEffects::ABSORPTION(),
            'blindness' => VanillaEffects::BLINDNESS(),
            'conduit_power' => VanillaEffects::CONDUIT_POWER(),
            'fatal_poison' => VanillaEffects::FATAL_POISON(),
            'fire_resistance' => VanillaEffects::FIRE_RESISTANCE(),
            'haste' => VanillaEffects::HASTE(),
            'health_boost' => VanillaEffects::HEALTH_BOOST(),
            'hunger' => VanillaEffects::HUNGER(),
            'instant_damage' => VanillaEffects::INSTANT_DAMAGE(),
            'instant_health' => VanillaEffects::INSTANT_HEALTH(),
            'invisibility' => VanillaEffects::INVISIBILITY(),
            'jump_boost' => VanillaEffects::JUMP_BOOST(),
            'levitation' => VanillaEffects::LEVITATION(),
            'mining_fatigue' => VanillaEffects::MINING_FATIGUE(),
            'nausea' => VanillaEffects::NAUSEA(),
            'night_vision' => VanillaEffects::NIGHT_VISION(),
            'poison' => VanillaEffects::POISON(),
            'regeneration' => VanillaEffects::REGENERATION(),
            'resistance' => VanillaEffects::RESISTANCE(),
            'saturation' => VanillaEffects::SATURATION(),
            'slowness' => VanillaEffects::SLOWNESS(),
            'speed' => VanillaEffects::SPEED(),
            'strength' => VanillaEffects::STRENGTH(),
            'water_breathing' => VanillaEffects::WATER_BREATHING(),
            'weakness' => VanillaEffects::WEAKNESS(),
            'wither' => VanillaEffects::WITHER()
        ];
    }

	/** @return array<string, string> */
    public static function stringEffectMap(): array
    {
        $ar = [];

        foreach (self::effectMap() as $name => $effect) {
            $ar[$name] = $name;
        }

        return $ar;
    }

    // Aqua Affinity, Bane of Arthropods, Channeling, Binding, Depth Strider, Frost Walker, Impaling, Looting,
    // Loyalty, Luck of the Sea, Lure, Multi Shot, Piercing, Quick Charge, Riptide, Smite, and Soul Speed are missing
    /** @return Enchantment[] */
    public static function enchantMap(): array
    {
        return [
            'fortune' => VanillaEnchantments::FORTUNE(),
            'blast_protection' => VanillaEnchantments::BLAST_PROTECTION(),
            'vanishing' => VanillaEnchantments::VANISHING(),
            'efficiency' => VanillaEnchantments::EFFICIENCY(),
            'feather_falling' => VanillaEnchantments::FEATHER_FALLING(),
            'fire_aspect' => VanillaEnchantments::FIRE_ASPECT(),
            'fire_protection' => VanillaEnchantments::FIRE_PROTECTION(),
            'flame' => VanillaEnchantments::FLAME(),
            'infinity' => VanillaEnchantments::INFINITY(),
            'knockback' => VanillaEnchantments::KNOCKBACK(),
            'mending' => VanillaEnchantments::MENDING(),
            'power' => VanillaEnchantments::POWER(),
            'projectile_protection' => VanillaEnchantments::PROJECTILE_PROTECTION(),
            'protection' => VanillaEnchantments::PROTECTION(),
            'punch' => VanillaEnchantments::PUNCH(),
            'respiration' => VanillaEnchantments::RESPIRATION(),
            'sharpness' => VanillaEnchantments::SHARPNESS(),
            'silk_touch' => VanillaEnchantments::SILK_TOUCH(),
            'thorns' => VanillaEnchantments::THORNS(),
            'unbreaking' => VanillaEnchantments::UNBREAKING()
        ];
    }

	/** @return array<string, string> */
    public static function stringEnchantMap(): array
    {
        $ar = [];

        foreach (self::enchantMap() as $name => $enchant) {
            $ar[$name] = $name;
        }

        return $ar;
    }


}