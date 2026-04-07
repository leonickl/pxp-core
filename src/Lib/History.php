<?php

namespace PXP\Lib;

class History
{
    public function history(): array
    {
        return session()->array('history');
    }

    public function back(int $steps = 1): string
    {
        return $this->history()[$steps - 1] ?? '/';
    }
}
