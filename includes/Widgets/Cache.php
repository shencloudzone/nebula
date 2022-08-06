<?php

namespace Nebula\Widgets;

use Nebula\Common;
use Nebula\Helpers\Cookie;

class Cache extends Base
{
    /**
     * 缓存 id
     *
     * @var string
     */
    private $cacheId;

    /**
     * @var array
     */
    private $caches = [];

    public function execute()
    {
        // 删除过期
        $this->db->delete('caches', [
            'expires[<]' => time(),
        ]);

        $this->caches = $this->db->select('caches', ['name', 'value']);

        $cacheId = Cookie::get('cache_id');
        if (null === $cacheId) {
            $cacheId = Common::hash(uniqid());
            Cookie::set('cache_id', $cacheId);
        }

        $this->cacheId = $cacheId;
    }

    /**
     * 获取 caches
     *
     * @param null|string $name 缓存名
     * @param null|string $defaultValue 默认值
     * @return null|string|array
     */
    public function get($name = null, $defaultValue = null)
    {
        if (null === $name) {
            return $this->caches;
        } else {
            $name = $this->cacheId . '@' . $name;
            return array_values(array_filter($this->caches, function ($cache) use ($name) {
                return $cache['name'] === $name;
            }))[0]['value'] ?? $defaultValue;
        }
    }

    /**
     * 设置缓存项
     *
     * @param string $name 缓存名
     * @param string $value 缓存值
     * @param int $expires 过期时间
     * @return void
     */
    public function set($name, $value, $expires = 60)
    {
        $name = $this->cacheId . '@' . $name;

        $cacheNames = array_map(function ($cache) {
            return $cache['name'];
        }, $this->caches);

        $index = array_search($name, $cacheNames);

        if (false === $index) {
            $this->db->insert('caches', [
                'name' => $name,
                'value' => $value,
                'expires' => time() + $expires
            ]);
            array_push($this->caches, ['name' => $name, 'value' => $value]);
        } else {
            if ($this->caches[$index]['value'] !== $value) {
                $this->db->update('caches', [
                    'value' => $value,
                    'expires' => time() + $expires
                ], ['name' => $name]);
                $this->caches[$index]['value'] = $value;
            }
        }
    }

    /**
     * 设置多个缓存项
     *
     * @param array $caches 缓存项列表
     * @param int $expires 过期时间
     * @return void
     */
    public function sets($caches, $expires = 60)
    {
        foreach ($caches as $name => $value) {
            $this->set($name, $value, $expires);
        }
    }
}
