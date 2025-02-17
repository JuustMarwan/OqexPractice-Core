<?php

namespace xSuper\OqexPractice\ffa;

use pocketmine\utils\CloningRegistryTrait;
use xSuper\OqexPractice\player\kit\BuildUHCKit;

/**
 * @method static SumoFFA SUMO()
 * @method static NoDebuffFFA NO_DEBUFF()
 * @method static BuildFFA BUILD()
 * @method static OITCFFA OITC()
 * @method static BUHCFFA BUHC()
 */
final class Arenas{
	use CloningRegistryTrait;

	private function __construct(){
		//NOOP
	}

	protected static function register(string $name, FFA $item) : void{
		self::_registryRegister($name, $item);
	}

	/**
	 * @return FFA[]
	 * @phpstan-return array<string, FFA>
	 */
	public static function getAll() : array{
		//phpstan doesn't support generic traits yet :(
		/** @var FFA[] $result */
		$result = self::_registryGetAll();
		return $result;
	}

	protected static function setup(): void{
		self::register('sumo', new SumoFFA());
		self::register('no_debuff', new NoDebuffFFA());
		self::register('build', new BuildFFA());
		self::register('oitc', new OITCFFA());
        self::register('buhc', new BUHCFFA());
	}
}