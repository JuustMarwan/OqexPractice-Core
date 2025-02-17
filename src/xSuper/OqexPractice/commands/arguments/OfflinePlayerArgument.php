<?php

namespace xSuper\OqexPractice\commands\arguments;

use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\args\BaseArgument;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\types\command\CommandEnum;
use function array_keys;
use function strtolower;

class OfflinePlayerArgument extends BaseArgument {

	/** @var array<string, string> */
    private static array $values = [];

	/** @param array<string, string> $values */
    public function __construct(string $name, array $values, bool $optional = false) {
        parent::__construct($name, $optional);
        self::$values = $values;
        $this->parameterData->enum = new CommandEnum($this->getEnumName(), $this->getEnumValues());
    }

    public function getNetworkType(): int {
        return -1;
    }

    public function canParse(string $testString, CommandSender $sender): bool {
        return true;
    }

    public function getEnumName(): string {
        return 'offlinePlayer';
    }

    public function getValue(string $string): string{
        return self::$values[strtolower($string)];
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
        return 'offlinePlayer';
    }
}