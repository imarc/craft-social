<?php

/**
 * SimpleCache
 *
 * @copyright 2014 iMarc LLC
 * @author Kevin Hamer [kh] <kevin@imarc.net>
 */
trait SimpleCacheTrait
{
	protected $memcached = null;
	protected $memcached_expiration = 3600;

	/**
	 * cache
	 *
	 * @param mixed $key
	 * @param mixed $value
	 * @return void
	 */
	public function cache($key, $value = null)
	{
		if (!class_exists('Memcached')) {
			return false;
		}

		if ($this->memcached === null) {
			$this->memcached = new Memcached($_SERVER['HTTP_HOST']);

			if (count($this->memcached->getServerList()) === 0) {
				$this->memcached->addServer('127.0.0.1', 11211);
				$this->memcached->setOption(Memcached::OPT_LIBKETAMA_COMPATIBLE,  true);
			}
		}

		$long_key = __FILE__ . ' ' . $key;

		if ($value === null) {
			return $this->memcached->get($long_key);

		} else {
			$this->memcached->set($long_key, $value, $this->memcached_expiration);
			return $value;
		}
	}
}
