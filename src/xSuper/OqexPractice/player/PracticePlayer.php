<?php

namespace xSuper\OqexPractice\player;

use Closure;
use DateTime;
use Generator;
use pocketmine\color\Color;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Skin;
use pocketmine\item\Armor;
use pocketmine\item\EnderPearl;
use pocketmine\item\GoldenAppleEnchanted;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\ChangeDimensionPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\types\DimensionIds;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\CancelTaskException;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\world\Position;
use Ramsey\Uuid\Uuid;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\SOFe\AwaitGenerator\Await;
use xSuper\OqexPractice\duel\Duel;
use xSuper\OqexPractice\duel\generator\maps\Map;
use xSuper\OqexPractice\duel\special\BotDuel;
use xSuper\OqexPractice\duel\type\Type;
use xSuper\OqexPractice\duel\type\Types;
use xSuper\OqexPractice\duel\utils\Elo;
use xSuper\OqexPractice\duel\utils\Leaderboard;
use xSuper\OqexPractice\duel\utils\LeaderboardIds;
use xSuper\OqexPractice\entities\FishingHookEntity;
use xSuper\OqexPractice\events\Event;
use xSuper\OqexPractice\ffa\FFA;
use xSuper\OqexPractice\ffa\OITCFFA;
use xSuper\OqexPractice\items\custom\InteractiveItems;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\party\Party;
use xSuper\OqexPractice\player\cosmetics\CosmeticManager;
use xSuper\OqexPractice\player\data\PlayerData;
use xSuper\OqexPractice\player\data\PlayerInfo;
use xSuper\OqexPractice\player\data\RankMap;
use xSuper\OqexPractice\player\kit\Kit;
use xSuper\OqexPractice\player\settings\SettingIDS;
use xSuper\OqexPractice\utils\scoreboard\Scoreboards;
use xSuper\OqexPractice\utils\SkinTask;
use xSuper\OqexPractice\utils\LocalAC;
use xSuper\OqexPractice\utils\TimeUtils;

class PracticePlayer extends Player
{
    private PlayerData $data;
    private bool $loaded = false;

    private ?Duel $duel = null;
    private ?FFA $ffa = null;
    private bool $canBeDamaged = false;
    private int $combat = 0;
    private ?PracticePlayer $tagger = null;
    private ?FishingHookEntity $fishing = null;
    private bool $staffMode = false;
    private bool $vanished = false;

    private bool $staffChat = false;

    private bool $frozen = false;

    public ?string $editKit = null;

    private int $pearlCD = 0;
    private bool $isSpectator = false;

    /** @var array<string, array{int<0, 60>, Type, Map}> */
    private array $requests = [];
    /** @var array<string, int<0, 60>> */
    private array $partyInvites = [];

    private ?string $party = null;

    private bool $canPlace = true;
    private string $scoreboard = '';
    private Skin $oldSkin;

    /** @phpstan-var LeaderboardIds::KILLS_* */
    private int $killsLeaderboard = LeaderboardIds::KILLS_LIFETIME;
    /** @phpstan-var LeaderboardIds::DEATHS_* */
    private int $deathsLeaderboard = LeaderboardIds::DEATHS_LIFETIME;
    /** @phpstan-var LeaderboardIds::KD_* */
    private int $kdLeaderboard = LeaderboardIds::KD_LIFETIME;
    /** @phpstan-var LeaderboardIds::AVERAGE_ELO|LeaderboardIds::NO_DEBUFF_ELO|LeaderboardIds::DEBUFF_ELO|LeaderboardIds::GAPPLE_ELO|LeaderboardIds::BUILD_UHC_ELO|LeaderboardIds::COMBO_ELO|LeaderboardIds::SUMO_ELO|LeaderboardIds::VANILLA_ELO|LeaderboardIds::ARCHER_ELO|LeaderboardIds::SOUP_ELO|LeaderboardIds::BRIDGE_ELO */
    private int $eloLeaderboard = LeaderboardIds::AVERAGE_ELO;
    /** @phpstan-var LeaderboardIds::PARKOUR_* */
    private int $parkourLeaderboard = LeaderboardIds::PARKOUR_LIFETIME;
    private bool $canSkin = true;
    private bool $openingPack = false;

    private ?Position $checkpoint = null;
    private ?float $parkour = null;
    private ?float $time = null;

    private ?int $arrow = null;
    private bool $agro = false;
    private ?Event $event = null;

    private ChatHandler $chatHandler;


    private string $botDiff = 'normal';
    private string $botType = 'NoDebuff';


    public function getBotDiff(): string
    {
        return $this->botDiff;
    }

    public function getBotType(): string
    {
        return $this->botType;
    }

    public function setBotDiff(string $diff): void
    {
        $this->botDiff = $diff;
    }

    public function setBotType(string $type): void
    {
        $this->botType = $type;
    }

    public function getScoreboard(): string
    {
        return $this->scoreboard;
    }

    public function setScoreboard(string $scoreboard): void
    {
        $this->scoreboard = $scoreboard;
    }

    public function getChatHandler(): ChatHandler
    {
        return $this->chatHandler;
    }

    public function preTeleport(Position $position): void
    {
        $this->parkour = null;
        $this->checkpoint = null;
        $this->time = null;

        if ($this->isOnline()) {
            $this->sendTip('');
            $this->teleport($position);
        }
    }

    public function banEvasion(): void
    {
        $pos = $this->getPosition();
        $packet = LevelSoundEventPacket::create(LevelSoundEvent::RECORD_RELIC, $pos, 0, '', false, false);

        $this->getNetworkSession()->sendDataPacket($packet);
        $s = 0;
        $this->sendSound('ambient.cave');
        OqexPractice::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function () use ($packet, &$s): void {
            if (!$this->isOnline()) {
                throw new CancelTaskException();
            }
            if ($s === 100) {
                $this->sendSound('nearby_close.warden');
            }

            if ($s === 160) {
                $this->sendSound('nearby_closer.warden');
            }

            if ($s === 200) {
                $this->sendSound('nearby_closest.warden');
            }

            if ($s === 220) {
                $this->sendSound('slightly_angry.warden');
            }
            $s += 20;
            $this->getEffects()->add(new EffectInstance(VanillaEffects::DARKNESS(), 300, 1, false, false));
            $this->sendTitle('§r§l§4Ba̷n Evasio͙n', '§r§7Leave.');

            if ($s === 60 * 20) {
                $this->getNetworkSession()->sendDataPacket(ChangeDimensionPacket::create(DimensionIds::NETHER, $this->getPosition(), false));
                throw new CancelTaskException();
            }
        }), 20);
    }

    public function setEvent(?Event $event): void
    {
        $this->event = $event;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setAgro(bool $agro): void
    {
        $this->agro = $agro;
        if ($agro) {
            OqexPractice::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function (): void {
                $this->agro = false;
            }), 20);
        }
    }

    public function isAgro(): bool
    {
        return $this->agro;
    }

    public function getParkour(): ?float
    {
        return $this->parkour;
    }

    public function startParkour(): void
    {
        $this->parkour = 0;

        $this->getInventory()->setContents([
            2 => InteractiveItems::CHECKPOINT()->getActualItem($this),
            6 => InteractiveItems::LEAVE_PARKOUR()->getActualItem($this)
        ]);

        OqexPractice::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
            if ((int) $this->parkour === -1) {
                $this->setParkourBest($this->time ?? throw new AssumptionFailedError('This should not be null'));
                $this->endParkour();
                throw new CancelTaskException();
            }

            if ($this->parkour === null) {
                $this->endParkour();
                throw new CancelTaskException();
            }

            $this->parkour = $this->parkour + 1;
            $this->time = $this->parkour;
            if ((int) $this->getParkourBest() === -1) $time = 'No Score.';
            else $time = gmdate('i:s', (int) $this->getParkourBest());
            if ($this->isOnline()) $this->sendTip('§r§l§fBEST TIME: §r§a' . $time . ' §r§8| §r§l§fATTEMPT: §r§a' . gmdate('i:s', (int) $this->parkour));
        }), 20);
    }

    public function setParkour(float $value): void
    {
        $this->parkour = $value;
    }

    public function endParkour(): void
    {
        $this->parkour = null;
        $this->checkpoint = null;
        $this->time = null;

        if ($this->isOnline()) {
            $this->sendTip('');
            $this->reset(OqexPractice::getInstance(), false);
        }
    }

    public function getParkourBest(): float
    {
        return $this->getData()->getParkour('lifetime');
    }

    public function setParkourBest(float $new): void
    {
		/** @phpstan-ignore-next-line */
        Await::f2c(function () use($new): Generator{
            $rows = yield from OqexPractice::getDatabase()->asyncSelect('oqex-practice.parkour.all.newBest', [
                'uuid' => $this->uuid->toString(),
                'best' => $new
            ]);
            foreach ([LeaderboardIds::DAILY, LeaderboardIds::WEEKLY, LeaderboardIds::MONTHLY, LeaderboardIds::LIFETIME] as $timeframe) {
                Leaderboard::updateParkour($timeframe);
            }

            $this->getData()->setParkour($rows[0]['best']);
        });
    }

    public function setCheckpoint(Position $position): void
    {
        $this->checkpoint = $position;
    }

    public function getCheckpoint(): ?Position
    {
        return $this->checkpoint;
    }

    public function setOpeningPack(bool $value): void
    {
        $this->openingPack = $value;
    }

    public function removePack(string $name): void
    {
        OqexPractice::getDatabase()->executeChange('oqex-practice.packs.remove', [
            'uuid' => $this->uuid->toString(),
            'pack' => $name
        ]);
    }

    public function arrowTask(): void
    {
        $this->arrow = 5 * 20;

        OqexPractice::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
            if ($this->arrow === null) {
                if ($this->isOnline()) {
                    $this->getXpManager()->setXpLevel(0);
                    $this->getXpManager()->setXpProgress(0);
                }

                throw new CancelTaskException();
            }

            if ($this->arrow === 0) {
                $this->arrow = null;

                if ($this->isOnline() && $this->getFFA() instanceof OITCFFA) {
                    $a = false;
                    foreach ($this->getInventory()->getContents(false) as $i) {
                        if ($i->getTypeId() === ItemTypeIds::ARROW) $a = true;
                    }

                    if (!$a) $this->getInventory()->setItem(8, VanillaItems::ARROW());
                    $this->getXpManager()->setXpLevel(0);
                    $this->getXpManager()->setXpProgress(0);
                }
                throw new CancelTaskException();
            }

            $c = (int)floor($this->arrow / 20);
            if ($c === 0) $c = 1;
            if ($this->isOnline()) {
                $this->getXpManager()->setXpLevel($c);
                $this->getXpManager()->setXpProgress($this->arrow / 100);
            }

            $this->arrow--;
        }), 1);
    }

    public function rmArrow(): void
    {
        $this->arrow = null;
    }

    public function isOpeningPack(): bool
    {
        return $this->openingPack;
    }

    public function setChangeSkin(bool $skin) : void{
        $this->canSkin = $skin;
        if(!$skin){
            new SkinTask($this);
        }
    }

    public function canChangeSkin() : bool{
        return $this->canSkin;
    }

    public function tryChangeSkin() : bool{
        if(!$this->canChangeSkin()) return false;
        return true;
    }


    public static function testForBan(string $pId): void
    {
		$pUuid = Uuid::fromString($pId);
        PlayerSqlHelper::banIfBannedAliasExists($pUuid,
			'perm',
			'Console',
			'Ban Evasion',
			true, function(bool $banned) use ($pUuid): void{
			if($banned){
				return;
			}

			$p = Server::getInstance()->getPlayerByUUID($pUuid);
			if(!$p instanceof PracticePlayer || !$p->isOnline()){
				return;
			}

			$p->finishLoading();
        });
    }

    public function init(): void
    {
        $this->chatHandler = new ChatHandler($this);

        $this->data = new PlayerData();
        $this->data->finish(function (): void {
            if ($this->isOnline()) {
                $banData = $this->data->getBanned();
                if ($banData !== null && count($banData) > 0) {
                    $c = true;
                    $duration = $banData['duration'];

                    if ($duration === 'perm') {
                        $s = '§r§cYou are permanently banned!';
                    } else {
                        $to = new DateTime();
                        $from = date_create_from_format('Y-m-d H-i-s', $duration);
                        if ($from <= $to) {
                            PlayerSqlHelper::unBan($this->getUniqueId()->toString());
							$this->getData()->setBanned(null);

                            $c = false;
                        }
                        else $s = '§r§cYou are banned for ' . TimeUtils::formatDate(new DateTime(), $from);
                    }


                    if ($c) {
                        $s .= "\n" . ' §fReason: ' . $banData['reason'] . ' [' . $banData['staff'] . "]\n";
                        echo "a1\n";
                        $this->kick($s);
                        return;
                    }
                }

                $info = PlayerInfo::getData($this->getUniqueId()->toString());
                $ip = $this->getNetworkSession()->getIp();
                $uuid = $this->getUniqueId()->toString();
                $xuid = $this->getXuid();
                OqexPractice::getInstance()->getDatabase()->executeSelect('oqex-practice.players.get_data', [
                    'uuid' => $this->getUniqueId()->toString()
                ], static function (array $rows) use ($info, $ip, $uuid, $xuid ): void {
                    $addresses = [];
                    $clientRandomIds = [];
                    $deviceIds = [];
                    $selfSignedIds = [];
                    $xuids = [];

                    $new = 0;
                    $rows[] = 'Placeholder';
                    foreach ($rows as $row) {
                        $addresses = array_merge($addresses, json_decode($row['Addresses'] ?? "[]", true));
                        $clientRandomIds = array_merge($clientRandomIds, json_decode($row['ClientRandomIds'] ?? "[]", true));
                        $deviceIds = array_merge($deviceIds, json_decode($row['DeviceIds'] ?? "[]", true));
                        $selfSignedIds = array_merge($selfSignedIds, json_decode($row['SelfSignedIds'] ?? "[]", true));
                        $xuids = array_merge($clientRandomIds, json_decode($row['Xuids'] ?? "[]", true));


                        if (!in_array(($h = hash('sha256', $ip)), $addresses)) {
                            $addresses = array_merge($addresses, [$h]);
                            $new++;
                        }

                        if (!in_array($info->getClientRandomId(), $clientRandomIds)) {
                            $clientRandomIds = array_merge($clientRandomIds, [$info->getClientRandomId()]);
                            $new++;
                        }
                        if (!in_array($info->getDeviceId(), $deviceIds)) {
                            $deviceIds = array_merge($deviceIds, [$info->getDeviceId()]);
                            $new++;
                        }
                        if (!in_array($info->getSelfSigned(), $selfSignedIds)) {
                            $selfSignedIds = array_merge($selfSignedIds, [$info->getSelfSigned()]);
                            $new++;
                        }
                        if (!in_array($xuid, $xuids)) {
                            $xuids = array_merge($xuids, [$xuid]);
                            $new++;
                        }
                    }

                    if ($new > 0) {
                        OqexPractice::getInstance()->getDatabase()->executeChange('oqex-practice.players.set_data', [
                            'uuid' => $uuid,
                            'addresses' => json_encode($addresses),
                            'clientRandomIds' => json_encode($clientRandomIds),
                            'deviceIds' => json_encode($deviceIds),
                            'selfSignedIds' => json_encode($selfSignedIds),
                            'xuids' => json_encode($xuids)
                        ], function () use ($uuid): void {
                            self::testForBan($uuid);
                        });
                    } else {
                        self::testForBan($uuid);
                    }
                });
            }
        });

        $this->sendMessage('§r§bYour data is loading..');
        $this->data->load($this->uuid);
    }

    public function finishLoading(): void
    {
        $this->setChangeSkin(false);
        CosmeticManager::applyCosmetics($this, $this->getSkin(), true);
        $this->sendSkin();

        Scoreboards::LOBBY()->send($this);

        $isHelper = $this->getData()->getRankPermission() >= RankMap::permissionMap('helper');

        foreach (Server::getInstance()->getOnlinePlayers() as $p) {
            /** @var PracticePlayer $p */
            $p->sendMessage('§r§7[§2+§7] §2' . $this->getName());
            if ($p->getVanished()) {
                if (!$isHelper) $this->hidePlayer($p);
                else $this->showPlayer($p);
            }
        }

        $this->reset(OqexPractice::getInstance());

        $this->loaded = true;
        $this->sendMessage('§r§bYour data has finished loading!');
    }

    public function isLoaded(): bool
    {
        return $this->loaded;
    }

    public function getData(): PlayerData
    {
        return $this->data;
    }

    public function getOldSkin(): Skin
    {
        return $this->oldSkin;
    }

    public function setOldSkin(Skin $skin): void
    {
        $this->oldSkin = $skin;
    }

    public function getEquipped(string $type, Closure $function): void
    {
		/** @phpstan-ignore-next-line */
        Await::f2c(function () use($function, $type): Generator{
            $rows = yield from OqexPractice::getDatabase()->asyncSelect("oqex-practice.cosmetics.equipped.$type.get", ['uuid' => $this->uuid->toString()]);
            $value = $rows[0][$type] ?? null;
            if($type === 'potColor'){
                $value = Color::fromRGB($rows[0]['potColor'] ?? (new Color(255, 0, 0))->toRGBA());
            }
            $function($value);
        });
    }

    public function setEquipped(string $type, int|string|Color $value): void
    {
        Await::f2c(function () use($type, $value): Generator{
            $encodedValue = $value;
            if($value instanceof Color){
                $encodedValue = $value->toRGBA();
            }
            yield from OqexPractice::getDatabase()->asyncInsert("oqex-practice.cosmetics.equipped.$type.set", [
                'uuid' => $this->uuid->toString(),
                $type => $encodedValue
            ]);
        });
    }

	/**
	 * @return LeaderboardIds::DEATHS_*
	 */
	public function getDeathsLeaderboard(): int{
		return $this->deathsLeaderboard;
	}

	/**
	 * @phpstan-param LeaderboardIds::DEATHS_* $deathsLeaderboard
	 */
	public function setDeathsLeaderboard(int $deathsLeaderboard): void{
		$this->deathsLeaderboard = $deathsLeaderboard;
	}

	/**
	 * @return LeaderboardIds::AVERAGE_ELO|LeaderboardIds::NO_DEBUFF_ELO|LeaderboardIds::DEBUFF_ELO|LeaderboardIds::GAPPLE_ELO|LeaderboardIds::BUILD_UHC_ELO|LeaderboardIds::COMBO_ELO|LeaderboardIds::SUMO_ELO|LeaderboardIds::VANILLA_ELO|LeaderboardIds::ARCHER_ELO|LeaderboardIds::SOUP_ELO|LeaderboardIds::BRIDGE_ELO
	 */
	public function getEloLeaderboard(): int{
		return $this->eloLeaderboard;
	}

	/**
	 * @phpstan-param LeaderboardIds::AVERAGE_ELO|LeaderboardIds::NO_DEBUFF_ELO|LeaderboardIds::DEBUFF_ELO|LeaderboardIds::GAPPLE_ELO|LeaderboardIds::BUILD_UHC_ELO|LeaderboardIds::COMBO_ELO|LeaderboardIds::SUMO_ELO|LeaderboardIds::VANILLA_ELO|LeaderboardIds::ARCHER_ELO|LeaderboardIds::SOUP_ELO|LeaderboardIds::BRIDGE_ELO $eloLeaderboard
	 */
	public function setEloLeaderboard(int $eloLeaderboard): void{
		$this->eloLeaderboard = $eloLeaderboard;
	}

	/**
	 * @return LeaderboardIds::KD_*
	 */
	public function getKdLeaderboard(): int{
		return $this->kdLeaderboard;
	}

	/**
	 * @phpstan-param LeaderboardIds::KD_* $kdLeaderboard
	 */
	public function setKdLeaderboard(int $kdLeaderboard): void{
		$this->kdLeaderboard = $kdLeaderboard;
	}

	/**
	 * @return LeaderboardIds::KILLS_*
	 */
	public function getKillsLeaderboard(): int{
		return $this->killsLeaderboard;
	}

	/**
	 * @phpstan-param LeaderboardIds::KILLS_* $killsLeaderboard
	 */
	public function setKillsLeaderboard(int $killsLeaderboard): void{
		$this->killsLeaderboard = $killsLeaderboard;
	}

	/**
	 * @return LeaderboardIds::PARKOUR_*
	 */
	public function getParkourLeaderboard(): int{
		return $this->parkourLeaderboard;
	}

	/**
	 * @phpstan-param LeaderboardIds::PARKOUR_* $parkourLeaderboard
	 */
	public function setParkourLeaderboard(int $parkourLeaderboard): void{
		$this->parkourLeaderboard = $parkourLeaderboard;
	}

	/** @phpstan-impure */
    public function getFFA(): ?FFA
    {
        return $this->ffa;
    }

    public function setFFA(?FFA $ffa): void
    {
        $this->ffa = $ffa;
    }

    public function setCanPlace(bool $value): void
    {
        $this->canPlace = $value;
    }

    public function canPlace(): bool
    {
        return $this->canPlace;
    }

    public function addRequest(Player $sender, Type $type, Map $map): void
    {
        if (!$this->getData()->getSettings()->asBool(SettingIDS::DUEL_REQUESTS)) {
            $sender->sendMessage('§r§cThat player is not accepting duel requests!');
            return;
        }

        $this->requests[$sender->getName()] = [60, $type, $map];
        $this->sendMessage("\n§r§l§c(!) §r§7You have a duel request §l§c(!)\n\n§r§8 - §r§7Type: §a" . $type->getName() . "\n§r§8 - §r§7Map: §a" . $map->getName() . "\n\n§r§7Run §b/duel " . $sender->getName() . " §7 to accept!\n\n");

        OqexPractice::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function () use ($sender): void {
            if (!isset($this->requests[$sender->getName()])) throw new CancelTaskException();

            if ($this->requests[$sender->getName()][0] === 0) {
                unset($this->requests[$sender->getName()]);
                throw new CancelTaskException();
            }

            $this->requests[$sender->getName()][0]--;
        }), 20);
    }

    public function hasRequest(PracticePlayer $player): bool
    {
        foreach ($this->requests as $name => $time) {
            if ($player->getName() === $name) return true;
        }

        return false;
    }

    public function acceptRequest(PracticePlayer $player): void
    {
        $type = $this->requests[$player->getName()][1];
        $map = $this->requests[$player->getName()][2];

        if (isset($this->requests[$player->getName()])) unset($this->requests[$player->getName()]);

        Duel::createDuel(OqexPractice::getInstance(), $type, [$this, $player], false, $map);
    }

    public function addPartyInvite(Party $party): void
    {
        $p = Server::getInstance()->getPlayerByUUID(Uuid::fromString($party->getOwner()));
        if ($p === null || !$p->isOnline()) return;
        $this->partyInvites[$party->getId()] = 60;
        $this->sendMessage('§r§l§dPARTY §r§8» §7You have a party invite from §d' . $p->getName() . '§7, join using §d/party join ' . $p->getName());

        OqexPractice::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function () use ($party): void {
            if (!isset($this->partyInvites[$party->getId()])) throw new CancelTaskException();

            if ($this->partyInvites[$party->getId()] === 0) {
                unset($this->partyInvites[$party->getId()]);
                throw new CancelTaskException();
            }

            $this->partyInvites[$party->getId()]--;
        }), 20);
    }

    public function hasPartyInvite(PracticePlayer $player): bool
    {
        foreach ($this->partyInvites as $id => $time) {
            $party = Party::getParty($id);
            if ($party !== null && $party->isMember($player->getUniqueId()->toString())) return true;
        }

        return false;
    }

    public function hasPartyInviteById(string $id): bool
    {
        return isset($this->partyInvites[$id]);
    }

    public function acceptPartyInviteById(string $id): void
    {
        $party = Party::getParty($id);

        if ($party === null) return;
        if (isset($this->partyInvites[$party->getId()])) unset($this->partyInvites[$party->getId()]);
        $party->addPlayer($this);
    }


    public function acceptPartyInvite(PracticePlayer $player): void
    {
        $p = $player->getParty();

        if ($p === null) return;

        $party = Party::getParty($p);

        if ($party === null) return;
        if (isset($this->partyInvites[$party->getId()])) unset($this->partyInvites[$party->getId()]);
        $party->addPlayer($this);
    }

	/** @return array<string, int<0, 60>> */
    public function getPartyInvites(): array
    {
        return $this->partyInvites;
    }

    public function getParty(): ?string
    {
        return $this->party;
    }

    public function setParty(?string $party): void
    {
        $this->party = $party;
    }

    public function getSpectator(): bool
    {
        return $this->isSpectator;
    }

    public function pearl(): void
    {
        $this->pearlCD = 15 * 20;

        OqexPractice::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
            if ($this->pearlCD === 0) {
                if ($this->isOnline()) {
                    $this->getXpManager()->setXpLevel(0);
                    $this->getXpManager()->setXpProgress(0);
                }
                throw new CancelTaskException();
            }

            $c = (int)floor($this->pearlCD / 20);
            if ($c === 0) $c = 1;
            if ($this->isOnline()) {
                $this->getXpManager()->setXpLevel($c);
                $this->getXpManager()->setXpProgress($this->pearlCD / 300);
            }

            $this->pearlCD--;
        }), 1);
    }

    public function rmPearl(): void
    {
        $this->pearlCD = 0;
    }

    public function canPearl(): bool
    {
        return $this->pearlCD === 0;
    }

    public function spectator(bool $item = false): void
    {
        if ($this->isOnline()) {
            foreach (Server::getInstance()->getOnlinePlayers() as $p) {
                if ($p->spawned && $p->isOnline()) {
                    $p->hidePlayer($this);
                }
            }

            $this->setSilent();
            $this->extinguish();
            $this->getInventory()->clearAll();
            $this->getArmorInventory()->clearAll();
            $this->getCursorInventory()->clearAll();
            $this->getOffHandInventory()->clear(0);
            $this->getHungerManager()->setFood($this->getHungerManager()->getMaxFood());
            $this->setHealth($this->getMaxHealth());
            $this->getEffects()->clear();
            $this->setAbsorption(0);
            $this->setGamemode(GameMode::ADVENTURE());
            $this->removeCombatTag();

            $this->setCanBeDamaged(false);
            $this->setAllowFlight(true);
            $this->setFlying(true);

            $this->isSpectator = true;
        }
    }

    public function freeze(): void
    {
        $this->frozen = true;

        $this->setNoClientPredictions();

        OqexPractice::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
            if ($this->isOnline()) {
                $this->sendTitle('§r§l§cYou are frozen!');
                $this->sendSubTitle('§r§7Do not log out!');

                if (!$this->frozen) {
                    $this->sendTitle('§r§l§bYou are thawed!');
                    $this->sendSubTitle('§r§7You may safely log out!');

                    OqexPractice::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function (): void {
                        if ($this->isOnline()) $this->sendTitle('§r');
                    }), 20);

                    throw new CancelTaskException();
                }
            }
        }), 40);
    }

    public function unFreeze(): void
    {
        $this->setNoClientPredictions(false);
        $this->frozen = false;
    }

    public function isFrozen(): bool
    {
        return $this->frozen;
    }

    public function getVanished(): bool
    {
        return $this->vanished;
    }

    public function vanish(bool $notify = false): void
    {
        foreach (Server::getInstance()->getOnlinePlayers() as $p) {
            if ($p instanceof PracticePlayer && $p->isOnline() && $p->isLoaded()) {
                if ($p->getData()->getRankPermission() < RankMap::permissionMap('helper')) $p->hidePlayer($this);
                else $p->showPlayer($this);
            }
        }

        $this->vanished = true;

        $this->setSilent();
        if ($notify) $this->sendTip('§r§aYou are now vanished!');
    }

    public function unVanish(bool $notify = false): void
    {
        foreach (Server::getInstance()->getOnlinePlayers() as $p) {
            if ($p->spawned && $p->isOnline()) {
                $p->showPlayer($this);
            }
        }

        $this->vanished = false;

        $this->setSilent(false);
        if ($notify) $this->sendTip('§r§cYou are no longer vanished!');
    }

    public function setStaffMode(bool $value): void
    {
        $this->staffMode = $value;
        if ($value) $this->sendMessage('§r§l§eSTAFF §r§8» §rYou are now in staff mode');
        else $this->sendMessage('§r§l§eSTAFF §r§8» §rYou are no longer in staff mode');
    }

    public function getStaffMode(): bool
    {
        return $this->staffMode;
    }

    public function setStaffChat(bool $value): void
    {
        $this->staffChat = $value;
    }

    public function getStaffChat(): bool {
        return $this->staffChat;
    }

    public function getDuel(): ?Duel
    {
        return $this->duel;
    }

    public function setDuel(?Duel $duel): void
    {
        $this->duel = $duel;
    }

    public function canBeDamaged(): bool
    {
        return $this->canBeDamaged;
    }

    public function setCanBeDamaged(bool $value): void
    {
        $this->canBeDamaged = $value;
    }

    public function setElo(Type $type, int $amount): void
    {
		$name = $type->getName();
		if(!in_array($name, Elo::LADDERS, true)){
			throw new AssumptionFailedError("Unknown ladder $name");
		}
        OqexPractice::getDatabase()->executeChange('oqex-practice.elos.set', [
            'uuid' => $this->getUniqueId()->toString(),
            'ladder' => $name,
            'elo' => $amount
        ], static function() use ($name): void{
            Leaderboard::updateAverageElo();
            Leaderboard::updateElo($name);
        });
    }

    public function subtractRankedGame(): void
    {
        $eGames = $this->getData()->getExtraRankedGames();
        $rGames = $this->getData()->getRankedGames();
        if ($rGames > 0) {
            $rGames--;
			$this->getData()->setRankedGames($rGames);
        } else if ($eGames !== 0) {
            $eGames--;
			$this->getData()->setExtraRankedGames($eGames);
        }
		OqexPractice::getDatabase()->executeGeneric('oqex-practice.players.decrease_ranked_game', [
			'uuid' => $this->getUniqueId()->toString()
		]);
    }

    public function giveCombatTag(PluginBase $plugin, PracticePlayer $tagger): void
    {
        $this->tagger = $tagger;

        if ($this->ffa !== null && $this->getData()->getSettings()->asBool(SettingIDS::HIDE_PLAYERS_AT_FFA)) {
            foreach ($this->ffa->getSpawn()->getWorld()->getPlayers() as $p) {
                if ($p->getId() !== $tagger->getId()) $this->hidePlayer($p);
            }
        }

        if ($this->combat <= 0) {
            $this->combat = 15;
            $this->combatTask($plugin);
        } else $this->combat = 15;
    }

    public function combatTask(PluginBase $plugin): void
    {
        $plugin->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
            if ($this->combat <= 0) {
                $this->removeCombatTag();
                throw new CancelTaskException();
            }

            $this->combat--;
        }), 20);
    }

    public function getCombat(): int
    {
        return $this->combat;
    }

    public function getTagger(): ?PracticePlayer
    {
        return $this->tagger;
    }

    public function removeCombatTag(): void
    {
        $this->combat = 0;
        $this->tagger = null;

        if ($this->ffa !== null && $this->getData()->getSettings()->asBool(SettingIDS::HIDE_PLAYERS_AT_FFA)) {
            foreach ($this->ffa->getSpawn()->getWorld()->getPlayers() as $p) {
                $this->showPlayer($p);
            }
        }
    }

	/** @return array{float, float} */
    private static function maxMin(float $first, float $second) : array{
        return $first > $second ? [$first, $second] : [$second, $first];
    }

    public function knockBack(float $x, float $z, float $force = 0.4, ?float $verticalLimit = 0.4): void
    {
        $f = sqrt($x * $x + $z * $z);
        if($f <= 0){
            return;
        }

        $xzKb = $force;
        $yKb = $force;

        if (($duel = $this->getDuel()) !== null) {
            if ($duel instanceof BotDuel) $type = Types::NO_DEBUFF();
            else $type = $duel->getType();

            $xzKb = $type->getKB()['xzKb'];
            $yKb = $type->getKB()['yKb'];
            $maxHeight = $type->getKB()['maxHeight'];
            $revert = $type->getKB()['revert'];
        } else if (($ffa = $this->getFFA()) !== null) {
            $xzKb = $ffa->getKB()['xzKb'];
            $yKb = $ffa->getKB()['yKb'];
            $maxHeight = $ffa->getKB()['maxHeight'];
            $revert = $ffa->getKB()['revert'];
        } else {
            $maxHeight = 3;
            $revert = 0.75;
        }

        if(!$this->isOnGround() && $maxHeight > 0){
            $entity = $this->getTagger();
            if ($entity !== null) {
                [$max, $min] = self::maxMin($this->getPosition()->getY(), $entity->getPosition()->getY());
                if ($max - $min >= $maxHeight) {
                    $yKb *= $revert;
                }
            }
        }
        if($this->isAgro()){
            $xzKb *= 0.85;
            $yKb *= 0.85;
            $this->setAgro(false);
        }

        if(mt_rand() / mt_getrandmax() > $this->knockbackResistanceAttr->getValue()){
            $f = 1 / $f;
            $motion = clone $this->motion;
            $motion->x /= 2;
            $motion->y /= 2;
            $motion->z /= 2;
            $motion->x += $x * $f * $xzKb;
            $motion->y += $yKb;
            $motion->z += $z * $f * $xzKb;
            if($motion->y > $yKb){
                $motion->y = $yKb;
            }

            $this->setMotion($motion);
        }
    }

    public function giveKit(string $name, bool $ffa = false, bool $event = false): void
    {
        if (!$this->isOnline()) return;

        $kit = Kit::getKit($name);
        if ($kit !== null) {
            $this->getArmorInventory()->setContents($kit->getArmor());
            $this->getInventory()->setContents($this->getData()->getKit($name));

            foreach ($kit->getEffects() as $effect) {
                $this->getEffects()->add($effect);
            }

            if ($event && in_array($name, ['NoDebuff', 'Debuff', 'Vanilla', 'Combo'], true)) {
                foreach ($this->getInventory()->getContents() as $s => $i) {
                    if ($i instanceof EnderPearl) {
                        $this->getInventory()->setItem($s, $i->setCount(5));
                    }

                    if ($i instanceof Armor) {
                        $this->getInventory()->setItem($s, VanillaItems::AIR());
                    }

                    if ($i instanceof GoldenAppleEnchanted) {
                        $this->getInventory()->setItem($s, $i->setCount(10));
                    }
                }
            }
        }
    }

    public function reset(PluginBase $plugin, bool $teleport = true): void
    {
        $plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(function (): void {
            if ($this->isOnline()) {
                $this->extinguish();
                $this->getInventory()->clearAll();
                $this->unVanish();
                $this->getArmorInventory()->clearAll();
                $this->getCursorInventory()->clearAll();
                $this->getOffHandInventory()->clear(0);
                $this->getHungerManager()->setFood($this->getHungerManager()->getMaxFood());
                $this->setHealth($this->getMaxHealth());
                $this->setAbsorption(0);
                $this->setGamemode(GameMode::ADVENTURE());
                $this->setFlying(false);
                $this->setAllowFlight(false);
                $this->setInvisible(false);
                $this->setCanBeDamaged(false);
                $this->setSilent(false);
                $this->setNameTagVisible();
                $this->setNameTagAlwaysVisible();
                $this->sendTitle('§r');
                $this->setMaxHealth(20);
                RankMap::formatTag($this);
                $this->tagger?->removeCombatTag();
                $this->removeCombatTag();
                Scoreboards::LOBBY()->send($this);

                $this->ffa = null;
                $this->duel = null;
                $this->isSpectator = false;
                $this->event = null;
                $this->rmPearl();
                $this->rmArrow();

                foreach (Server::getInstance()->getOnlinePlayers() as $p) {
                    if ($p instanceof PracticePlayer && $p->isOnline() && $p->isLoaded()) {
                        if (!$p->getData()->getSettings()->asBool(SettingIDS::HIDE_PLAYERS_AT_SPAWN) && !$this->vanished) $p->showPlayer($this);
                        else $p->hidePlayer($this);
                    }
                }

                $this->getInventory()->setContents([
                    0 => InteractiveItems::DUEL_SWORD()->getActualItem($this),
                    1 => InteractiveItems::RANKED_DUEL_SWORD()->getActualItem($this),
                    3 => InteractiveItems::FFA()->getActualItem($this),
                    5 => InteractiveItems::EVENT()->getActualItem($this),
                    7 => InteractiveItems::PARTY()->getActualItem($this),
                    8 => InteractiveItems::PROFILE()->getActualItem($this)
                ]);
            }
        }), 1);

        if ($teleport) {
            $plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(function (): void {
                if ($this->isOnline()) {
                    $this->getEffects()->clear();
                    $this->preTeleport(new Position(-99.5, 74, 0.5, Server::getInstance()->getWorldManager()->getDefaultWorld()));
                }
            }), 2);
        }
    }


    public function sendSound(string $soundName, float $pitch = 1, float $volume = 1): void
    {
        $pos = $this->getPosition();

        $pk = new PlaySoundPacket();
        $pk->soundName = $soundName;
        $pk->x = $pos->x;
        $pk->y = $pos->y;
        $pk->z = $pos->z;
        $pk->pitch = $pitch;
        $pk->volume = $volume;

        $this->getNetworkSession()->sendDataPacket($pk);
    }

    public function startFishing(FishingHookEntity $entity): void
    {
        if ($this->isOnline()) {
            if (!$this->isFishing()) {
                $this->fishing = $entity;
            }
        }
    }

    public function getFishing(): ?FishingHookEntity
    {
        return $this->fishing;
    }

    public function stopFishing(): void
    {
        if ($this->isFishing()) {
            $this->fishing = null;
        }
    }

    public function isFishing(): bool
    {
        return $this->fishing !== null;
    }

    /**
    _____  ____  _
    / ____|/ __ \| |
    | (___ | |  | | |
    \___ \| |  | | |
    ____) | |__| | |____
    |_____/ \___\_\______|
     */

    public function addKill(): void
    {
        OqexPractice::getDatabase()->executeChange('oqex-practice.kills.all.increment', ['uuid' => $this->uuid->toString()]);
        foreach ([LeaderboardIds::DAILY, LeaderboardIds::WEEKLY, LeaderboardIds::MONTHLY, LeaderboardIds::LIFETIME] as $timeframe) {
            Leaderboard::updateKills($timeframe);
            Leaderboard::updateKD($timeframe);
        }
        $this->getData()->addKill();
    }

    public function addDeath(): void
    {
        OqexPractice::getDatabase()->executeChange('oqex-practice.deaths.all.increment', ['uuid' => $this->uuid->toString()]);
        foreach ([LeaderboardIds::DAILY, LeaderboardIds::WEEKLY, LeaderboardIds::MONTHLY, LeaderboardIds::LIFETIME] as $timeframe) {
            Leaderboard::updateDeaths($timeframe);
            Leaderboard::updateKD($timeframe);
        }
        $this->getData()->addDeath();
    }
}