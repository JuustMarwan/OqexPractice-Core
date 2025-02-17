<?php

declare(strict_types=1);

namespace xSuper\OqexPractice\utils;

use JsonException;
use pocketmine\entity\Skin;
use pocketmine\network\mcpe\convert\LegacySkinAdapter;
use pocketmine\network\mcpe\protocol\types\skin\SkinData;

class PersonaSkinAdapter extends LegacySkinAdapter{
    /**
     * @var SkinData[]
     * @phpstan-var array<int, SkinData>
     */
    private array $personaSkinData = [];

    public function fromSkinData(SkinData $data) : Skin{
        $skin = parent::fromSkinData($data);

        if($data->isPersona()){
            $this->personaSkinData[spl_object_id($skin)] = $data;
        }
        return $skin;
    }

    /** @throws JsonException */
    public function toSkinData(Skin $skin) : SkinData{
        return $this->personaSkinData[spl_object_id($skin)] ?? parent::toSkinData($skin);
    }
}