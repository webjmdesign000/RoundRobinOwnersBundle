<?php

namespace RoundRobinOwnersBundle\Helper;

class RoundRobinHelper
{
    public static function assignRoundRobin(array $items, int $currentIndex): int
    {
        return $currentIndex % count($items);
    }
}
