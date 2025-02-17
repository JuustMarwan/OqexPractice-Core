<?php

namespace xSuper\OqexPractice\commands\arguments;

use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\args\BaseArgument;
use pocketmine\command\CommandSender;
use pocketmine\item\StringToItemParser;
use pocketmine\network\mcpe\protocol\types\command\CommandEnum;
use function array_keys;
use function array_map;
use function implode;
use function preg_match;
use function strtolower;

class ItemArgument extends BaseArgument {

	/** @var array<string, string> */
    private static array $values = [];

    public function __construct(string $name, bool $optional = false) {
        parent::__construct($name, $optional);
        foreach (StringToItemParser::getInstance()->getKnownAliases() as $item) {
            self::$values[(string)$item] = (string)$item;
        }

        $this->parameterData->enum = new CommandEnum($this->getEnumName(), $this->getEnumValues());
    }

    public function getNetworkType(): int {
        return -1;
    }

    public function canParse(string $testString, CommandSender $sender): bool {
        $ar = $this->getEnumValues();
        foreach (array_chunk($ar, 1000) as $values) {
            if (preg_match(
                "/^(" . implode("|", array_map("\\strtolower", $values)) . ")$/iu",
                $testString
            ) !== 0) return true;
        }

        return false;
    }

    public function getEnumName(): string {
        return 'item';
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
        return 'item';
    }
}