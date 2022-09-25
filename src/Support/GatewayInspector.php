<?php

namespace YlsIdeas\FeatureFlags\Support;

use YlsIdeas\FeatureFlags\Contracts\ActionableFlag;
use YlsIdeas\FeatureFlags\Contracts\DebuggableFlag;
use YlsIdeas\FeatureFlags\Contracts\Gateway;

/**
 * @see \YlsIdeas\FeatureFlags\Tests\Support\GatewayInspectorTest
 */
class GatewayInspector
{
    public function __construct(
        protected string $name,
        protected Gateway $gateway,
        protected ?FeatureFilter $filter = null,
        protected ?GatewayCache $cache = null,
    ) {
    }

    public function gateway(): Gateway
    {
        return $this->gateway;
    }

    public function filter(): ?FeatureFilter
    {
        return $this->filter;
    }

    public function cache(): ?GatewayCache
    {
        return $this->cache;
    }

    public function handle(ActionableFlag $action, callable $next): ActionableFlag
    {
        if ($action->hasResult()) {
            $this->handleDebug($action, ActionDebugLog::REASON_RESULT_ALREADY_FOUND, $action->getResult());

            return $next($action);
        }

        if ($this->filter && $this->filter->fails($action->feature())) {
            $this->handleDebug($action, ActionDebugLog::REASON_FILTER, $action->getResult());


            return $next($action);
        }

        if ($this->cache && $this->cache->hits($action->feature())) {
            $action->setResult($this->cache->result($action->feature()));
            $this->handleDebug(
                $action,
                ActionDebugLog::REASON_CACHE,
                $action->getResult()
            );

            return $next($action);
        }

        if (($result = $this->gateway->accessible($action->feature())) !== null) {
            $this->cache?->store($action->feature(), $result);
            $action->setResult($result);

            $this->handleDebug(
                $action,
                ActionDebugLog::REASON_RESULT,
                $action->getResult()
            );
        } else {
            $this->handleDebug(
                $action,
                ActionDebugLog::REASON_NO_RESULT,
                $action->getResult()
            );
        }

        return $next($action);
    }

    protected function handleDebug(mixed $action, string $reason, ?bool $result = null): void
    {
        if ($action instanceof DebuggableFlag && $action->isDebuggable()) {
            $action->storeInspectionInformation($this->name, $reason, $result);
        }
    }
}
