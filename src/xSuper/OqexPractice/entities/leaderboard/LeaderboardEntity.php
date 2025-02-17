<?php

namespace xSuper\OqexPractice\entities\leaderboard;

use DaveRandom\CallbackValidator\BuiltInTypes;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Location;
use pocketmine\entity\object\FallingBlock;
use pocketmine\entity\Zombie;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\types\entity\ByteMetadataProperty;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\entity\FloatMetadataProperty;
use pocketmine\network\mcpe\protocol\types\entity\IntMetadataProperty;
use pocketmine\network\mcpe\protocol\types\entity\LongMetadataProperty;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\network\mcpe\protocol\types\entity\StringMetadataProperty;
use pocketmine\player\Player;
use xSuper\OqexPractice\duel\utils\Leaderboard;
use xSuper\OqexPractice\duel\utils\LeaderboardIds;
use xSuper\OqexPractice\player\PracticePlayer;

class LeaderboardEntity extends FallingBlock implements LeaderboardIds
{
    public bool $gravityEnabled = false;

	/** @phpstan-param LeaderboardIds::KILLS|LeaderboardIds::DEATHS|LeaderboardIds::KD|LeaderboardIds::ELO|LeaderboardIds::PARKOUR $leaderboard */
    public function __construct(private int $leaderboard, Location $location, ?CompoundTag $nbt = null)
    {
        parent::__construct($location, VanillaBlocks::AIR(), $nbt);

        $this->setScale(0.5);
		$this->setCanSaveWithChunk(false);
    }

    public function onUpdate(int $currentTick): bool
    {
        return false;
    }

    public function sendSpawnPacket(Player $player) : void
    {
        /** @var PracticePlayer $player */

        /** @var list<ClientboundPacket> $pks */
        $pks = [];

        $pks[] = RemoveActorPacket::create($this->getId());

            $actorFlags = (
                1 << EntityMetadataFlags::NO_AI
            );
            $actorMetadata = [
                EntityMetadataProperties::FLAGS => new LongMetadataProperty($actorFlags),
                EntityMetadataProperties::SCALE => new FloatMetadataProperty(2.5), //zero causes problems on debug builds
                EntityMetadataProperties::BOUNDING_BOX_WIDTH => new FloatMetadataProperty(2.5),
                EntityMetadataProperties::BOUNDING_BOX_HEIGHT => new FloatMetadataProperty(0.6),
                EntityMetadataProperties::NAMETAG => new StringMetadataProperty($this->format($player)),
                EntityMetadataProperties::VARIANT => new IntMetadataProperty(TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId(VanillaBlocks::AIR()->getStateId())),
                EntityMetadataProperties::ALWAYS_SHOW_NAMETAG => new ByteMetadataProperty(1),
            ];
            $pks[] = AddActorPacket::create(
                $this->getId(),
                $this->getId(),
                EntityIds::FALLING_BLOCK,
                $this->getPosition()->asVector3(),
                null,
                0,
                0,
                0,
                0,
                [],
                $actorMetadata,
                new PropertySyncData([], []),
                []
            );

        foreach ($pks as $pk) $player->getNetworkSession()->sendDataPacket($pk);
    }

    public function attack(EntityDamageEvent $source): void
    {
        if ($source instanceof EntityDamageByEntityEvent) {
            if ($source->getDamager() instanceof PracticePlayer) {
                $this->setNextType($source->getDamager());
                $this->despawnFrom($source->getDamager());
                $this->spawnTo($source->getDamager());
            }
        }

        $source->cancel();
    }

    public function format(PracticePlayer $player): string
    {
        $current = match ($this->leaderboard){
			self::KILLS => $player->getKillsLeaderboard(),
			self::DEATHS => $player->getDeathsLeaderboard(),
			self::KD => $player->getKdLeaderboard(),
			self::ELO => $player->getEloLeaderboard(),
			self::PARKOUR => $player->getParkourLeaderboard()
		};

        $data = Leaderboard::getLeaderboard($current)->getData();

        $s = match ($current) {
            self::KILLS_LIFETIME => '§r§l§6Lifetime Top Kills',
            self::KILLS_MONTHLY => '§r§l§6Monthly Top Kills',
            self::KILLS_WEEKLY => '§r§l§6Weekly Top Kills',
            self::KILLS_DAILY => '§r§l§6Daily Top Kills',
            self::DEATHS_LIFETIME => '§r§l§6Lifetime Top Deaths',
            self::DEATHS_MONTHLY => '§r§l§6Monthly Top Deaths',
            self::DEATHS_WEEKLY => '§r§l§6Weekly Top Deaths',
            self::DEATHS_DAILY => '§r§l§6Daily Top Deaths',
            self::KD_LIFETIME => '§r§l§6Lifetime Top K/D Ratio',
            self::KD_MONTHLY => '§r§l§6Monthly Top K/D Ratio',
            self::KD_WEEKLY => '§r§l§6Weekly Top K/D Ratio',
            self::KD_DAILY => '§r§l§6Daily Top K/D Ratio',
            self::AVERAGE_ELO => '§r§l§6Top Average Elo',
            self::NO_DEBUFF_ELO => '§r§l§6Top NoDebuff Elo',
            self::DEBUFF_ELO => '§r§l§6Top Debuff Elo',
            self::GAPPLE_ELO => '§r§l§6Top Gapple Elo',
            self::BUILD_UHC_ELO => '§r§l§6Top BuildUHC Elo',
            self::COMBO_ELO => '§r§l§6Top Combo Elo',
            self::SUMO_ELO => '§r§l§6Top Sumo Elo',
            self::VANILLA_ELO => '§r§l§6Top Vanilla Elo',
            self::ARCHER_ELO => '§r§l§6Top Archer Elo',
            self::SOUP_ELO => '§r§l§6Top Soup Elo',
            self::BRIDGE_ELO => '§r§l§6Top Bridge Elo',
            self::PARKOUR_LIFETIME => '§r§l§6Lifetime Top Parkour Time',
            self::PARKOUR_MONTHLY => '§r§l§6Monthly Top Parkour Time',
            self::PARKOUR_DAILY => '§r§l§6Daily Top Parkour Time',
            self::PARKOUR_WEEKLY => '§r§l§6Weekly Top Parkour Time'
        };

        $s .= "\n \n";

        $rank = 0;
        foreach ($data as $r) {
            $rank++;

            if ($rank > 10) break;

            $color = match ($rank) {
                1 => '§6',
                2 => '§e',
                3 => '§f',
                default => '§7'
            };

            $s .= ' §r' . $color . $rank . '. ' . $r[0] . ': §e' . $r[1] . "\n";
        }

        if ($rank < 10) {
            for ($x = $rank + 1; $x <= 10; $x++) {
                $s .= ' §r§7' . $x . '. Unknown:§e ?' . "\n";
            }
        }

        $s .= "\n  \n§e» Click to toggle «";

        return $s;
    }


    public function setNextType(PracticePlayer $player): void
    {
		switch($this->leaderboard){
			case self::KILLS:
				$player->setKillsLeaderboard(match ($player->getKillsLeaderboard()) {
					self::KILLS_LIFETIME => self::KILLS_MONTHLY,
					self::KILLS_MONTHLY => self::KILLS_WEEKLY,
					self::KILLS_WEEKLY => self::KILLS_DAILY,
					self::KILLS_DAILY => self::KILLS_LIFETIME
				});
				return;
			case self::DEATHS:
				$player->setDeathsLeaderboard(match ($player->getDeathsLeaderboard()) {
					self::DEATHS_LIFETIME => self::DEATHS_MONTHLY,
					self::DEATHS_MONTHLY => self::DEATHS_WEEKLY,
					self::DEATHS_WEEKLY => self::DEATHS_DAILY,
					self::DEATHS_DAILY => self::DEATHS_LIFETIME
				});
				return;
			case self::KD:
				$player->setKdLeaderboard(match ($player->getKdLeaderboard()) {
					self::KD_LIFETIME => self::KD_MONTHLY,
					self::KD_MONTHLY => self::KD_WEEKLY,
					self::KD_WEEKLY => self::KD_DAILY,
					self::KD_DAILY => self::KD_LIFETIME
				});
				return;
			case self::ELO:
				$player->setEloLeaderboard(match ($player->getEloLeaderboard()) {
					self::AVERAGE_ELO => self::NO_DEBUFF_ELO,
					self::NO_DEBUFF_ELO => self::DEBUFF_ELO,
					self::DEBUFF_ELO => self::GAPPLE_ELO,
					self::GAPPLE_ELO => self::BUILD_UHC_ELO,
					self::BUILD_UHC_ELO => self::COMBO_ELO,
					self::COMBO_ELO => self::SUMO_ELO,
					self::SUMO_ELO => self::VANILLA_ELO,
					self::VANILLA_ELO => self::ARCHER_ELO,
					self::ARCHER_ELO => self::SOUP_ELO,
					self::SOUP_ELO => self::BRIDGE_ELO,
					self::BRIDGE_ELO => self::AVERAGE_ELO
				});
				return;
			case self::PARKOUR:
				$player->setParkourLeaderboard(match ($player->getParkourLeaderboard()) {
					self::PARKOUR_LIFETIME => self::PARKOUR_MONTHLY,
					self::PARKOUR_MONTHLY => self::PARKOUR_WEEKLY,
					self::PARKOUR_WEEKLY => self::PARKOUR_DAILY,
					self::PARKOUR_DAILY => self::PARKOUR_LIFETIME
				});
		}
    }
}