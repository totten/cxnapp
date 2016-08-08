<?php

namespace Civi\MemberStatusBundle\Controller;

use Civi\MemberStatusBundle\MembershipChecker;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller {

  /**
   * @var MembershipChecker
   */
  protected $checker;

  /**
   * @param ContainerInterface $container
   * @param MembershipChecker $checker
   */
  public function __construct(
    ContainerInterface $container,
    MembershipChecker $checker
  ) {
    $this->setContainer($container);
    $this->checker = $checker;
  }

  /**
   * Check if a specific organization is a member.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function findAction(Request $request) {
    $memberships = $this->checker->find(
      $request->request->get('cxn_app_id'),
      $request->request->get('cxn_site_url'),
      $request->request->get('cxn_via_port')
    );
    return new Response(
      json_encode($memberships),
      200,
      array('Content-Type' => 'application/json')
    );
  }

  /**
   * Determine if we're generally configured/working.
   */
  public function healthAction() {
    $sourceId = $this->checker->getSourceId();

    $canaries = array(
      'memberships.civicrmorg_sql' => 'https://civicrm.org/sites/all/modules/civicrm/extern/cxn.php',
      'memberships.civicrmorg_http' => 'https://civicrm.org/sites/all/modules/civicrm/extern/cxn.php',
      'memberships.static' => 'https://dmaster.l/sites/all/modules/civicrm/extern/cxn.php',
    );
    $status = $this->checker->check('app:org.civicrm.profile',
      $canaries[$sourceId], NULL)
      ? 'ok' : 'error';

    return new Response(
      "$status $sourceId",
      $status === 'ok' ? 200 : 500,
      array(
        'Content-Type' => 'text/plain',
      )
    );
  }

}
