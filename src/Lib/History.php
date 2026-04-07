<?php

namespace PXP\Lib;

class History
{
    /**
     * @return list<string>
     */
    public function history(): array
    {
        return session()->array('history');
    }

    public function last(): string
    {
        return $this->history()[0] ?? '/';
    }
}
