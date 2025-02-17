<?php

namespace xSuper\OqexPractice\player\cosmetics;

use InvalidArgumentException;
use pocketmine\entity\Skin;
use pocketmine\errorhandler\ErrorToExceptionHandler;
use pocketmine\promise\PromiseResolver;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\Utils;
use Symfony\Component\Filesystem\Path;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\tasks\SkinSaveTask;

class CosmeticManager
{
    public const TYPE_HAT = 'hat';
    public const TYPE_BACKPACK = 'backpack';
    public const TYPE_BELT = 'belt';
    public const TYPE_CAPE = 'cape';

    public const TYPE_TAG = 'tag';
    public const TYPE_PROJECTILE_TRAIL = 'projectile_trail';
    public const TYPE_KILL_PHRASE = 'kill_phrase';
    public const TYPE_CHAT_COLOR = 'chat_color';
    public const TYPE_POT_COLOR = 'pot_color';

    /** @var Cosmetic[] */
    private static array $cosmetics = [];

    private static string $dataFolder;

    const BOUNDS_64_64 = 0;
    const BOUNDS_64_32 = self::BOUNDS_64_64;
    const BOUNDS_128_128 = 1;

    private static array $skinBounds = [];

    private static array $killPhrases = [];

    public static function init(OqexPractice $plugin): void
    {
        self::$dataFolder = $plugin->getDataFolder();
        self::$killPhrases = json_decode(file_get_contents(Path::join(self::$dataFolder, 'cosmetic', 'killPhrases.json')), true);

        $cosmetics = new Config(self::$dataFolder . '/cosmetic/cosmetics.yml');

        $cubes = self::getCubes(json_decode('		{
			"description": {
				"identifier": "geometry.humanoid.custom",
				"texture_width": 128,
				"texture_height": 128,
				"visible_bounds_width": 4,
				"visible_bounds_height": 4,
				"visible_bounds_offset": [0, 2, 0]
			},
			"bones": [
				{
					"name": "root",
					"pivot": [0, 0, 0]
				},
				{
					"name": "waist",
					"parent": "root",
					"pivot": [0, 12, 0]
				},
				{
					"name": "body",
					"parent": "waist",
					"pivot": [0, 24, 0],
					"cubes": [
						{"origin": [-4, 12, -2], "size": [8, 12, 4], "uv": [16, 16]}
					]
				},
				{
					"name": "head",
					"parent": "body",
					"pivot": [0, 24, 0],
					"cubes": [
						{"origin": [-4, 24, -4], "size": [8, 8, 8], "uv": [0, 0]}
					]
				},
				{
					"name": "hat",
					"parent": "head",
					"pivot": [0, 24, 0],
					"cubes": [
						{"origin": [-4, 24, -4], "size": [8, 8, 8], "inflate": 0.5, "uv": [32, 0]}
					]
				},
				{
					"name": "cape",
					"parent": "body",
					"pivot": [0, 24, 3]
				},
				{
					"name": "leftArm",
					"parent": "body",
					"pivot": [5, 22, 0],
					"cubes": [
						{"origin": [4, 12, -2], "size": [4, 12, 4], "uv": [32, 48]}
					]
				},
				{
					"name": "leftSleeve",
					"parent": "leftArm",
					"pivot": [5, 22, 0],
					"cubes": [
						{"origin": [4, 12, -2], "size": [4, 12, 4], "inflate": 0.25, "uv": [48, 48]}
					]
				},
				{
					"name": "leftItem",
					"parent": "leftArm",
					"pivot": [6, 15, 1]
				},
				{
					"name": "rightArm",
					"parent": "body",
					"pivot": [-5, 22, 0],
					"cubes": [
						{"origin": [-8, 12, -2], "size": [4, 12, 4], "uv": [40, 16]}
					]
				},
				{
					"name": "rightSleeve",
					"parent": "rightArm",
					"pivot": [-5, 22, 0],
					"cubes": [
						{"origin": [-8, 12, -2], "size": [4, 12, 4], "inflate": 0.25, "uv": [40, 32]}
					]
				},
				{
					"name": "rightItem",
					"parent": "rightArm",
					"pivot": [-6, 15, 1],
					"locators": {
						"lead_hold": [-6, 15, 1]
					}
				},
				{
					"name": "jacket",
					"parent": "body",
					"pivot": [0, 24, 0],
					"cubes": [
						{"origin": [-4, 12, -2], "size": [8, 12, 4], "inflate": 0.25, "uv": [16, 32]}
					]
				},
				{
					"name": "leftLeg",
					"parent": "root",
					"pivot": [1.9, 12, 0],
					"cubes": [
						{"origin": [-0.1, 0, -2], "size": [4, 12, 4], "uv": [16, 48]}
					]
				},
				{
					"name": "leftPants",
					"parent": "leftLeg",
					"pivot": [1.9, 12, 0],
					"cubes": [
						{"origin": [-0.1, 0, -2], "size": [4, 12, 4], "inflate": 0.25, "uv": [0, 48]}
					]
				},
				{
					"name": "rightLeg",
					"parent": "root",
					"pivot": [-1.9, 12, 0],
					"cubes": [
						{"origin": [-3.9, 0, -2], "size": [4, 12, 4], "uv": [0, 16]}
					]
				},
				{
					"name": "rightPants",
					"parent": "rightLeg",
					"pivot": [-1.9, 12, 0],
					"cubes": [
						{"origin": [-3.9, 0, -2], "size": [4, 12, 4], "inflate": 0.25, "uv": [0, 32]}
					]
				}
			],
			"item_display_transforms": {
				"thirdperson_righthand": {
					"rotation": [67.5, 180, 0],
					"translation": [0, -0.25, 0.5],
					"scale": [0.18, 0.18, 0.18]
				},
				"thirdperson_lefthand": {
					"rotation": [67.5, 180, 0],
					"translation": [0, -0.25, 0.5],
					"scale": [0.18, 0.18, 0.18]
				},
				"firstperson_righthand": {
					"translation": [0.5, 4.25, 0],
					"scale": [0.25781, 0.25781, 0.25781]
				},
				"firstperson_lefthand": {
					"translation": [0.5, 4.25, 0],
					"scale": [0.25781, 0.25781, 0.25781]
				},
				"ground": {
					"translation": [0, 6, -1.75],
					"scale": [0.25, 0.25, 0.25]
				},
				"gui": {
					"scale": [0.32227, 0.32227, 0.32227]
				},
				"head": {
					"translation": [0, -53, -0.25],
					"scale": [1.38, 1.38, 1.38]
				},
				"fixed": {
					"rotation": [0, 180, 0],
					"translation": [0, 4, 3.55],
					"scale": [1.5, 1.5, 1.5]
				}
			}
		}', true));
        self::$skinBounds[self::BOUNDS_64_64] = self::getSkinBounds($cubes);
        self::$skinBounds[self::BOUNDS_128_128] = self::getSkinBounds($cubes, 2.0);

        foreach ($cosmetics->get('cosmetics') as $id => $data) {
            self::$cosmetics[$id] = new Cosmetic($id, $data['name'], $data['rarity'], $data['season'], $data['type'], $data['data']);
        }

        @mkdir(Path::join(OqexPractice::getInstance()->getDataFolder(), "players", "skin"));
    }

    public static function getKillPhrase(PracticePlayer $killer, string $victim): string
    {
        $phrase = $killer->getData()->getCosmetics()->getEquippedKillPhrase();
        return str_replace(['{killer}', '{victim}'], [$killer->getName(), $victim], $phrase);
    }

    public static function load(PracticePlayer $player): void
    {

    }


    public static function changeCosmetic(Cosmetic $cosmetic, PracticePlayer $player): void
    {
        switch ($cosmetic->getType()) {
            case self::TYPE_HAT:
            case self::TYPE_BACKPACK:
            case self::TYPE_BELT:
            case self::TYPE_CAPE:
                $func = function() use ($cosmetic, $player): void {
                    match ($cosmetic->getType()) {
                        self::TYPE_HAT => $player->getData()->getCosmetics()->setEquippedHat($cosmetic->getId()),
                        self::TYPE_BACKPACK => $player->getData()->getCosmetics()->setEquippedBackpack($cosmetic->getId()),
                        self::TYPE_BELT => $player->getData()->getCosmetics()->setEquippedBelt($cosmetic->getId()),
                        self::TYPE_CAPE => $player->getData()->getCosmetics()->setEquippedCape($cosmetic->getId())
                    };
                };

                $func();
                self::applyCosmetics($player, $player->getSkin());
        }
    }

    public const TEMPLATE_SIZE = 128;
    public const SECTION_SIZE = 64;

    public static function test(PracticePlayer $player, Skin $skin, bool $save = false): void
    {
        $closure = function() use ($player): void {
            $name = $player->getName();

            $canvas = imagecreatetruecolor(self::TEMPLATE_SIZE, self::TEMPLATE_SIZE);

            $transparentColor = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
            imagefill($canvas, 0, 0, $transparentColor);
            imagesavealpha($canvas, true);

            $skin = Path::join(self::$dataFolder, "players", "skin", "$name.png");

            $skin = ErrorToExceptionHandler::trapAndRemoveFalse(fn() => imagecreatefrompng($skin));
            imagecopy($canvas, $skin, 0, 0, 0, 0, self::SECTION_SIZE, self::SECTION_SIZE);
            imagedestroy($skin);

            $defaultPath = Path::join(self::$dataFolder, "cosmetic");

            $path = Path::join($defaultPath, "default_geometry.json");

            $p = json_decode(file_get_contents($path), true);

            $cosmetics = $player->getData()->getCosmetics();
            foreach (['straw_hat', 'fox_backpack', 'fox_tail'] as $type) {
                if ($type !== null) {
                    $cosmetic = self::getCosmetic($type);
                   // if ($cosmetic !== null) {
                        $c = json_decode(file_get_contents(Path::join($defaultPath, 'geo', $type . '.json')), true);

                        [$x, $y, $sX, $sY] = match ($type) {
                            'straw_hat' => [self::SECTION_SIZE, 0, 0, 0],
                            'fox_backpack' => [0, self::SECTION_SIZE, 0, 0],
                            'fox_tail' => [self::SECTION_SIZE, self::SECTION_SIZE, 0, 0]
                        };

                        $co = ErrorToExceptionHandler::trapAndRemoveFalse(fn() => imagecreatefrompng(Path::join($defaultPath, 'img', $type . '.png')));
                        imagecopy($canvas, $co, $x, $y, $sX, $sY, self::SECTION_SIZE, self::SECTION_SIZE);
                        imagedestroy($co);

                        $p['minecraft:geometry'][1]['bones'][] = $c; // classic
                        $p['minecraft:geometry'][2]['bones'][] = $c; // slim
                  //  }
                }
            }

            imagepalettetotruecolor($canvas);

            $skinData = "";
            for ($y = 0; $y < self::TEMPLATE_SIZE; $y++) {
                for ($x = 0; $x < self::TEMPLATE_SIZE; $x++) {
                    // https://www.php.net/manual/en/function.imagecolorat.php
                    $rgba = imagecolorat($canvas, $x, $y);
                    $a = ($rgba >> 24) & 0xff;
                    $r = ($rgba >> 16) & 0xff;
                    $g = ($rgba >> 8) & 0xff;
                    $b = $rgba & 0xff;
                    $skinData .= chr($r) . chr($g) . chr($b) . chr(~(($a << 1) | ($a >> 6)) & 0xff);
                }
            }

            imagedestroy($canvas);

            $oldSkin = $player->getSkin();
            $cape = '';

            if (($c = $cosmetics->getCape()) !== null) {
                $c = self::getCosmetic($c);
                if ($c !== null) $cape = $c->getData()['img'];
            }

            if (!in_array($oldSkin->getGeometryName(), ["geometry.humanoid.customSlim", "geometry.humanoid.custom"], true)) $name = 'geometry.humanoid.custom';
            else $name = $oldSkin->getGeometryName();

            $player->setSkin(new Skin($oldSkin->getSkinId(), $skinData, $cape, $name, json_encode($p)));
            $player->sendSkin();
        };

        if ($save) {
            $resolver = new PromiseResolver();
            $resolver->getPromise()->onCompletion($closure, function (): void {});

            $task = new SkinSaveTask($skin->getSkinData(), Path::join(self::$dataFolder, "players", "skin", $player->getName() . '.png'), CosmeticManager::getSkinTransparencyPercentage($skin->getSkinData()) > 75 || !in_array($skin->getGeometryName(), ["geometry.humanoid.customSlim", "geometry.humanoid.custom"], true), self::$dataFolder, $resolver);
            Server::getInstance()->getAsyncPool()->submitTask($task);
        } else $closure();
    }

    public static function applyCosmetics(PracticePlayer $player, Skin $skin, bool $save = false): void
    {
        $closure = function() use ($player): void {
            $name = $player->getName();

            $canvas = imagecreatetruecolor(self::TEMPLATE_SIZE, self::TEMPLATE_SIZE);

            $transparentColor = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
            imagefill($canvas, 0, 0, $transparentColor);
            imagesavealpha($canvas, true);

            $skin = Path::join(self::$dataFolder, "players", "skin", "$name.png");

            $skin = ErrorToExceptionHandler::trapAndRemoveFalse(fn() => imagecreatefrompng($skin));
            imagecopy($canvas, $skin, 0, 0, 0, 0, self::SECTION_SIZE, self::SECTION_SIZE);
            imagedestroy($skin);

            $defaultPath = Path::join(self::$dataFolder, "cosmetic");

            $path = Path::join($defaultPath, "default_geometry.json");
            $p = json_decode(file_get_contents($path), true);

            $cosmetics = $player->getData()->getCosmetics();
            foreach ([$cosmetics->getHat(), $cosmetics->getBackpack(), $cosmetics->getBelt()] as $type) {
                if ($type !== null) {
                    $cosmetic = self::getCosmetic($type);
                    if ($cosmetic !== null) {
                        $c = json_decode(file_get_contents(Path::join($defaultPath, 'geo', $cosmetic->getData()['geo'])), true);

                        [$x, $y, $sX, $sY] = match ($cosmetic->getType()) {
                            self::TYPE_HAT => [self::SECTION_SIZE, 0, 0, 0],
                            self::TYPE_BACKPACK => [0, self::SECTION_SIZE, 0, 0],
                            self::TYPE_BELT => [self::SECTION_SIZE, self::SECTION_SIZE, 0, 0]
                        };

                        $co = ErrorToExceptionHandler::trapAndRemoveFalse(fn() => imagecreatefrompng(Path::join($defaultPath, 'img', $cosmetic->getData()['img'])));
                        imagecopy($canvas, $co, $x, $y, $sX, $sY, self::SECTION_SIZE, self::SECTION_SIZE);
                        imagedestroy($co);

                        $p['minecraft:geometry'][1]['bones'][] = $c; // classic
                        $p['minecraft:geometry'][2]['bones'][] = $c; // slim
                    }
                }
            }

            imagepalettetotruecolor($canvas);

            $skinData = "";
            for ($y = 0; $y < self::TEMPLATE_SIZE; $y++) {
                for ($x = 0; $x < self::TEMPLATE_SIZE; $x++) {
                    // https://www.php.net/manual/en/function.imagecolorat.php
                    $rgba = imagecolorat($canvas, $x, $y);
                    $a = ($rgba >> 24) & 0xff;
                    $r = ($rgba >> 16) & 0xff;
                    $g = ($rgba >> 8) & 0xff;
                    $b = $rgba & 0xff;
                    $skinData .= chr($r) . chr($g) . chr($b) . chr(~(($a << 1) | ($a >> 6)) & 0xff);
                }
            }

            imagedestroy($canvas);

            $oldSkin = $player->getSkin();
            $cape = '';

            if (($c = $cosmetics->getCape()) !== null) {
                $c = self::getCosmetic($c);
                if ($c !== null) $cape = $c->getData()['img'];
            }

            if (!in_array($oldSkin->getGeometryName(), ["geometry.humanoid.customSlim", "geometry.humanoid.custom"], true)) $name = 'geometry.humanoid.custom';
            else $name = $oldSkin->getGeometryName();
            
            $player->setSkin(new Skin($oldSkin->getSkinId(), $skinData, $cape, $name, json_encode($p)));
            $player->sendSkin();
        };

        if ($save) {
            $resolver = new PromiseResolver();
            $resolver->getPromise()->onCompletion($closure, function (): void {});

            $task = new SkinSaveTask($skin->getSkinData(), Path::join(self::$dataFolder, "players", "skin", $player->getName() . '.png'), CosmeticManager::getSkinTransparencyPercentage($skin->getSkinData()) > 75 || !in_array($skin->getGeometryName(), ["geometry.humanoid.customSlim", "geometry.humanoid.custom"], true), self::$dataFolder, $resolver);
            Server::getInstance()->getAsyncPool()->submitTask($task);
        } else $closure();
    }

    public static function getSkinTransparencyPercentage(string $skinData) : int{
        switch(strlen($skinData)){
            case 8192:
                $maxX = 64;
                $maxY = 32;
                $bounds = self::$skinBounds[self::BOUNDS_64_32];
                break;
            case 16384:
                $maxX = 64;
                $maxY = 64;
                $bounds = self::$skinBounds[self::BOUNDS_64_64];
                break;
            case 65536:
                $maxX = 128;
                $maxY = 128;
                $bounds = self::$skinBounds[self::BOUNDS_128_128];
                break;
            default:
                throw new InvalidArgumentException("Inappropriate skin data length: " . strlen($skinData));
        }
        $transparentPixels = $pixels = 0;
        foreach($bounds as $bound){
            if($bound["max"]["x"] > $maxX || $bound["max"]["y"] > $maxY){
                continue;
            }
            for($y = $bound["min"]["y"]; $y <= $bound["max"]["y"]; $y++){
                for($x = $bound["min"]["x"]; $x <= $bound["max"]["x"]; $x++){
                    $key = (($maxX * $y) + $x) * 4;
                    $a = ord($skinData[$key + 3]);
                    if($a < 127){
                        ++$transparentPixels;
                    }
                    ++$pixels;
                }
            }
        }
        return (int) round($transparentPixels * 100 / max(1, $pixels));
    }

    public static function getCubes(array $geometryData) : array{
        $cubes = [];
        foreach($geometryData["bones"] as $bone){
            if(!isset($bone["cubes"])){
                continue;
            }
            if($bone["mirror"] ?? false){
                throw new InvalidArgumentException("Unsupported geometry data");
            }
            foreach($bone["cubes"] as $cubeData){
                $cube = [];
                $cube["x"] = $cubeData["size"][0];
                $cube["y"] = $cubeData["size"][1];
                $cube["z"] = $cubeData["size"][2];
                $cube["uvX"] = $cubeData["uv"][0];
                $cube["uvY"] = $cubeData["uv"][1];
                $cubes[] = $cube;
            }
        }
        return $cubes;
    }

    public static function getSkinBounds(array $cubes, float $scale = 1.0) : array{
        $bounds = [];
        foreach($cubes as $cube){
            $x = (int) ($scale * $cube["x"]);
            $y = (int) ($scale * $cube["y"]);
            $z = (int) ($scale * $cube["z"]);
            $uvX = (int) ($scale * $cube["uvX"]);
            $uvY = (int) ($scale * $cube["uvY"]);
            $bounds[] = ["min" => ["x" => $uvX + $z, "y" => $uvY], "max" => ["x" => $uvX + $z + (2 * $x) - 1, "y" => $uvY + $z - 1]];
            $bounds[] = ["min" => ["x" => $uvX, "y" => $uvY + $z], "max" => ["x" => $uvX + (2 * ($z + $x)) - 1, "y" => $uvY + $z + $y - 1]];
        }
        return $bounds;
    }

    public static function getSkinDataFromPNG(string $path) : string{
        $image = ErrorToExceptionHandler::trapAndRemoveFalse(fn() => imagecreatefrompng($path));
        [$width, $height] = Utils::assumeNotFalse(getimagesize($path));
        $bytes = '';
        for ($y = 0; $y < $height; ++$y) {
            for ($x = 0; $x < $width; ++$x) {
                $color = @imagecolorsforindex($image, @imagecolorat($image, $x, $y));
                $bytes .= chr($color['red']) . chr($color['green']) . chr($color['blue']) . chr((($color['alpha'] << 1) ^ 0xff) - 1);
            }
        }
        imagedestroy($image);
        return $bytes;
    }

    public static function getCosmetic(string $id): ?Cosmetic
    {
        return self::$cosmetics[$id] ?? null;
    }
}