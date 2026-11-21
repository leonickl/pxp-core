<?php

namespace PXP\Data\Migrations;

use Closure;
use PXP\Data\DB;

final class Migration
{
    public function __construct(private Closure $up) {}

    public function up(DB $db)
    {
        ($this->up)(db: $db);
    }
}
