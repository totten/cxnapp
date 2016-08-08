<?php
namespace Civi\MemberStatusBundle;

use Civi\MemberStatusBundle\Reader\ReaderFactoryInterface;
use Civi\MemberStatusBundle\Reader\ReaderHelper;
use Ddeboer\DataImport\Filter\CallbackFilter;
use Ddeboer\DataImport\Reader\ArrayReader;
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
   * @var ContainerInterface
   */
  protected $container;

  /**
   * @var string
   *
   * The name of a Symfony service which provides the list of memberships.
   * The service should implement ReaderInterface or ReaderFactoryInterface.
   */
  protected $sourceId;

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
    $memberships = $this->find($appId, $siteUrl, $viaPort);
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
   *   List of records. Each contains 'url', 'via_port', 'is_active'.
   */
  public function find($appId, $siteUrl, $viaPort) {
    if (!$this->getSourceId()) {
      throw new \RuntimeException("memberships_source is not configured");
    }

    /** @var ReaderInterface $reader */
    $reader = ReaderHelper::toReader($this->container->get($this->getSourceId()),
      array(
        'cxn_app_id' => $appId,
        'cxn_site_url' => $siteUrl,
        'cxn_via_port' => $viaPort,
      )
    );

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


  public function setContainer(ContainerInterface $container = NULL) {
    $this->container = $container;
  }

  /**
   * @return mixed
   */
  public function getSourceId() {
    return $this->sourceId;
  }

  /**
   * @param mixed $sourceId
   * @return $this
   */
  public function setSourceId($sourceId) {
    $this->sourceId = $sourceId;
    return $this;
  }

}
