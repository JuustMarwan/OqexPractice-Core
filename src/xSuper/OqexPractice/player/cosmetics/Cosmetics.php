<?php

namespace xSuper\OqexPractice\player\cosmetics;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use xSuper\OqexPractice\OqexPractice;

class Cosmetics
{
    private string $equippedChatColor = '§f';
    private ?string $equippedPotColor = null;
    private ?string $equippedKillPhrase = null;

    public function getEquippedKillPhrase(): ?string
    {
        return $this->equippedKillPhrase;
    }

    private ?string $equippedTrail = null;
    private ?string $equippedTag = null;

    private ?string $equippedHat = null;

    public function setEquippedChatColor(string $equippedChatColor): void
    {
        $this->equippedChatColor = $equippedChatColor;
        $this->save($this->uuid);
    }

    public function setEquippedPotColor(?string $equippedPotColor): void
    {
        $this->equippedPotColor = $equippedPotColor;
        $this->save($this->uuid);
    }

    public function setEquippedKillPhrase(?string $equippedKillPhrase): void
    {
        $this->equippedKillPhrase = $equippedKillPhrase;
        $this->save($this->uuid);
    }

    public function setEquippedTrail(?string $equippedTrail): void
    {
        $this->equippedTrail = $equippedTrail;
        $this->save($this->uuid);
    }

    public function setEquippedTag(?string $equippedTag): void
    {
        $this->equippedTag = $equippedTag;
        $this->save($this->uuid);
    }

    public function setEquippedHat(?string $equippedHat): void
    {
        $this->equippedHat = $equippedHat;
        $this->save($this->uuid);
    }

    public function setEquippedBackpack(?string $equippedBackpack): void
    {
        $this->equippedBackpack = $equippedBackpack;
        $this->save($this->uuid);
    }

    public function setEquippedBelt(?string $equippedBelt): void
    {
        $this->equippedBelt = $equippedBelt;
        $this->save($this->uuid);
    }

    public function setEquippedCape(?string $equippedCape): void
    {
        $this->equippedCape = $equippedCape;
        $this->save($this->uuid);
    }

    private ?string $equippedBackpack = null;
    private ?string $equippedBelt = null;
    private ?string $equippedCape = null;


    private ?array $ownedChatColors = null;
    private ?array $ownedPotColors = null;
    private ?array $ownedKillPhrases = null;
    private ?array $ownedTrails = null;
    private ?array $ownedTags = null;

    private ?array $ownedHats = null;
    private ?array $ownedBackpacks = null;
    private ?array $ownedBelts = null;
    private ?array $ownedCapes = null;

    public function __construct(private UuidInterface $uuid)
    {
    }

    public function init(array $equipped, array $owned): void
    {
        $this->equippedChatColor = $equipped['chatColor'] ?? '§f';
        $this->equippedPotColor = $equipped['potColor'] ?? null;
        $this->equippedKillPhrase = $equipped['killPhrase'] ?? null;
        $this->equippedTrail = $equipped['trail'] ?? null;
        $this->equippedTag = $equipped['tag'] ?? null;

        $this->equippedHat = $equipped['hat'] ?? null;
        $this->equippedBackpack = $equipped['backpack'] ?? null;
        $this->equippedBelt = $equipped['belt'] ?? null;
        $this->equippedCape = $equipped['cape'] ?? null;

        $this->ownedHats = $owned['hats'] ?? [];
        $this->ownedBackpacks = $owned['backpacks'] ?? [];
        $this->ownedBelts = $owned['belts'] ?? [];
        $this->ownedCapes = $owned['capes'] ?? [];
        $this->ownedChatColors = $owned['chatColors'] ?? [];
        $this->ownedPotColors = $owned['potColors'] ?? [];
        $this->ownedKillPhrases = $owned['killPhrases'] ?? [];
        $this->ownedTrails = $owned['trails'] ?? [];
        $this->ownedTags = $owned['tags'] ?? [];
    }

    public function getHat(): ?string
    {
        return $this->equippedHat;
    }

    public function getBackpack(): ?string
    {
        return $this->equippedBackpack;
    }

    public function getBelt(): ?string
    {
        return $this->equippedBelt;
    }

    public function getCape(): ?string
    {
        return $this->equippedCape;
    }

    public function getTag(): ?string
    {
        return $this->equippedTag;
    }

    public function getChatColor(): string
    {
        return $this->equippedChatColor;
    }

    public function save(UuidInterface $uuid) : void{
        return;

        $uuidString = $uuid->toString();
        $db = OqexPractice::getDatabase();

        $db->executeChange('oqex-practice.cosmetics.equipped.hat.set', [
            'uuid' => $uuidString,
            'hat' => $this->equippedHat
        ]);

        $db->executeChange('oqex-practice.cosmetics.equipped.backpack.set', [
            'uuid' => $uuidString,
            'backpack' => $this->equippedBackpack
        ]);

        $db->executeChange('oqex-practice.cosmetics.equipped.belt.set', [
            'uuid' => $uuidString,
            'belt' => $this->equippedBelt
        ]);


        $db->executeChange('oqex-practice.cosmetics.equipped.cape.set', [
            'uuid' => $uuidString,
            'cape' => $this->equippedCape
        ]);

        $db->executeChange('oqex-practice.cosmetics.equipped.potColor.set', [
            'uuid' => $uuidString,
            'potColor' => $this->equippedPotColor
        ]);

        $db->executeChange('oqex-practice.cosmetics.equipped.trail.set', [
            'uuid' => $uuidString,
            'trail' => $this->equippedTrail
        ]);

        $db->executeChange('oqex-practice.cosmetics.equipped.tag.set', [
            'uuid' => $uuidString,
            'tag' => $this->equippedTag
        ]);

        $db->executeChange('oqex-practice.cosmetics.equipped.killPhrase.set', [
            'uuid' => $uuidString,
            'killPhrase' => $this->equippedKillPhrase
        ]);

        $db->executeChange('oqex-practice.cosmetics.equipped.chatColor.set', [
            'uuid' => $uuidString,
            'chatColor' => $this->equippedChatColor
        ]);

        foreach ($this->ownedHats as $hat) {
            $db->executeInsert('oqex-practice.cosmetics.owned.hats.save', [
                'uuid' => $uuidString,
                'hat' => $hat
            ]);
        }

        foreach ($this->ownedBackpacks as $backpack) {
            $db->executeInsert('oqex-practice.cosmetics.owned.backpacks.save', [
                'uuid' => $uuidString,
                'backpack' => $backpack
            ]);
        }

        foreach ($this->ownedBelts as $belt) {
            $db->executeInsert('oqex-practice.cosmetics.owned.belts.save', [
                'uuid' => $uuidString,
                'belt' => $belt
            ]);
        }

        foreach ($this->ownedCapes as $cape) {
            $db->executeInsert('oqex-practice.cosmetics.owned.capes.save', [
                'uuid' => $uuidString,
                'cape' => $cape
            ]);
        }

        foreach ($this->ownedTrails as $trail) {
           $db->executeInsert('oqex-practice.cosmetics.owned.trials.save', [
                'uuid' => $uuidString,
                'trail' => $trail
           ]);
        }

        foreach ($this->ownedTags as $tag) {
            $db->executeInsert('oqex-practice.cosmetics.owned.tags.save', [
                'uuid' => $uuidString,
                'tag' => $tag
            ]);
        }

        foreach ($this->ownedKillPhrases as $killPhrase) {
            $db->executeInsert('oqex-practice.cosmetics.owned.killPhrases.save', [
                'uuid' => $uuidString,
                'killPhrase' => $killPhrase
            ]);
        }

        foreach ($this->ownedPotColors as $color) {
            $db->executeInsert('oqex-practice.cosmetics.owned.potColors.save', [
                'uuid' => $uuidString,
                'color' => $color
            ]);
        }

        foreach ($this->ownedChatColors as $color) {
            $db->executeInsert('oqex-practice.cosmetics.owned.chatColors.save', [
                'uuid' => $uuidString,
                'color' => $color
            ]);
        }
    }
}