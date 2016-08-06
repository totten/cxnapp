<?php
namespace Civi\MemberStatusBundle;

/**
 * Class MembershipCheckerInterface
 * @package Civi\MemberStatusBundle
 *
 * The MembershipChecker service is used to determine whether a given
 * connection (appId/siteUrl/viaPort)
 */
interface MembershipCheckerInterface {

  /**
   * Determine whether the given connection has an active membership.
   *
   * @param \Civi\Cxn\AppBundle\Entity\CxnEntity $cxn
   * @return bool
   */
  public function checkCxn($cxn);

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
  public function check($appId, $siteUrl, $viaPort);

}