<?php

namespace ryun42680\chestguard\block;

use pocketmine\block\Block;
use pocketmine\block\Chest as PM_Chest;
use pocketmine\block\tile\Chest as PM_TileChest;
use pocketmine\block\tile\Tile;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\BlockTransaction;
use pocketmine\world\format\Chunk;
use pocketmine\world\sound\DoorBumpSound;
use ryun42680\chestguard\block\tile\Guarder;
use ryun42680\chestguard\block\tile\TileChest;
use ryun42680\chestguard\ChestGuard;
use ryun42680\chestguard\ChestGuardQueue;

class Chest extends PM_Chest
{

    public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null): bool
    {
        if ($player instanceof Player) {
            ChestGuard::getInstance()->getScheduler()->scheduleTask(new ClosureTask(function () use ($player) {
                $position = $this->position;
                $world = $position->getWorld();
                $chunk = $world->loadChunk($position->getX() >> Chunk::COORD_BIT_SIZE, $position->getZ() >> Chunk::COORD_BIT_SIZE);
                if ($chunk instanceof Chunk) {
                    if (($pm_tile = $world->getTile($position)) instanceof Tile) {
                        $vanillaNBT = $pm_tile->saveNBT();
                        $pm_tile->close();
                        $tile = new TileChest($world, $position->asVector3());
                        $chunk->addTile($tile);
                        $tile->setup($player);
                        $tile->readSaveData($vanillaNBT);
                    }
                }
            }));
        }
        return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
    }

    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []): bool
    {
        if (($tile = $this->position->getWorld()->getTile($this->position)) instanceof Tile) {
            if ($tile instanceof Guarder and $player instanceof Player) {
                if (!empty(($queue = ChestGuardQueue::$queue [$player->getName()] ?? []))) {
                    foreach ($queue as $key => $data) {
                        unset(ChestGuardQueue::$queue [$player->getName()] [$key]);
                        switch ($data [0]) {
                            case ChestGuardQueue::TYPE_QUEUE_ADD:
                                if (!$tile->isSharedPlayer($data [1])) {
                                    $tile->addSharedPlayer($data [1]);
                                    $player->sendMessage(ChestGuard::$prefix . '해당 플레이어에게 권한을 추가했습니다.');

                                    if (($pair = $tile->getPair()) instanceof PM_TileChest) {
                                        if ($pair instanceof Guarder) {
                                            $pair->addSharedPlayer($data [1]);
                                        }
                                    }
                                } else {
                                    $player->sendMessage(ChestGuard::$prefix . '이미 권한을 갖고있는 플레이어입니다.');
                                }
                                break;

                            case ChestGuardQueue::TYPE_QUEUE_REMOVE:
                                if ($tile->isSharedPlayer($data [1])) {
                                    $tile->removeSharedPlayer($data [1]);
                                    $player->sendMessage(ChestGuard::$prefix . '해당 플레이어에게서 권한을 회수했습니다.');

                                    if (($pair = $tile->getPair()) instanceof PM_TileChest) {
                                        if ($pair instanceof Guarder) {
                                            $pair->removeSharedPlayer($data [1]);
                                        }
                                    }
                                } else {
                                    $player->sendMessage(ChestGuard::$prefix . '권한을 갖고있지 않은 플레이어입니다.');
                                }
                                break;
                        }
                    }
                } else {
                    if ($player->isSneaking()) {
                        $player->sendMessage(ChestGuard::$prefix . '주인: ' . $tile->getOwner());
                        $player->sendMessage(ChestGuard::$prefix . '맴버: ' . implode(', ', $tile->getSharedPlayers()));
                    } else {
                        if ($tile->canOpenChest($player)) {
                            return parent::onInteract($item, $face, $clickVector, $player, $returnedItems);
                        } else {
                            $player->getWorld()->addSound($player->getPosition(), new DoorBumpSound());
                            $player->sendMessage(ChestGuard::$prefix . '이 상자를 열 권한을 갖지 못했습니다.');
                        }
                    }
                }
            }
        }
        return true;
    }
}