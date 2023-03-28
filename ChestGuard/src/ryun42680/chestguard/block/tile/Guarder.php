<?php

namespace ryun42680\chestguard\block\tile;

use pocketmine\player\Player;

interface Guarder
{

    const TAG_OWNER = 'guard.owner';
    const TAG_SHARED = 'guard.shared.players';
    const TAG_LOCKED = 'guard.locked';

    public function isOwner(Player $player): bool;

    public function getOwner(): string;

    public function isLocked(): bool;

    public function setLocked(bool $locked): void;

    public function getSharedPlayers(): array;

    public function isSharedPlayer(string $player): bool;

    public function addSharedPlayer(string $player): void;

    public function removeSharedPlayer(string $player): void;

    public function canOpenChest(Player $player): bool;
}