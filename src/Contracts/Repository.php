<?php

namespace YlsIdeas\FeatureFlags\Contracts;

interface Repository
{
    /**
     * @param string $feature
     * @return bool|null
     */
    public function accessible(string $feature);

    /**
     * @return array
     */
    public function all();

    /**
     * @param string $feature
     * @return void
     */
    public function turnOn(string $feature);

    /**
     * @param string $feature
     * @return void
     */
    public function turnOff(string $feature);
}
