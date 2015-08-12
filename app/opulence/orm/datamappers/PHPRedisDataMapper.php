<?php
/**
 * Copyright (C) 2015 David Young
 *
 * Defines the Cache data mapper implemented by the PHPRedis library
 */
namespace Opulence\ORM\DataMappers;
use Opulence\Redis\OpulencePHPRedis;

abstract class PHPRedisDataMapper extends RedisDataMapper
{
    /** @var OpulencePHPRedis The Redis cache to use for queries */
    protected $redis = null;

    /**
     * @inheritdoc
     */
    protected function getSetMembersFromRedis($key)
    {
        return $this->redis->sMembers($key);
    }

    /**
     * @inheritdoc
     */
    protected function getSortedSetMembersFromRedis($key)
    {
        return $this->redis->zRange($key, 0, -1);
    }

    /**
     * @inheritdoc
     */
    protected function getValueFromRedis($key)
    {
        return $this->redis->get($key);
    }
} 