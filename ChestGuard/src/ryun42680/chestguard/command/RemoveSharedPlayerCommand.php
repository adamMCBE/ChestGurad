<?php

namespace ryun42680\chestguard\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use ryun42680\chestguard\ChestGuard;
use ryun42680\chestguard\ChestGuardQueue;

final class RemoveSharedPlayerCommand extends Command {

    public function __construct() {
        parent::__construct('상자잠금제거', '보호된 상자를 열 권한을 회수합니다.');
        $this->setPermission(DefaultPermissions::ROOT_USER);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if ($sender instanceof Player) {
            if (isset ($args [0])) {
                ChestGuardQueue::$queue [$sender->getName()] [] = [ChestGuardQueue::TYPE_QUEUE_REMOVE, strtolower($args [0])];
                $sender->sendMessage(ChestGuard::$prefix . '이제 제거하길 원하는 상자를 터치해주세요!');
            } else {
                $sender->sendMessage(ChestGuard::$prefix . '/상자잠금제거 [닉네임] - 보호된 상자를 열 권한을 회수합니다.');
            }
        }
    }
}