<?php

namespace ryun42680\chestguard\block\tile;

use pocketmine\block\tile\Chest as PM_TileChest;
use pocketmine\nbt\tag\CompoundTag;
use ryun42680\chestguard\traits\ChestGuardTrait;

class TileChest extends PM_TileChest implements Guarder
{

    use ChestGuardTrait;

    public function readSaveData(CompoundTag $nbt): void
    {
        parent::readSaveData($nbt);
        $this->loadGuardData($nbt);
    }

    protected function writeSaveData(CompoundTag $nbt): void
    {
        parent::writeSaveData($nbt);
        $this->saveGuardData($nbt);
    }
}