<?php

namespace xSuper\OqexPractice\entities\custom;

use ErrorException;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Human;
use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\errorhandler\ErrorToExceptionHandler;
use pocketmine\nbt\tag\CompoundTag;

class CustomEntity extends Human
{
    public bool $gravityEnabled = false;

    public function __construct(Location $location, Skin $skin, ?CompoundTag $nbt = null)
    {
        parent::__construct($location, $skin, $nbt);
        $this->setNoClientPredictions();
    }

    public function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(6, 3);
    }

	/**
	 * @throws ErrorException
	 */
	public static function getSkinDataFromPNG(string $path): string {
        $image = ErrorToExceptionHandler::trapAndRemoveFalse(static fn() => imagecreatefrompng($path));
        $data = "";
        for($y = 0, $height = imagesy($image); $y < $height; $y++) {
            for($x = 0, $width = imagesx($image); $x < $width; $x++) {
                $color = imagecolorat($image, $x, $y);
                $data .= pack("c", ($color >> 16) & 0xFF)
                    . pack("c", ($color >> 8) & 0xFF)
                    . pack("c", $color & 0xFF)
                    . pack("c", 255 - (($color & 0x7F000000) >> 23));
            }
        }
        return $data;
    }

    public static function createSkin(string $skinData): Skin
    {
        return new Skin('Standard_Custom', $skinData, '', 'geometry.humanoid.custom');
    }
}