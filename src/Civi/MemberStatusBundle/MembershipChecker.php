<?php
namespace Civi\MemberStatusBundle;

use Ddeboer\DataImport\Filter\CallbackFilter;
use Ddeboer\DataImport\Reader\ReaderInterface;
use Ddeboer\DataImport\Workflow;
use Ddeboer\DataImport\Writer\ArrayWriter;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class MembershipChecker
 * @package Civi\MemberStatusBundle
 *
 * The MembershipChecker service is used to determine whether a given
 * connection (appId/siteUrl/viaPort)
 */
class MembershipChecker implements ContainerAwareInterface, MembershipCheckerInterface {

  /**
   * @var \Doctrine\Common\Cache\Cache
   */
  protected $cache;

  /**
   * @var ContainerInterface
   */
  protected $container;

  /**
   * @var string
   *
   * The name of a Symfony service which provides the list of memberships.
   */
  protected $source;

  /**
   * @var int
   */
  protected $negativeTtl = 600;

  /**
   * @var int
   */
  protected $positiveTtl = 600;

  /**
   * Determine whether the given connection has an active membership.
   *
   * @param \Civi\Cxn\AppBundle\Entity\CxnEntity $cxn
   * @return bool
   */
  public function checkCxn($cxn) {
    return $this->check(
      $cxn->getAppId(), $cxn->getSiteUrl(), $cxn->getViaPort());
  }

  /**
   * Determine whether the given connection has an active membership.
   *
   * @param string $appId
   *   Ex: 'app:org.civicrm.profile'.
   * @param string $siteUrl
   *   Ex: 'https://example.com/sites/all/modules/civicrm/extern/cxn.php'.
   * @param string|NULL $viaPort
   *   Ex: 'proxy.example.com:789'
   * @return bool
   */
  public function check($appId, $siteUrl, $viaPort) {
    $key = md5(json_encode(array($appId, $siteUrl, $viaPort)));

    if ($this->cache && $this->cache->contains($key)) {
      $memberships = $this->cache->fetch($key);
    }
    else {
      $memberships = $this->find($appId, $siteUrl, $viaPort);
      if ($this->cache) {
        $ttl = count($memberships) ? $this->positiveTtl : $this->negativeTtl;
        $this->cache->save($key, $memberships, $ttl);
      }
    }

    return count($memberships) > 0;
  }

  /**
   * @param string $appId
   *   Ex: 'app:org.civicrm.profile'.
   * @param string $siteUrl
   *   Ex: 'https://example.com/sites/all/modules/civicrm/extern/cxn.php'.
   * @param string|NULL $viaPort
   *   Ex: 'proxy.example.com:789'
   * @return array
   */
  public function find($appId, $siteUrl, $viaPort) {
    if (!$this->getSource()) {
      throw new \RuntimeException("memberships_source is not configured");
    }

    /** @var ReaderInterface $reader */
    $reader = $this->container->get($this->getSource());
    $params = array(
      'cxn_app_id' => $appId,
      'cxn_site_url' => $siteUrl,
      'cxn_via_port' => $viaPort,
    );

    if ($reader instanceof \Civi\MemberStatusBundle\Reader\ParameterizedReaderInterface) {
      $reader->setParameters($params);
    }
    elseif ($reader instanceof \Ddeboer\DataImport\Reader\DbalReader) {
      $reader->setSqlParameters($params);
    }

    $workflow = new Workflow($reader);

    $expectHost = parse_url($siteUrl, PHP_URL_HOST);
    $workflow->addFilter(new CallbackFilter(function ($item) use ($expectHost) {
      $actualDomain = parse_url($item['url'], PHP_URL_HOST);
      return ($actualDomain === $expectHost);
    }));

    if ($viaPort) {
      $workflow->addFilter(new CallbackFilter(function ($item) use ($viaPort) {
        if (empty($item['via_port'])) {
          return FALSE;
        }
        if ($item['via_port'] === $viaPort) {
          return TRUE;
        }
        return FALSE;
      }));
    }

    $workflow->addFilter(new CallbackFilter(function ($item) {
      return $item['is_active'];
    }));

    $matches = array();
    $workflow->addWriter(new ArrayWriter($matches));
    $workflow->process();
    return $matches;
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

  public function setContainer(ContainerInterface $container = NULL) {
    $this->container = $container;
  }

  /**
   * @return mixed
   */
  public function getSource() {
    return $this->source;
  }

  /**
   * @param mixed $source
   * @return $this
   */
  public function setSource($source) {
    $this->source = $source;
    return $this;
  }

  /**
   * @return int
   */
  public function getNegativeTtl() {
    return $this->negativeTtl;
  }

  /**
   * @param int $negativeTtl
   */
  public function setNegativeTtl($negativeTtl) {
    $this->negativeTtl = $negativeTtl;
  }

  /**
   * @return int
   */
  public function getPositiveTtl() {
    return $this->positiveTtl;
  }

  /**
   * @param int $positiveTtl
   */
  public function setPositiveTtl($positiveTtl) {
    $this->positiveTtl = $positiveTtl;
  }

  /**
   * @param $appId
   * @param $siteUrl
   * @param $viaPort
   * @return object
   */
  public function createReader($appId, $siteUrl, $viaPort) {

    return $reader;
  }

}