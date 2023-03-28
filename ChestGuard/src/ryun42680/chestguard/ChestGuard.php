<?php

namespace ryun42680\chestguard;

use pocketmine\block\BlockBreakInfo;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\block\BlockTypeInfo;
use pocketmine\block\tile\Chest as PM_TileChest;
use pocketmine\block\tile\Tile;
use pocketmine\block\tile\TileFactory;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\EventPriority;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use ryun42680\chestguard\block\Chest;
use ryun42680\chestguard\block\tile\Guarder;
use ryun42680\chestguard\block\tile\TileChest;
use ryun42680\chestguard\command\AddSharedPlayerCommand;
use ryun42680\chestguard\command\RemoveSharedPlayerCommand;

final class ChestGuard extends PluginBase
{

    use SingletonTrait;

    public static string $prefix = '§l§b[상자보호]§r§7 ';

    protected function onEnable(): void
    {
        $chest = VanillaBlocks::CHEST();
        RuntimeBlockStateRegistry::getInstance()->register(new Chest($chest->getIdInfo(), $chest->getName(), new BlockTypeInfo(BlockBreakInfo::axe(2.5))), true);
        TileFactory::getInstance()->register(TileChest::class, ['Chest', 'minecraft:chest']);
        $this->getServer()->getCommandMap()->registerAll(strtolower($this->getName()), [
            new AddSharedPlayerCommand(), new RemoveSharedPlayerCommand()
        ]);
        $this->getServer()->getPluginManager()->registerEvent(BlockBreakEvent::class, function (BlockBreakEvent $event): void {
            $player = $event->getPlayer();
            $position = $event->getBlock()->getPosition();
            if (($tile = $position->getWorld()->getTile($position)) instanceof Tile) {
                if ($tile instanceof Guarder and $player instanceof Player) {
                    if ($tile->isLocked()) {
                        $event->cancel();
                        if ($player->hasPermission(DefaultPermissions::ROOT_OPERATOR) or $tile->isOwner($player)) {
                            $player->sendMessage(ChestGuard::$prefix . '이 상자의 잠금을 해제했습니다.');
                            $tile->setLocked(false);

                            if (($pair = $tile->getPair()) instanceof PM_TileChest) {
                                if ($pair instanceof Guarder) {
                                    $pair->setLocked(false);
                                }
                            }
                        } else {
                            $player->sendMessage(ChestGuard::$prefix . '이 상자의 소유권을 갖고있지 않습니다.');
                        }
                    }
                }
            }
        }, EventPriority::NORMAL, $this);
    }

    protected function onLoad(): void
    {
        self::setInstance($this);
    }
}