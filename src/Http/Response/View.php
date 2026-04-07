<?php

namespace PXP\Http\Response;

use PXP\Exceptions\ViewNotFoundException;
use Override;

class View extends Template
{
    #[Override]
    protected function find(): string
    {
        // user-defined views
        $user = path("views/$this->view.php");

        if (file_exists($user)) {
            return $user;
        }

        // framework-internal views
        $internal = path("views/$this->view.php", internal: true);

        if (file_exists($internal)) {
            return $internal;
        }

        throw new ViewNotFoundException($this->view);
    }
}
