<?php

namespace Millancore\Pesto;

class View
{
    public function __construct(
        private Environment $env,
        private string $name,
        private array $data = [],
    )
    {}

    public function render()
    {
        //return $this->env->render($this->name, $this->data);
    }
}