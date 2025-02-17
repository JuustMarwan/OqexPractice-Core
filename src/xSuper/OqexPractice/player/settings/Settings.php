<?php

namespace xSuper\OqexPractice\player\settings;

use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\jackmd\scorefactory\ScoreFactory;
use pocketmine\Server;
use pocketmine\utils\AssumptionFailedError;
use Ramsey\Uuid\UuidInterface;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\utils\scoreboard\Scoreboards;

class Settings implements SettingIDS
{
    public const DEFAULTS = [
        self::CHAT_MESSAGE => 1,
        self::KILL_MESSAGE => 1,
        self::ANNOUCEMENTS => 1,
        self::PRIVATE_MESSAGE => 1,
        self::PROFANITY => 0,
        self::HIDE_PLAYERS_AT_SPAWN => 0,
        self::HIDE_PLAYERS_AT_EVENT => 0,
        self::HIDE_PLAYERS_AT_FFA => 0,
        self::INTERRUPTING => 0,
        self::UI_TYPE => self::UI_TYPE_RECOMMENDED,
        self::SCOREBOARD => 1,
        self::DUEL_REQUESTS => 1,
        self::PARTY_INVITES => 1,
        self::ANIMATE_PACKS => 1,
        self::SHOP_ALERT => 1,
        self::STAT_RESET_ALERT => 1,
        self::FFA_RESPAWN => 1
    ];

	/** @var array{0?: int<0, 1>, 1?: int<0, 1>, 2?: int<0, 1>, 3?: int<0, 1>, 4?: int<0, 1>, 5?: int<0, 1>, 6?: int<0, 1>, 7?: int<0, 1>, 8?: int<0, 1>, 9?: int<0, 1>, 10?: SettingIDS::UI_TYPE_*, 11?: int<0, 1>, 12?: int<0, 1>, 13?: int<0, 1>, 14?: int<0, 1>} */
    private array $settings = [];

    private ?PracticePlayer $p = null;

    public function __construct(private UuidInterface $uuid)
    {

    }

	/**
	 * @phpstan-param key-of<self::DEFAULTS> $id
	 * @phpstan-return ($id is SettingIDS::UI_TYPE ? SettingIDS::UI_TYPE_* : int<0, 1>)
	 */
    public function getSetting(int $id): int
    {
        if ($id === self::UI_TYPE) return $this->getUIType();

        return $this->settings[$id] ?? self::DEFAULTS[$id] ?? 0;
    }

	/**
	 * @phpstan-param key-of<self::DEFAULTS> $id
	 * @phpstan-return ($id is SettingIDS::UI_TYPE ? SettingIDS::UI_TYPE_* : int<0, 1>)
	 */
    public function getRawSetting(int $id): int
    {
        return $this->settings[$id] ?? self::DEFAULTS[$id] ?? 0;
    }

	/** @return SettingIDS::UI_TYPE_* */
    private function getUIType(): int
    {
        $current = $this->settings[self::UI_TYPE] ?? self::UI_TYPE_RECOMMENDED;

        if ($current === self::UI_TYPE_RECOMMENDED) {
            if ($this->p === null){
				$player = Server::getInstance()->getPlayerByUUID($this->uuid);
				if($player !== null && !$player instanceof PracticePlayer){
					throw new AssumptionFailedError('Player should be an instance of PracticePlayer');
				}
				$this->p = $player;
			}
            if ($this->p !== null) {
                if ($this->p->getData()->getInfo()->isPE()) return self::UI_TYPE_FORM;
                else return self::UI_TYPE_CHEST;
            }
        }

        return $current;
    }

	/** @phpstan-param key-of<self::DEFAULTS> $id */
    public function asBool(int $id): bool
    {
        $s = $this->settings[$id] ?? null;
        if ($s === null) $s = self::DEFAULTS[$id] ?? false;
        return (bool) $s;
    }

	/**
	 * @phpstan-param key-of<self::DEFAULTS> $id
	 * @phpstan-param int<0, 1>|SettingIDS::UI_TYPE_* $value
	 */
    public function setSetting(int $id, int $value, bool $save = true): void
    {
		if($id === SettingIDS::UI_TYPE) {
            if (!in_array($value, [SettingIDS::UI_TYPE_CHEST, SettingIDS::UI_TYPE_FORM, SettingIDS::UI_TYPE_RECOMMENDED], true)) {
                throw new \InvalidArgumentException("Unexpected value $value for setting $id");
            }

            $settings = $this->settings;
            $settings[$id] = $value;
            /** @var array{0?: int<0, 1>, 1?: int<0, 1>, 2?: int<0, 1>, 3?: int<0, 1>, 4?: int<0, 1>, 5?: int<0, 1>, 6?: int<0, 1>, 7?: int<0, 1>, 8?: int<0, 1>, 9?: int<0, 1>, 10?: SettingIDS::UI_TYPE_*, 11?: int<0, 1>, 12?: int<0, 1>, 13?: int<0, 1>, 14?: int<0, 1>} $settings */
            $this->settings = $settings;
        } else if ($id === SettingIDS::SCOREBOARD) {
            $player = Server::getInstance()->getPlayerByUUID($this->uuid);
            if(!$player instanceof PracticePlayer){
                throw new AssumptionFailedError('Player should be an instance of PracticePlayer');
            }

            if($value !== 0 && $value !== 1){
                throw new \InvalidArgumentException("Unexpected value $value for setting $id");
            }

            $settings = $this->settings;
            $settings[$id] = $value;
            /** @var array{0?: int<0, 1>, 1?: int<0, 1>, 2?: int<0, 1>, 3?: int<0, 1>, 4?: int<0, 1>, 5?: int<0, 1>, 6?: int<0, 1>, 7?: int<0, 1>, 8?: int<0, 1>, 9?: int<0, 1>, 10?: SettingIDS::UI_TYPE_*, 11?: int<0, 1>, 12?: int<0, 1>, 13?: int<0, 1>, 14?: int<0, 1>} $settings */
            $this->settings = $settings;

            if ($value === 0) {
                $player->setScoreboard('');
                ScoreFactory::removeObjective($player);
            } else Scoreboards::LOBBY()->send($player);
		}else{
			if($value !== 0 && $value !== 1){
				throw new \InvalidArgumentException("Unexpected value $value for setting $id");
			}
			$settings = $this->settings;
			$settings[$id] = $value;
			/** @var array{0?: int<0, 1>, 1?: int<0, 1>, 2?: int<0, 1>, 3?: int<0, 1>, 4?: int<0, 1>, 5?: int<0, 1>, 6?: int<0, 1>, 7?: int<0, 1>, 8?: int<0, 1>, 9?: int<0, 1>, 10?: SettingIDS::UI_TYPE_*, 11?: int<0, 1>, 12?: int<0, 1>, 13?: int<0, 1>, 14?: int<0, 1>} $settings */
			$this->settings = $settings;
		}

        if ($save) $this->save();
    }

    public function save(): void
    {

        foreach ($this->settings as $id => $value) {
            OqexPractice::getDatabase()->executeInsert('oqex-practice.settings.save', [
                'uuid' => $this->uuid->toString(),
                'setting' => $id,
                'value' => $value
            ]);
        }
    }
}