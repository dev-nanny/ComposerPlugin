<?php

namespace DevNanny\Composer\Plugin\Interfaces;

interface DecoratorInterface
{
    /**
     * @param string $message
     *
     * @return string
     */
    public function decorate($message);
}
