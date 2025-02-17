<?php

namespace xSuper\OqexPractice;

use pocketmine\entity\EntityDataHelper as Helper;
use pocketmine\network\mcpe\convert\SkinAdapter;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\scheduler\ClosureTask;
use xSuper\OqexPractice\commands\misc\KitCommand;
use xSuper\OqexPractice\commands\misc\MessageCommand;
use xSuper\OqexPractice\commands\misc\RulesCommand;
use xSuper\OqexPractice\commands\misc\StatsCommand;
use xSuper\OqexPractice\commands\staff\FreezeCommand;
use xSuper\OqexPractice\commands\staff\StaffChatCommand;
use xSuper\OqexPractice\duel\utils\Elo;
use xSuper\OqexPractice\entities\firework\FireworkRocket;
use xSuper\OqexPractice\entities\VotePartySheepEntity;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\exception\HookAlreadyRegistered;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\CortexPE\Commando\PacketHooker;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\data\bedrock\PotionTypeIdMap;
use pocketmine\data\bedrock\PotionTypeIds;
use pocketmine\data\SavedDataLoadingException;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\Arrow;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\world\generator\GeneratorManager;
use pocketmine\world\World;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\poggit\libasynql\DataConnector;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\poggit\libasynql\generic\GenericStatementImpl;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\poggit\libasynql\libasynql;
use xSuper\OqexPractice\commands\BotCommand;
use xSuper\OqexPractice\commands\cosmetics\CosmeticsCommand;
use xSuper\OqexPractice\commands\defaults\ClearCommand;
use xSuper\OqexPractice\commands\defaults\DeopCommand;
use xSuper\OqexPractice\commands\defaults\EffectCommand;
use xSuper\OqexPractice\commands\defaults\EnchantCommand;
use xSuper\OqexPractice\commands\defaults\GamemodeCommand;
use xSuper\OqexPractice\commands\defaults\GiveCommand;
use xSuper\OqexPractice\commands\defaults\OpCommand;
use xSuper\OqexPractice\commands\defaults\StatusCommand;
use xSuper\OqexPractice\commands\duel\SpectateCommand;
use xSuper\OqexPractice\commands\DuelCommand;
use xSuper\OqexPractice\commands\misc\EventCommand;
use xSuper\OqexPractice\commands\misc\ExtraRankedGamesCommand;
use xSuper\OqexPractice\commands\misc\KnockbackCommand;
use xSuper\OqexPractice\commands\misc\PingCommand;
use xSuper\OqexPractice\commands\misc\RestartCommand;
use xSuper\OqexPractice\commands\party\PartyCommand;
use xSuper\OqexPractice\commands\SettingsCommand;
use xSuper\OqexPractice\commands\SpawnCommand;
use xSuper\OqexPractice\commands\staff\AddCoinsCommand;
use xSuper\OqexPractice\commands\staff\BanCommand;
use xSuper\OqexPractice\commands\staff\KickCommand;
use xSuper\OqexPractice\commands\staff\ResetStatsCommand;
use xSuper\OqexPractice\commands\staff\SetRankCommand;
use xSuper\OqexPractice\commands\staff\StaffCommand;
use xSuper\OqexPractice\commands\staff\TeleportCommand;
use xSuper\OqexPractice\commands\staff\UnbanCommand;
use xSuper\OqexPractice\duel\generator\maps\Map;
use xSuper\OqexPractice\duel\generator\VoidGenerator;
use xSuper\OqexPractice\duel\type\Type;
use xSuper\OqexPractice\duel\utils\Leaderboard;
use xSuper\OqexPractice\entities\ArrowEntity;
use xSuper\OqexPractice\entities\custom\CustomEntity;
use xSuper\OqexPractice\entities\EnderPearlEntity;
use xSuper\OqexPractice\entities\FishingHookEntity;
use xSuper\OqexPractice\entities\OqexPackEntity;
use xSuper\OqexPractice\entities\PackItemEntity;
use xSuper\OqexPractice\entities\ServerSelectorNPC;
use xSuper\OqexPractice\entities\SplashPotionEntity;
use xSuper\OqexPractice\entities\TopEloPlayerEntity;
use xSuper\OqexPractice\events\EventManger;
use xSuper\OqexPractice\ffa\FFA;
use xSuper\OqexPractice\items\custom\CustomItem;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\poggit\libasynql\SqlDialect;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\poggit\libasynql\SqlThread;
use xSuper\OqexPractice\listeners\DamageListener;
use xSuper\OqexPractice\listeners\NetworkListener;
use xSuper\OqexPractice\listeners\OverrideListener;
use xSuper\OqexPractice\listeners\PlayerListener;
use xSuper\OqexPractice\listeners\WorldProtectionListener;
use xSuper\OqexPractice\player\cosmetic\misc\pack\PackHelper;
use xSuper\OqexPractice\player\cosmetics\CosmeticManager;
use xSuper\OqexPractice\player\data\RankMap;
use xSuper\OqexPractice\player\kit\Kit;
use xSuper\OqexPractice\player\PlayerSqlHelper;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\utils\PersonaSkinAdapter;
use xSuper\OqexPractice\utils\TimeUtils;


class OqexPractice extends PluginBase
{
    public const VOTE_LINK = 'versai.pro/vote';
    public const STORE_LINK = 'shop.versai.pro';
    public const IP = 'versai.pro';
    public const NAME = 'Versai';
    public const MOTD = '§r§l§bVersai';

    public const DAILY = 0;
    public const WEEKLY = 1;
    public const MONTHLY = 2;

    private static DataConnector $db;
    private static self $instance;

    private EventManger $eventManger;

    public function onDisable(): void
    {
        if($this->originalAdaptor !== null){
            TypeConverter::getInstance()->setSkinAdapter($this->originalAdaptor);
        }

        self::$db->waitAll();
        self::$db->close();

        TopEloPlayerEntity::closeCurrent();
    }

    private ?SkinAdapter $originalAdaptor = null;

    public function onEnable(): void
    {
        $typeConverter = TypeConverter::getInstance();

        $this->originalAdaptor = $typeConverter->getSkinAdapter();
        $typeConverter->setSkinAdapter(new PersonaSkinAdapter());

        Kit::init();

        $this->getServer()->getWorldManager()->getDefaultWorld()->setTime(6000);
        $this->getServer()->getWorldManager()->getDefaultWorld()->stopTime();
        $this->getServer()->getNetwork()->setName(self::MOTD);

        GeneratorManager::getInstance()->addGenerator(VoidGenerator::class, 'void', fn() => null, true);

        self::$instance = $this;

        self::$db = libasynql::create($this, $this->getConfig()->get('database'), [
            'sqlite' => 'sqlite.sql',
            'mysql' => 'mysql.sql'
        ]);

		self::$db->executeGeneric('oqex-practice.ladders.init');
		/** @noinspection Annotator */
		$query = 'INSERT INTO ladders(ladder) VALUES';
		$vars = [];
		$args = [];
		$query .= implode(', ', array_map(fn(string $ladder) => "('$ladder')", Elo::LADDERS));
		$query .= " ON CONFLICT DO NOTHING;";
		$stmt = GenericStatementImpl::forDialect(SqlDialect::SQLITE, "dynamic-insert-ladders", [$query], "", $vars, __FILE__, __LINE__);

		$rawArgs = [];
		$rawQuery = $stmt->format($args, null, $rawArgs);

		self::$db->executeImplRaw($rawQuery, $rawArgs, [SqlThread::MODE_GENERIC], fn() => null, null);
		self::$db->executeGeneric('oqex-practice.ranks.init');
		/** @noinspection Annotator */
		$query = 'INSERT INTO ranks(rank, priority) VALUES';
		$vars = [];
		$args = [];
		$query .= implode(', ', array_map(fn(string $rank) => "('$rank', " . RankMap::permissionMap($rank) . ")", RankMap::RANKS));
		$query .= " ON CONFLICT DO NOTHING;";
		$stmt = GenericStatementImpl::forDialect(SqlDialect::SQLITE, "dynamic-insert-ranks", [$query], "", $vars, __FILE__, __LINE__);

		$rawArgs = [];
		$rawQuery = $stmt->format($args, null, $rawArgs);

		self::$db->executeImplRaw($rawQuery, $rawArgs, [SqlThread::MODE_GENERIC], fn() => null, null);
        self::$db->executeGeneric('oqex-practice.players.init');
		self::$db->executeGeneric('oqex-practice.banned.init');
		//TODO: Migrate to new split tables
		self::$db->executeGeneric('oqex-practice.players.migrate_data');
        self::$db->executeGeneric('oqex-practice.kills.init');
        self::$db->executeGeneric('oqex-practice.deaths.init');
        self::$db->executeGeneric('oqex-practice.kits.init');
        self::$db->executeGeneric('oqex-practice.elos.init');
        self::$db->executeGeneric('oqex-practice.games.init');
        self::$db->executeGeneric('oqex-practice.cosmetics.equipped.init');
        self::$db->executeGeneric('oqex-practice.cosmetics.owned.hats.init');
        self::$db->executeGeneric('oqex-practice.cosmetics.owned.backpacks.init');
        self::$db->executeGeneric('oqex-practice.cosmetics.owned.belts.init');
        self::$db->executeGeneric('oqex-practice.cosmetics.owned.capes.init');
        self::$db->executeGeneric('oqex-practice.cosmetics.owned.tags.init');
        self::$db->executeGeneric('oqex-practice.cosmetics.owned.trails.init');
        self::$db->executeGeneric('oqex-practice.cosmetics.owned.killPhrases.init');
        self::$db->executeGeneric('oqex-practice.cosmetics.owned.chatColors.init');
        self::$db->executeGeneric('oqex-practice.cosmetics.owned.potColors.init');
        self::$db->executeGeneric('oqex-practice.packs.init');
        self::$db->executeGeneric('oqex-practice.parkour.init');
        self::$db->executeGeneric('oqex-practice.settings.init');
        self::$db->executeGeneric('oqex-practice.resets.init');

        Leaderboard::init();
        self::$db->waitAll();

        TimeUtils::attemptReRoll();

        try {
            if (!PacketHooker::isRegistered()) PacketHooker::register($this);
        } catch (HookAlreadyRegistered $e) {

        }

        $this->registerCommands();

        $this->getServer()->getPluginManager()->registerEvents(new DamageListener($this), $this);
        $this->getServer()->getPluginManager()->registerEvents(new NetworkListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new PlayerListener($this), $this);
        $this->getServer()->getPluginManager()->registerEvents(new WorldProtectionListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new OverrideListener($this->getScheduler()), $this);

        EntityFactory::getInstance()->register(SplashPotionEntity::class, function (World $world, CompoundTag $nbt): SplashPotionEntity {
            $potionType = PotionTypeIdMap::getInstance()->fromId($nbt->getShort("PotionId", PotionTypeIds::WATER));
            if ($potionType === null) {
                throw new SavedDataLoadingException("No such potion type");
            }

            return new SplashPotionEntity(EntityDataHelper::parseLocation($nbt, $world), null, $potionType, null, $nbt);
        }, ['PracticeSplashPotion']);

        EntityFactory::getInstance()->register(FishingHookEntity::class, function (World $world, CompoundTag $nbt): FishingHookEntity {
            return new FishingHookEntity(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
        }, ['FishingHookEntity']);

        $e = new OqexPackEntity(new Location(13.5, 62.5, 10.5, Server::getInstance()->getWorldManager()->getDefaultWorld(), 0, 0), VanillaBlocks::BEACON());
        $e->spawnToAll();

        EntityFactory::getInstance()->register(EnderPearlEntity::class, function (World $world, CompoundTag $nbt): EnderPearlEntity {
            return new EnderPearlEntity(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
        }, ['EnderPearlEntity']);

        EntityFactory::getInstance()->register(OqexPackEntity::class, function (World $world, CompoundTag $nbt): OqexPackEntity {
            $e = new OqexPackEntity(new Location(13.5, 62.5, 10.5, $world, 0, 0), VanillaBlocks::BEACON(), $nbt);
            $e->flagForDespawn();

            return $e;
        }, ['OqexPackEntity']);

        EntityFactory::getInstance()->register(TopEloPlayerEntity::class, function (World $world, CompoundTag $nbt): TopEloPlayerEntity {
            $e = new TopEloPlayerEntity(new Location(13.5, 62.5, 10.5, $world, 0, 0), CustomEntity::createSkin(CustomEntity::getSkinDataFromPNG($this->getDataFolder() . 'cosmetic/default_skin.png')),$nbt);
            $e->flagForDespawn();

            return $e;
        }, ['OqexPackEntity']);

        EntityFactory::getInstance()->register(ArrowEntity::class, function(World $world, CompoundTag $nbt): ArrowEntity {
            $e = new ArrowEntity(EntityDataHelper::parseLocation($nbt, $world), null, $nbt->getByte(Arrow::TAG_CRIT, 0) === 1, $nbt);
            $e->flagForDespawn();
            return $e;
        }, ['ArrowEntity']);

        EntityFactory::getInstance()->register(PackItemEntity::class, function (World $world, CompoundTag $nbt): PackItemEntity {
            $et = new PackItemEntity(EntityDataHelper::parseLocation($nbt, $world), VanillaItems::PAPER(), $nbt);
            $et->flagForDespawn();

            return $et;
        }, ['PackItemEntity']);

        EntityFactory::getInstance()->register(ServerSelectorNPC::class, function (World $world, CompoundTag $nbt): ServerSelectorNPC {
            return new ServerSelectorNPC(new Location(-186.5, 57.5, 17.5, $world, 0, 0), $nbt);
        }, ['NPC']);

        EntityFactory::getInstance()->register(VotePartySheepEntity::class, function (World $world, CompoundTag $nbt): VotePartySheepEntity {
            return new VotePartySheepEntity(Helper::parseLocation($nbt, $world), $nbt);
        }, ['VotePartySheep']);

        EntityFactory::getInstance()->register(FireworkRocket::class, function (World $world, CompoundTag $nbt): FireworkRocket {
            return new FireworkRocket(Helper::parseLocation($nbt, $world), 1,  [], $nbt);
        }, ['FireworkRocket']);

        $this->eventManger = new EventManger();

        CustomItem::init();
        Type::init();
        FFA::init();
        Map::init();
        PackHelper::init();

        $uselessEnchant = new Enchantment(' ', 0, 0, 0, 1);
        EnchantmentIdMap::getInstance()->register(1000, $uselessEnchant);

        CosmeticManager::init($this);
        $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
            $p = Server::getInstance()->getPlayerExact('TonicDevel');
            if ($p instanceof PracticePlayer && $p->isLoaded()) {
                $p->sendSound('scrape', 0.1, 5);
            }
        }), 20 * rand(45, 200));
    }

    public function getEventManager(): EventManger
    {
        return $this->eventManger;
    }

    public function registerCommands(): void
    {
        $this->unregisterCommands(['msg', 'w', 'give', 'status', 'kick', 'teleport', 'list', 'particle', 'save-on', 'save-off', 'say', 'seed', 'spawnpoint', 'time', 'title', 'transferserver', 'ban', 'unban', 'unban-ip', 'pardon', 'pardon-ip', 'stop', 'pl', 'ver', 'me', 'op', 'deop', 'gamemode', 'effect', 'checkperm', 'ban-ip', 'banlist', 'defaultgamemode', 'difficulty', 'clear', 'dumpmemory', 'enchant', 'gc', 'kill']);

        $map = $this->getServer()->getCommandMap();

        $map->register('OqexPractice', new OpCommand($this, 'op', 'Give a player operator status'));
        $map->register('OqexPractice', new DeopCommand($this, 'deop', 'Take operator status from a player'));
        $map->register('OqexPractice', new GamemodeCommand($this, 'gamemode', 'Change the gamemode of another player', ['gm']));
        $map->register('OqexPractice', new EffectCommand($this, 'effect', 'Modify the effects of another player'));
        $map->register('OqexPractice', new ClearCommand($this, 'clear', 'Clear all items from another players inventory'));
        $map->register('OqexPractice', new EnchantCommand($this, 'enchant', 'Enchant the held item of another player'));
        $map->register('OqexPractice', new GiveCommand($this, 'give', 'Give an item to another player'));
        $map->register('OqexPractice', new SpawnCommand($this, 'spawn', 'Teleport to the server spawn', ['hub']));
        $map->register('OqexPractice', new DuelCommand($this, 'duel', 'Send a duel request to another player'));
        $map->register('OqexPractice', new ExtraRankedGamesCommand($this, 'egames', 'Add extra ranked games to a player'));
        $map->register('OqexPractice', new StaffCommand($this, 'staff', 'Staff Mode for easy moderation'));
        $map->register('OqexPractice', new SpectateCommand($this, 'spectate', 'Spectate the duel of another player'));
        $map->register('OqexPractice', new PartyCommand($this, 'party'));
        $map->register('OqexPractice', new KnockbackCommand($this, 'knockback'));
        $map->register('OqexPractice', new RestartCommand($this, 'restart', 'Restart the server', ['reboot']));
        $map->register('OqexPractice', new KickCommand($this, 'kick', 'Kick a player from the server'));
        $map->register('OqexPractice', new TeleportCommand($this, 'teleport', 'Teleport to another player', ['tp']));
        $map->register('OqexPractice', new StatusCommand($this, 'status', 'Check the server status', ['tps']));
        $map->register('OqexPractice', new EventCommand($this, 'event'));
        $map->register('OqexPractice', new BotCommand($this, 'bot'));
        $map->register('OqexPractice', new SettingsCommand($this, 'settings', '', ['toggle']));
        $map->register('OqexPractice', new PingCommand($this, 'ping'));
        $map->register('OqexPractice', new RulesCommand($this, 'rules'));
        $map->register('OqexPractice', new KitCommand($this, 'kit'));
        $map->register('OqexPractice', new MessageCommand($this, 'message', 'Message an online player', ['w', 'msg', 'whisper']));
        $map->register('OqexPractice', new FreezeCommand($this, 'freeze'));
        $map->register('OqexPractice', new StaffChatCommand($this, 'staffchat', 'enables / disables staff chat', ['sc']));

        PlayerSqlHelper::getLowercaseUsernames(function (array $usernames) use ($map): void {
            $v = [];

            foreach ($usernames as $u) {
                $v[$u] = $u;
            }

            $map->register('OqexPractice', new BanCommand($v, $this, 'ban', 'Ban a player'));
            $map->register('OqexPractice', new UnbanCommand($v, $this, 'unban', 'Unban a player'));
            $map->register('OqexPractice', new SetRankCommand($v, $this, 'setrank', 'Set a players rank'));
            $map->register('OqexPractice', new ResetStatsCommand($v, $this, 'resetstats', 'Reset a players stats'));
            $map->register('OqexPractice', new AddCoinsCommand($v, $this, 'addcoins', 'Add coins to a player'));
            $map->register('OqexPractice', new CosmeticsCommand($v, $this, 'cosmetics', 'Manage your cosmetics'));
            $map->register('OqexPractice', new StatsCommand($v, $this, 'stats', 'View a players statistics'));
        });
    }

    /** @return PracticePlayer[] */
    public static function getOnlineStaff(): array
    {
        $players = [];
        /** @var PracticePlayer $player */
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            if ($player->isLoaded() && $player->getData()->getHighestRank() >= RankMap::permissionMap('helper')) {
                $players[] = $player;
            }
        }

        return $players;
    }

	/** @param list<string> $commands */
    public function unregisterCommands(array $commands): void
    {
        $map = $this->getServer()->getCommandMap();
        foreach ($commands as $cmd) {
            $command = $map->getCommand($cmd);
            if ($command !== null) {
                $map->unregister($command);
            }
        }
    }

    public static function getDatabase(): DataConnector
    {
        return self::$db;
    }

    public static function getInstance(): self
    {
        return self::$instance;
    }

    public function getParentFile() {
        return parent::getFile();
    }
}