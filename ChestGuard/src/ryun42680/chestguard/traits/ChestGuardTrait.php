<?php

namespace ryun42680\chestguard\traits;

use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use ryun42680\chestguard\block\tile\Guarder;

trait ChestGuardTrait
{

    protected ?array $guardData = null;

    public function setup(Player $owner): void
    {
        $this->guardData = [
            Guarder::TAG_OWNER => strtolower($owner->getName()),
            Guarder::TAG_LOCKED => true,
            Guarder::TAG_SHARED => []
        ];
    }

    public function isOwner(Player $player): bool
    {
        return strtolower($player->getName()) === $this->getOwner();
    }

    public function getOwner(): string
    {
        return $this->guardData [Guarder::TAG_OWNER];
    }

    public function isLocked(): bool
    {
        return $this->guardData [Guarder::TAG_LOCKED] ?? true;
    }

    public function setLocked(bool $locked): void
    {
        $this->guardData [Guarder::TAG_LOCKED] = $locked;
    }

    public function getSharedPlayers(): array
    {
        return $this->guardData [Guarder::TAG_SHARED];
    }

    public function isSharedPlayer(string $player): bool
    {
        return in_array(strtolower($player), $this->getSharedPlayers());
    }

    public function addSharedPlayer(string $player): void
    {
        $this->guardData [Guarder::TAG_SHARED] [] = strtolower($player);
    }

    public function removeSharedPlayer(string $player): void
    {
        unset($this->guardData [Guarder::TAG_SHARED] [array_search(strtolower($player), $this->guardData [Guarder::TAG_SHARED])]);
    }

    public function canOpenChest(Player $player): bool
    {
        return in_array(strtolower($player->getName()), array_merge([$this->getOwner()], $this->getSharedPlayers())) or !$this->isLocked() or $player->hasPermission(DefaultPermissions::ROOT_OPERATOR);
    }

    protected function loadGuardData(CompoundTag $tag): void
    {
        if (($ownerTag = $tag->getTag(Guarder::TAG_OWNER)) instanceof StringTag) {
            $this->guardData [Guarder::TAG_OWNER] = $ownerTag->getValue();
        }

        if (($lockedTag = $tag->getTag(Guarder::TAG_LOCKED)) instanceof StringTag) {
            $this->guardData [Guarder::TAG_LOCKED] = (bool)$lockedTag->getValue();
        }

        if (($sharedTag = $tag->getTag(Guarder::TAG_SHARED)) instanceof ListTag) {
            $this->guardData [Guarder::TAG_SHARED] = array_map(function (StringTag $stringTag): string {
                return $stringTag->getValue();
            }, $sharedTag->getValue());
        }
    }

    protected function saveGuardData(CompoundTag $tag): void
    {
        if (is_array($this->guardData)) {
            $tag->setString(Guarder::TAG_OWNER, $this->guardData [Guarder::TAG_OWNER]);
            $tag->setString(Guarder::TAG_LOCKED, (string)$this->guardData [Guarder::TAG_LOCKED]);
            $tag->setTag(Guarder::TAG_SHARED, new ListTag(array_map(function (string $value): StringTag {
                return new StringTag($value);
            }, $this->guardData [Guarder::TAG_SHARED]), NBT::TAG_String));
        }
    }
}