<?php

namespace xSuper\OqexPractice\commands\arguments;

use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\args\BaseArgument;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\types\command\CommandEnum;
use function array_keys;
use function array_map;
use function implode;
use function preg_match;
use function strtolower;

class EnchantArgument extends BaseArgument {

	/** @var array<string, string> */
    private static array $values = [];

    public function __construct(string $name, bool $optional = false) {
        parent::__construct($name, $optional);
        self::$values = ArgumentUtils::stringEnchantMap();

        $this->parameterData->enum = new CommandEnum($this->getEnumName(), $this->getEnumValues());
    }

    public function getNetworkType(): int {
        return -1;
    }

    public function canParse(string $testString, CommandSender $sender): bool {
        return (bool)preg_match(
            "/^(" . implode("|", array_map(strtolower(...), $this->getEnumValues())) . ")$/iu",
            $testString
        );
    }

    public function getEnumName(): string {
        return 'enchantment';
    }

    public function getValue(string $string): string{
        return  self::$values[strtolower($string)];
    }

	/** @return list<string> */
    public function getEnumValues(): array {
        return array_keys(self::$values);
    }

    public function parse(string $argument, CommandSender $sender): string
    {
        return $argument;
    }

    public static function addValue(string $value): void
    {
        self::$values[$value] = $value;
    }

    public function getTypeName(): string
    {
        return 'enchantment';
    }
}