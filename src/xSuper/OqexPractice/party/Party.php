<?php

namespace xSuper\OqexPractice\party;

use pocketmine\player\Player;
use pocketmine\scheduler\CancelTaskException;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use Ramsey\Uuid\Uuid;
use xSuper\OqexPractice\duel\Duel;
use xSuper\OqexPractice\duel\generator\maps\Map;
use xSuper\OqexPractice\duel\special\PartyDuel;
use xSuper\OqexPractice\duel\special\PartyScrimDuel;
use xSuper\OqexPractice\duel\type\Type;
use xSuper\OqexPractice\OqexPractice;
use xSuper\OqexPractice\player\PracticePlayer;
use xSuper\OqexPractice\player\settings\SettingIDS;

class Party
{
	/** @var array<string, self> */
    private static array $parties = [];

    public static function createParty(PracticePlayer $owner): void
    {
        $id = Uuid::uuid4()->toString();
        while (isset(self::$parties[$id])) $id = Uuid::uuid4()->toString();
        self::$parties[$id] = new self($id, $owner->getUniqueId()->toString(), [$owner->getUniqueId()->toString()]);
        $owner->setParty(self::$parties[$id]->getId());
    }

    public static function getParty(string $id): ?self
    {
        return self::$parties[$id] ?? null;
    }

    public static function deleteParty(Party $party): void
    {
        foreach ($party->getActualMembers() as $m) {
            $m->setParty(null);
            $m->sendMessage('§r§l§dPARTY §r§8» §r§7Your party has been disbanded!');
        }

        unset(self::$parties[$party->getId()]);
    }

    /**
     * @return Party[]
     */
    public static function getParties(): array
    {
        return self::$parties;
    }

    private ?PartyDuel $duel = null;
    private ?PartyScrimDuel $scrim = null;
	/** @var array<string, array{int<0, 60>, Type, Map}> */
    private array $scrimInvites = [];
	/** @param list<string> $members */
    public function __construct(private string $id, private string $owner, private array $members)
    {

    }

    public function addScrimRequest(Party $party, Type $type, Map $map): void
    {
        $p = Server::getInstance()->getPlayerByUUID(Uuid::fromString($party->getOwner()));
        if ($p === null || !$p->isOnline()) return;
        $this->scrimInvites[$party->getId()] = [60, $type, $map];
        $o = Server::getInstance()->getPlayerByUUID(Uuid::fromString($this->getOwner()));
        if ($o === null || !$o->isOnline()) return;
        $o->sendMessage('§r§l§dPARTY §r§8» You have a scrim request from §d' . $p->getName() . '§7, accept using §d/party scrim ' . $p->getName());

        OqexPractice::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function () use ($party): void {
            if (!isset($this->scrimInvites[$party->getId()])) throw new CancelTaskException();

            if ($this->scrimInvites[$party->getId()][0] === 0) {
                unset($this->scrimInvites[$party->getId()]);
                throw new CancelTaskException();
            }

            $this->scrimInvites[$party->getId()][0]--;
        }), 20);
    }

    public function hasScrimRequest(PracticePlayer $player): bool
    {
        foreach ($this->scrimInvites as $id => $data) {
            $party = Party::getParty($id);
            if ($party !== null && $party->isMember($player->getUniqueId()->toString())) return true;
        }

        return false;
    }

    public function hasScrimRequestById(string $id): bool
    {
        return isset($this->scrimInvites[$id]);
    }

    public function acceptScrimRequestById(string $id): void
    {
        $party = Party::getParty($id);
        if ($party === null) return;

        $data = $this->scrimInvites[$party->getId()];
        $this->createScrim($data[1], $data[2], $party);
        if (isset($this->scrimInvites[$party->getId()])) unset($this->scrimInvites[$party->getId()]);
    }

    public function acceptScrimRequest(PracticePlayer $player): void
    {
        $p = $player->getParty();

        if ($p === null) return;

        $party = Party::getParty($p);

        if ($party === null) return;
        $data = $this->scrimInvites[$party->getId()];
        $this->createScrim($data[1], $data[2], $party);
        if (isset($this->scrimInvites[$party->getId()])) unset($this->scrimInvites[$party->getId()]);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getOwner(): string
    {
        return $this->owner;
    }

    public function isMember(string $uuid): bool
    {
        return in_array($uuid, $this->members, true);
    }

    public function addPlayer(PracticePlayer $player): void
    {
        $player->setParty($this->getId());
        $this->members[] = $player->getUniqueId()->toString();

        foreach ($this->members as $m) {
            Server::getInstance()->getPlayerByUUID(Uuid::fromString($m))?->sendMessage('§r§l§dPARTY §r§8» §r§d' . $player->getName() . ' §7has joined the party');
        }
    }

    public function invite(PracticePlayer $player, Player $sender): void
    {
        if (!$player->getData()->getSettings()->asBool(SettingIDS::PARTY_INVITES)) {
            $sender->sendMessage('§r§cThat player is not accepting party invites!');
            return;
        }

        foreach ($this->members as $m) {
            Server::getInstance()->getPlayerByUUID(Uuid::fromString($m))?->sendMessage('§r§l§dPARTY §r§8» §r§d' . $player->getName() . ' §7has been invited to the party by §d' . $sender->getName());
        }
        $player->addPartyInvite($this);
    }

    public function kick(PracticePlayer $player, ?PracticePlayer $sender = null): void
    {
        if ($player->getUniqueId()->toString() === $this->owner) {
            self::deleteParty($this);
            return;
        }

        $player->setParty(null);
        if ($player->isOnline()) {
            if ($sender === null) $player->sendMessage('§r§l§dPARTY §r§8» §r§d' . $player->getName() . ' §7you left your party');
            else $player->sendMessage('§r§l§dPARTY §r§8» §r§d' . $player->getName() . ' §7you were kicked from your party by §d' . $sender->getName());
        }

        unset($this->members[array_search($player->getUniqueId()->toString(), $this->members, true)]);

        foreach ($this->members as $m) {
            if ($sender === null) Server::getInstance()->getPlayerByUUID(Uuid::fromString($m))?->sendMessage('§r§l§dPARTY §r§8» §r§d' . $player->getName() . ' §7has left the party');
            else Server::getInstance()->getPlayerByUUID(Uuid::fromString($m))?->sendMessage('§r§l§dPARTY §r§8» §r§d' . $player->getName() . ' §7was kicked from the party by §d' . $sender->getName());
        }
    }

    public function createDuel(Type $type, Map $map): void {
        $d = Duel::createPartyDuel(OqexPractice::getInstance(), $this, $type, $this->getActualMembers(), $map);
        $this->duel = $d;
    }

    public function createScrim(Type $type, Map $map, Party $opponent): void {
        $d = Duel::createScrim(OqexPractice::getInstance(), $this, $type, $this->getActualMembers(), $opponent->getActualMembers(), $map);
        $this->scrim = $d;
    }

    public function getScrim(): ?PartyScrimDuel
    {
        return $this->scrim;
    }

	/** @return list<PracticePlayer> */
    public function getActualMembers(): array
    {
        $p = [];
        foreach ($this->members as $m) {
            $k = Server::getInstance()->getPlayerByUUID(Uuid::fromString($m));
            if ($k instanceof PracticePlayer) $p[] = $k;
        }

        return $p;
    }

    public function removeDuel(): void
    {
        $this->duel = null;
    }

    public function getDuel(): ?PartyDuel
    {
        return $this->duel;
    }
}