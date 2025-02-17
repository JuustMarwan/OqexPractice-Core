<?php

declare(strict_types=1);

namespace xSuper\OqexPractice\tasks;

use pocketmine\promise\PromiseResolver;
use pocketmine\scheduler\AsyncTask;
use Symfony\Component\Filesystem\Path;
use xSuper\OqexPractice\player\cosmetics\CosmeticManager;

final class SkinSaveTask extends AsyncTask{
    public function __construct(private readonly string $skinData, private readonly string $filePath, private bool $default, private readonly string $resourceFolder, PromiseResolver $resolver)
    {
        $this->storeLocal('resolver', $resolver);
    }

    public function onRun(): void
    {
        $skinData = $this->skinData;

        $height = 64;
        $width = 64;
        switch (strlen($skinData)) {
            case 64 * 32 * 4:
                $height = 32;
                break;
            case 128 * 128 * 4:
                $height = 128;
                $width = 128;
                break;
            case 128 * 64 * 4:
                $width = 128;
                break;
        }

        if ($this->default) {
            copy(Path::join($this->resourceFolder, "cosmetic", "default_skin.png"), $this->filePath);
            return;
        }

        $img = imagecreatetruecolor($width, $height);
        imagealphablending($img, false);
        imagesavealpha($img, true);

        $index = 0;
        for ($y = 0; $y < $height; ++$y) {
            for ($x = 0; $x < $width; ++$x) {
                $list = substr($skinData, $index, 4);
                $r = ord($list[0]);
                $g = ord($list[1]);
                $b = ord($list[2]);
                $a = 127 - (ord($list[3]) >> 1);
                $index += 4;
                $color = imagecolorallocatealpha($img, $r, $g, $b, $a);
                imagesetpixel($img, $x, $y, $color);
            }
        }

        if ($height !== CosmeticManager::SECTION_SIZE || $width !== CosmeticManager::SECTION_SIZE) {

            $resizedSkin = imagecreatetruecolor(CosmeticManager::SECTION_SIZE, CosmeticManager::SECTION_SIZE);

            imagesavealpha($resizedSkin, true);
            $transparentColor = imagecolorallocatealpha($resizedSkin, 0, 0, 0, 127);
            imagefill($resizedSkin, 0, 0, $transparentColor);

            imagecopyresampled($resizedSkin, $img, 0, 0, 0, 0, CosmeticManager::SECTION_SIZE, CosmeticManager::SECTION_SIZE, $width, $height);

            imagepng($resizedSkin, $this->filePath);

            imagedestroy($img);
            imagedestroy($resizedSkin);

            return;
        }

        imagepng($img, $this->filePath);
        imagedestroy($img);
    }

    public function onCompletion(): void
    {
        /** @var PromiseResolver<true> $resolver */
        $resolver = $this->fetchLocal('resolver');
        $resolver->resolve(true);
    }

    private const MIN_TRANSPARENCY = 75;

    private function checkSkin(int $width, int $height) : bool {
        $pos = -1;
        $pixelsNeeded = (int)((100 - self::MIN_TRANSPARENCY) / 100 * ($width * $height)); // visible pixels needed
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                if (ord($this->skinData[$pos += 4]) === 255) {
                    if (--$pixelsNeeded === 0) {
                        return false;
                    }
                }
            }
        }
        return true;
    }
}