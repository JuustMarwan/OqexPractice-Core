<?php

namespace xSuper\OqexPractice\entities;

use Oqex\Skyblock\ces\utils\AttackReaction;
use Oqex\Skyblock\entities\dungeon\BlazeEntity;
use pocketmine\block\utils\DyeColor;
use pocketmine\color\Color;
use pocketmine\data\bedrock\DyeColorIdMap;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\entity\Living;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\math\RayTraceResult;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\entity\Entity;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\player\Player;

class VotePartySheepEntity extends Living
{
    private const TAG_IS_BABY = 'IsBaby';
    private const TAG_COLOR = 'Color';

    private DyeColor $color = DyeColor::WHITE;

    public function setColor(DyeColor $color): void
    {
        $this->color = $color;
        $this->networkPropertiesDirty = true;
    }

    public function getColor(): DyeColor
    {
        return $this->color;
    }

    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(0.675, 0.45);
    }

    public static function getNetworkTypeId(): string
    {
        return EntityIds::SHEEP;
    }

    public function getName(): string
    {
        return 'Sheep';
    }

    public function initEntity(CompoundTag $nbt): void
    {
        parent::initEntity($nbt);

        if(
            ($colorTag = $nbt->getTag(self::TAG_COLOR)) instanceof ByteTag &&
            ($color = DyeColorIdMap::getInstance()->fromId($colorTag->getValue())) !== null
        ){
            $this->color = $color;
        }else{
            $this->color = DyeColor::WHITE;
        }

        $this->setScale(0.6);
    }

    public function onInteract(Player $player, Vector3 $clickPos): bool
    {
        $this->flagForDespawn();
        return true;
    }

    public function saveNBT(): CompoundTag
    {
        $nbt = parent::saveNBT();

        $nbt->setByte(self::TAG_IS_BABY, 1);
        $nbt->setByte(self::TAG_COLOR, DyeColorIdMap::getInstance()->toId($this->color));

        return $nbt;
    }

    protected function syncNetworkData(EntityMetadataCollection $properties) : void{
        parent::syncNetworkData($properties);
        $properties->setGenericFlag(EntityMetadataFlags::BABY, true);

        $properties->setByte(EntityMetadataProperties::COLOR, DyeColorIdMap::getInstance()->toId($this->color));
    }
}