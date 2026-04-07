<?php

namespace PXP\Http\Response;

use PXP\Exceptions\ViewNotFoundException;
use Override;

class Plate extends View
{
    private function findPlate(): string
    {
        $user = path("views/$this->view.plate");

        if (file_exists($user)) {
            return $user;
        }

        $internal = path("views/$this->view.plate", internal: true);

        if (file_exists($internal)) {
            return $internal;
        }

        throw new ViewNotFoundException($this->view);
    }

    #[Override]
    protected function find(): string
    {
        $plate = $this->findPlate();
        $hash = hash_file('sha256', $plate);
        $php = path("cache/plate/$hash.php");

        if (! file_exists('cache/plate')) {
            mkdir('cache/plate', recursive: true);
        }

        if (! file_exists($php)) {
            file_put_contents($php, LeoNickl\Plate\Plate::file($plate));
        }
        
        return $php;
    }

    public function layout(string $view)
    {
        return self::make($view, [
            'slot' => $this->render(),
        ]);
    }
}
