<?php
namespace Civi\MemberStatusBundle\Reader;

use Ddeboer\DataImport\Reader\ArrayReader;
use Ddeboer\DataImport\Reader\ReaderInterface;

/**
 * Class CachedReaderFactory
 * @package Civi\MemberStatusBundle\Reader
 *
 * A wrapper for a Reader or ReaderFactory which stores and reads data
 * from a cache.
 */
class CachedReaderFactory implements ReaderFactoryInterface {

  /**
   * @var ReaderInterface|ReaderFactoryInterface
   */
  protected $source;

  /**
   * @var \Doctrine\Common\Cache\Cache
   */
  protected $cache;

  /**
   * @var int
   *   Seconds to retain data in cache.
   */
  protected $ttl = NULL;

  /**
   * CachedReaderFactory constructor.
   * @param ReaderInterface|ReaderFactoryInterface $source
   * @param \Doctrine\Common\Cache\Cache $cache
   * @param int $ttl
   */
  public function __construct(
    $source,
    \Doctrine\Common\Cache\Cache $cache,
    $ttl
  ) {
    $this->source = $source;
    $this->cache = $cache;
    $this->ttl = $ttl;
  }

  /**
   * @param array $params
   * @return ReaderInterface
   */
  public function createReader($params) {
    ksort($params);
    $key = md5(json_encode($params));
    if ($this->cache->contains($key)) {
      $cacheLine = json_decode($this->cache->fetch($key), TRUE);
    }
    else {
      $reader = ReaderHelper::toReader($this->source, $params);

      $cacheLine = array();
      $cacheLine['rows'] = array();
      foreach ($reader as $row) {
        $cacheLine['rows'][] = $row;
      }
      $cacheLine['fields'] = $reader->getFields();

      if ($this->ttl !== NULL) {
        $this->cache->save($key, json_encode($cacheLine), $this->ttl);
      }
    }

    return new ArrayReader($cacheLine['rows']);
  }

  /**
   * @return \Doctrine\Common\Cache\Cache
   */
  public function getCache() {
    return $this->cache;
  }

  /**
   * @param \Doctrine\Common\Cache\Cache $cache
   */
  public function setCache($cache) {
    $this->cache = $cache;
  }

  /**
   * @return int
   */
  public function getTtl() {
    return $this->ttl;
  }

  /**
   * @param int $ttl
   */
  public function setTtl($ttl) {
    $this->ttl = $ttl;
  }

}
