<?php

namespace xSuper\OqexPractice\duel\type;
use pocketmine\utils\CloningRegistryTrait;

/**
 * @method static NoDebuffType NO_DEBUFF()
 * @method static DebuffType DEBUFF()
 * @method static GappleType GAPPLE()
 * @method static BuildUHCType BUILD_UHC()
 * @method static ComboType COMBO()
 * @method static SumoType SUMO()
 * @method static VanillaType VANILLA()
 * @method static ArcherType ARCHER()
 * @method static SurvivalGamesType SURVIVAL_GAMES()
 * @method static SoupType SOUP()
 * @method static TheBridgeType BRIDGE()
 * @method static BotType BOT()
 */
final class Types{
	use CloningRegistryTrait;

	private function __construct(){
		//NOOP
	}

	protected static function register(string $name, Type $item) : void{
		self::_registryRegister($name, $item);
	}

	/**
	 * @return Type[]
	 * @phpstan-return array<string, Type>
	 */
	public static function getAll() : array{
		//phpstan doesn't support generic traits yet :(
		/** @var Type[] $result */
		$result = self::_registryGetAll();
		return $result;
	}

	protected static function setup(): void{
		self::register('no_debuff', new NoDebuffType());
		self::register('debuff', new DebuffType());
		self::register('gapple', new GappleType());
		self::register('build_uhc', new BuildUHCType());
		self::register('combo', new ComboType());
		self::register('sumo', new SumoType());
		self::register('vanilla', new VanillaType());
		self::register('archer', new ArcherType());
		self::register('survival_games', new SurvivalGamesType());
		self::register('soup', new SoupType());
		self::register('bridge', new TheBridgeType());
		self::register('bot', new BotType());
	}
}