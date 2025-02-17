<?php

namespace xSuper\OqexPractice\commands\cosmetics\subcommands;

use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\BaseSubCommand;
use Generator;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use Ramsey\Uuid\Uuid;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\SOFe\AwaitGenerator\Await;
use xSuper\OqexPractice\commands\arguments\OfflinePlayerArgument;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\cosmetic\CosmeticManager;
use xSuper\OqexPractice\player\cosmetic\misc\CosmeticItem;
use xSuper\OqexPractice\player\data\RankMap;
use xSuper\OqexPractice\player\PlayerSqlHelper;
use xSuper\OqexPractice\player\PracticePlayer;

class CosmeticsAllSubCommand extends BaseSubCommand
{
	/**
	 * @param array<string, string> $values
	 * @param string[] $aliases
	 */
    public function __construct(private array $values, string $name, string $description = "", array $aliases = [])
    {
        parent::__construct($name, $description, $aliases);
    }

	/** @param array<array-key, mixed> $args */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        return;

        if ($sender instanceof PracticePlayer) {
            if (!$sender->isLoaded()) return;
            if ($sender->getData()->getRankPermission() < RankMap::permissionMap('owner')) {
                $sender->sendMessage('§r§cYou do not have permission to run this command!');
                return;
            }
        }

		/** @var ?string $p */
        $p = $args['offlinePlayer'] ?? null;

        if ($p === null) {
            $sender->sendMessage('§r§cYou need to specify a player!');
            return;
        }

        PlayerSqlHelper::getByLowerName($p, function (?array $data) use ($sender): void {
            if ($data === null) {
                $sender->sendMessage('§r§cThat player has never joined the server!');
                return;
            }

            $capes = array_values(CosmeticManager::getAllCape());
            foreach ($capes as $c) {
                OqexPractice::getDatabase()->executeInsert('oqex-practice.cosmetics.owned.cape.save', [
                    'uuid' => $data['uuid'],
                    'cape' => $c->getId()
                ]);
            }
            $artifacts = array_values(CosmeticManager::getAllArtifact());
            foreach ($artifacts as $a) {
                OqexPractice::getDatabase()->executeInsert('oqex-practice.cosmetics.owned.artifact.save', [
                    'uuid' => $data['uuid'],
                    'artifact' => $a->getId()
                ]);
            }
            $projectiles = array_values(CosmeticManager::getAllProjectile());
            foreach ($projectiles as $p) {
                OqexPractice::getDatabase()->executeInsert('oqex-practice.cosmetics.owned.projectile.save', [
                    'uuid' => $data['uuid'],
                    'projectile' => $p->getId()
                ]);
            }

            if (($p = Server::getInstance()->getPlayerByUUID(Uuid::fromString($data['uuid']))) !== null && $p->isOnline()) {
                /** @var PracticePlayer $p */
                Await::f2c(static function() use($artifacts, $capes, $p, $projectiles): Generator{
					/**
					 * @var list<string> $tags
					 * @var list<numeric-string> $killPhrases
					 * @var array{cape: int, potColor: int, projectile: int, tag: string, artifact: int, killPhrase: int, chatColor: value-of<TextFormat::COLORS>} $equipped
					 */
                    [$tags, $killPhrases, $equipped] = yield from Await::all([/** @phpstan-ignore-line */
                        (static function() use($p): Generator{
							/** @var non-empty-list<array{'tag': string}> $rows */
                            $rows = yield from OqexPractice::getDatabase()->asyncSelect('oqex-practice.cosmetics.owned.tag.all', ['uuid' => $p->getUniqueId()->toString()]);
                            return array_column($rows, 'tag');
                        })(),
                        (static function() use($p): Generator{
							/** @var non-empty-list<array{'killPhrase': numeric-string}> $rows */
                            $rows = yield from OqexPractice::getDatabase()->asyncSelect('oqex-practice.cosmetics.owned.killPhrase.all', ['uuid' => $p->getUniqueId()->toString()]);
                            return array_column($rows, 'killPhrase');
                        })(),
                        (static function() use($p): Generator{
							/** @var array{array{cape: int, potColor: int, projectile: int, tag: string, artifact: int, killPhrase: int, chatColor: value-of<TextFormat::COLORS>}} $rows */
                            $rows = yield from OqexPractice::getDatabase()->asyncSelect('oqex-practice.cosmetics.equipped.all', ['uuid' => $p->getUniqueId()->toString()]);
                            return $rows[0];
                        })()
                    ]);
                    $cosmeticToId = static fn(CosmeticItem $artifact) => $artifact->getId();
                    $p->getData()->getCosmetics()->init($equipped, [
                        'tags' => $tags,
                        'artifacts' => array_map($cosmeticToId, $artifacts),
                        'capes' => array_map($cosmeticToId, $capes),
                        'projectiles' => array_map($cosmeticToId, $projectiles),
                        'killPhrases' => $killPhrases
                    ]);
                });
            }
        });
    }

    protected function prepare(): void
    {
        $this->setPermission('oqex');
        $this->registerArgument(0, new OfflinePlayerArgument('offlinePlayer', $this->values, true));
    }
}