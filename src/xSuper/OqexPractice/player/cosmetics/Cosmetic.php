<?php

namespace xSuper\OqexPractice\player\cosmetics;

class Cosmetic
{
    public function __construct(private string $id, private string $name, private string $rarity, private int $season, private string $type, private array $data)
    {

    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRarity(): string
    {
        return $this->rarity;
    }

    public function getSeason(): int
    {
        return $this->season;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getType(): string
    {
        return $this->type;
    }
}