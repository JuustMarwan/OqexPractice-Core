<?php

namespace xSuper\OqexPractice\items\custom;

use pocketmine\utils\CloningRegistryTrait;
use xSuper\OqexPractice\items\custom\event\EventLeaveItem;
use xSuper\OqexPractice\items\custom\lobby\DuelSwordItem;
use xSuper\OqexPractice\items\custom\lobby\EventItem;
use xSuper\OqexPractice\items\custom\lobby\FFASelectionItem;
use xSuper\OqexPractice\items\custom\lobby\LeaveQueueItem;
use xSuper\OqexPractice\items\custom\lobby\PartyItem;
use xSuper\OqexPractice\items\custom\lobby\ProfileItem;
use xSuper\OqexPractice\items\custom\lobby\RankedDuelSwordItem;
use xSuper\OqexPractice\items\custom\parkour\CheckpointItem;
use xSuper\OqexPractice\items\custom\parkour\LeaveParkourItem;
use xSuper\OqexPractice\items\custom\staff\FreezeItem;
use xSuper\OqexPractice\items\custom\staff\VanishItem;

/**
 * @method static DuelSwordItem DUEL_SWORD()
 * @method static RankedDuelSwordItem RANKED_DUEL_SWORD()
 * @method static LeaveQueueItem LEAVE_QUEUE()
 * @method static VanishItem VANISH()
 * @method static FreezeItem FREEZE()
 * @method static FFASelectionItem FFA()
 * @method static EventItem EVENT()
 * @method static PartyItem PARTY()
 * @method static ProfileItem PROFILE()
 * @method static CheckpointItem CHECKPOINT()
 * @method static LeaveParkourItem LEAVE_PARKOUR()
 * @method static EventLeaveItem LEAVE_EVENT()
 */
final class InteractiveItems{

	use CloningRegistryTrait;

	private function __construct(){
		//NOOP
	}

	protected static function register(string $name, CustomItem $item) : void{
		self::_registryRegister($name, $item);
	}

	/**
	 * @return CustomItem[]
	 * @phpstan-return array<string, CustomItem>
	 */
	public static function getAll() : array{
		//phpstan doesn't support generic traits yet :(
		/** @var CustomItem[] $result */
		$result = self::_registryGetAll();
		return $result;
	}

	protected static function setup(): void{
		self::register('duel_sword', new DuelSwordItem('duel_sword'));
		self::register('ranked_duel_sword', new RankedDuelSwordItem('ranked_duel_sword'));
		self::register('leave_queue', new LeaveQueueItem('leave_queue'));
		self::register('vanish', new VanishItem('vanish'));
		self::register('freeze', new FreezeItem('freeze'));
		self::register('ffa', new FFASelectionItem('ffa'));
		self::register('event', new EventItem('event'));
		self::register('party', new PartyItem('party'));
		self::register('profile', new ProfileItem('profile'));
		self::register('checkpoint', new CheckpointItem('checkpoint'));
		self::register('leave_parkour', new LeaveParkourItem('leave_parkour'));
		self::register('leave_event', new EventLeaveItem('leave_event'));
	}
}