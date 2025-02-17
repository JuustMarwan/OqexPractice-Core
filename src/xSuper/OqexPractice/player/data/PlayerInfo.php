<?php

namespace xSuper\OqexPractice\player\data;

use pocketmine\Server;
use pocketmine\utils\Filesystem;
use xSuper\OqexPractice\OqexPractice;

class PlayerInfo
{
    public const UNKNOWN = -1;
    public const ANDROID = 1;
    public const IOS = 2;
    public const OSX = 3;
    public const FIREOS = 4;
    public const VRGEAR = 5;
    public const VRHOLOLENS = 6;
    public const WINDOWS_10 = 7;
    public const WINDOWS_32 = 8;
    public const DEDICATED = 9;
    public const TVOS = 10;
    public const PS4 = 11;
    public const SWITCH = 12;
    public const XBOX = 13;
    public const LINUX = 14;

    public const KEYBOARD = 1;
    public const TOUCH = 2;
    public const CONTROLLER = 3;
    public const MOTION_CONTROLLER = 4;

    public const DEVICE_OS_VALUES = [
        self::UNKNOWN => 'Unknown',
        self::ANDROID => 'Android',
        self::IOS => 'iOS',
        self::OSX => 'OSX',
        self::FIREOS => 'FireOS',
        self::VRGEAR => 'VRGear',
        self::VRHOLOLENS => 'VRHololens',
        self::WINDOWS_10 => 'Windows',
        self::WINDOWS_32 => 'Windows 32',
        self::DEDICATED => 'Dedicated',
        self::TVOS => 'TVOS',
        self::PS4 => 'PS4',
        self::SWITCH => 'Switch',
        self::XBOX => 'Xbox',
        self::LINUX => 'Linux'
    ];

    public const NON_PE_DEVICES = [
        self::PS4 => true,
        self::WINDOWS_10 => true,
        self::XBOX => true,
        self::LINUX => true
    ];

    public const INPUT_VALUES = [
        self::UNKNOWN => 'Unknown',
        self::KEYBOARD => 'Keyboard',
        self::TOUCH => 'Touch',
        self::CONTROLLER => 'Controller',
        self::MOTION_CONTROLLER => 'Motion-Controller'
    ];

    const DEVICE_OS_UNICODES = [self::UNKNOWN => "", self::ANDROID => "", self::IOS => "", self::FIREOS => "", self::WINDOWS_10 => "", self::PS4 => "", self::SWITCH => "", self::XBOX => "", self::LINUX => ""];

	/** @var array<string, self> */
    private static array $datas = [];
	/** @var array<string, string>  */
    private static array $deviceModels = [];

    public static function init(): void
    {
        $file = OqexPractice::getInstance()->getDataFolder() . "device/device_models.json";
        if (file_exists($file)) {
            $contents = Filesystem::fileGetContents($file);
			$rawDeviceModels = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);
			if(!is_array($rawDeviceModels)){
				throw new \TypeError('Device models should be array, got ' . gettype($rawDeviceModels));
			}
			self::$deviceModels = $rawDeviceModels;
        }
    }

	/** @param array{'ClientRandomId'?: int, 'CurrentInputMode'?: int, 'DefaultInputMode'?: int, 'DeviceId'?: string, 'DeviceModel'?: string, 'DeviceOS'?: int, 'GameVersion'?: string, 'GuiScale'?: int, 'SelfSignedId'?: string, 'UIProfile'?: int} $data */
    public static function create(string $uuid, array $data): void
    {
        $data = self::from($data);
        self::$datas[$uuid] = $data;
    }

    public static function getData(string $uuid): ?self
    {
        return self::$datas[$uuid] ?? null;
    }

	/** @param array{'ClientRandomId'?: int, 'CurrentInputMode'?: int, 'DefaultInputMode'?: int, 'DeviceId'?: string, 'DeviceModel'?: string, 'DeviceOS'?: int, 'GameVersion'?: string, 'GuiScale'?: int, 'SelfSignedId'?: string, 'UIProfile'?: int} $data */
    public static function from(array $data): self
    {
        return new self(
            $data['ClientRandomId'] ?? null,
            $data['CurrentInputMode'] ?? self::UNKNOWN,
            $data['DefaultInputMode'] ?? self::UNKNOWN,
            $data['DeviceId'] ?? null,
            self::getDeviceFromModel($data['DeviceModel'] ?? 'Unknown'),
            $data['DeviceOS'] ?? self::UNKNOWN,
            $data['GameVersion'] ?? 'Unknown',
            $data['GuiScale'] ?? self::UNKNOWN,
            $data['SelfSignedId'] ?? null,
            $data['UIProfile'] ?? self::UNKNOWN
        );
    }

    public static function getDeviceFromModel(string $model): ?string
    {

        if (isset(self::$deviceModels[$model])) return self::$deviceModels[$model];
        Server::getInstance()->getLogger()->warning('Device Name for: ' . $model . ' not found!');
        return null;
    }

    private string $deviceModel;
    private int $deviceOS;
    public function __construct(private ?int $clientRandomId, private int $currentInput, private int $defaultInput, private ?string $deviceId, ?string $deviceModel, int $deviceOS, private string $version, private int $guiScale, private ?string $selfSignedId, private int $ui)
    {
        if ($deviceModel === null) $deviceModel = 'Unknown';
        if (trim($deviceModel) === "") {
            switch ($deviceOS) {
                case self::ANDROID:
                    $deviceOS = self::LINUX;
                    $deviceModel = "Linux";
                    break;
                case self::XBOX:
                    $deviceModel = "Xbox One";
                    break;
            }
        }

        $this->deviceOS = $deviceOS;
        $this->deviceModel = $deviceModel;
    }

    public function getUnicode(): string
    {
        return self::DEVICE_OS_UNICODES[$this->deviceOS] ?? '';
    }

    public function getClientRandomId(): ?int
    {
        return $this->clientRandomId;
    }

    public function getInput(): string
    {
        return self::INPUT_VALUES[$this->currentInput] ?? 'Unknown';
    }

    public function getDefaultInput(): int
    {
        return $this->defaultInput;
    }

    public function getDeviceId(): ?string
    {
        return $this->deviceId;
    }

    public function getDeviceModel(): string
    {
        return $this->deviceModel;
    }

    public function getDeviceOS(): ?string
    {
        return self::DEVICE_OS_VALUES[$this->deviceOS] ?? 'Unknown';
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getGuiScale(): int
    {
        return $this->guiScale;
    }

    public function getSelfSigned(): ?string
    {
        return $this->selfSignedId;
    }

    public function getUI(): int
    {
        return $this->ui;
    }

    public function isPE(): bool
    {
        return !isset(self::NON_PE_DEVICES[$this->deviceOS]) && $this->currentInput === self::TOUCH;
    }
}