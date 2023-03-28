<?php

namespace ryun42680\chestguard;

final class ChestGuardQueue
{

    const TYPE_QUEUE_ADD = 'add.shared.player';
    const TYPE_QUEUE_REMOVE = 'remove.shared.player';

    public static array $queue = [];
}