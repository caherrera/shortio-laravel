<?php

namespace Shortio\Laravel;

interface ConnectorInterface
{

    public function setConfig(array $config = []);

    public function config($key = null);

    public function getConfig();

    public function getPubKey(): string;

    public function setPubKey(string $pub);

    public function initialize(array $config = []);

    public function getHeaders(): array;
}
