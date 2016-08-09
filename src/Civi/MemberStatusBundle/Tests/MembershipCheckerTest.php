<?php
namespace Civi\MemberStatusBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MembershipCheckerTest extends WebTestCase {

  public function testStaticType() {
    $container = $this->createContainer();
    /** @var \Civi\MemberStatusBundle\MembershipChecker $checker */
    $checker = $container->get('memberships.checker');
    $checker->setSourceId('memberships.static');

    $this->assertTrue($checker->check('app:org.civicrm.foo', 'http://dmaster.l/sites/all/modules/civicrm/extern/cxn.php', NULL));
    $this->assertTrue($checker->check('app:org.civicrm.foo', 'https://dmaster.l/sites/all/modules/civicrm/extern/cxn.php', NULL));
    $this->assertFalse($checker->check('app:org.civicrm.foo', 'http://dmaster.l/sites/all/modules/civicrm/extern/cxn.php', 'via-example.com:123'));
    $this->assertFalse($checker->check('app:org.civicrm.foo', 'http://snickerdoodle.com/dmaster.l/sites/all/modules/civicrm/extern/cxn.php', NULL));

    $this->assertTrue($checker->check('app:org.civicrm.foo', 'http://wpmaster.l/wp-content/plugins/civicrm/civicrm/extern/cxn.php', NULL));
    $this->assertTrue($checker->check('app:org.civicrm.foo', 'https://wpmaster.l/wp-content/plugins/civicrm/civicrm/extern/cxn.php', NULL));

    $this->assertTrue($checker->check('app:org.civicrm.foo', 'http://via-example-1.l/sites/all/modules/civicrm/extern/cxn.php', NULL));
    $this->assertTrue($checker->check('app:org.civicrm.foo', 'http://via-example-1.l/sites/all/modules/civicrm/extern/cxn.php', 'example-1.com:123'));
    $this->assertFalse($checker->check('app:org.civicrm.foo', 'http://via-example-1.l/sites/all/modules/civicrm/extern/cxn.php', 'example-2.com:123'));
  }

  public function testCivicrmOrgSql() {
    $container = $this->createContainer();

    if (!$container->hasParameter('civicrmorg_database_name')
      || !$container->getParameter('civicrmorg_database_name')
      || $container->getParameter('civicrmorg_database_name') === 'IGNORE'
    ) {
      $this->markTestIncomplete("testCivicrmOrgSql requires database credentials");
    }

    /** @var \Civi\MemberStatusBundle\MembershipChecker $checker */
    $checker = $container->get('memberships.checker');
    $checker->setSourceId('memberships.civicrmorg_sql');

    $this->assertTrue($checker->check('app:org.civicrm.foo', 'http://civicrm.org/sites/all/modules/civicrm/extern/cxn.php', NULL));
    $this->assertTrue($checker->check('app:org.civicrm.foo', 'https://civicrm.org/sites/all/modules/civicrm/extern/cxn.php', NULL));
    $this->assertFalse($checker->check('app:org.civicrm.foo', 'http://civicrm.org/sites/all/modules/civicrm/extern/cxn.php', 'via-example.com:123'));
    $this->assertFalse($checker->check('app:org.civicrm.foo', 'http://snickerdoodle.com/civicrm.org/sites/all/modules/civicrm/extern/cxn.php', NULL));
  }

  public function getWebSourceIds() {
    return array(
      array('memberships.civicrmorg_http_uncached'),
      array('memberships.civicrmorg_http'),
    );
  }

  /**
   * @param string $webServiceId
   *   Ex: "memberships.civicrmor_http"
   * @dataProvider getWebSourceIds
   */
  public function testWebRoundtrip($webServiceId) {
    // Create a MembershipChecker which reads from our web service.
    $downstreamContainer = $this->createContainer();
    /** @var \Civi\MemberStatusBundle\MembershipChecker $downstreamChecker */
    $downstreamContainer->get('memberships.civicrmorg_http_uncached')
      ->setUrl('/memberstatus/find')
      ->setHttp($this->createClient());
    $downstreamChecker = $downstreamContainer->get('memberships.checker');
    $downstreamChecker->setSourceId($webServiceId);

    // Check that downstream gets the data from upstream.
    $this->assertTrue($downstreamChecker->check('app:org.civicrm.foo', 'http://dmaster.l/sites/all/modules/civicrm/extern/cxn.php', NULL));
    $this->assertTrue($downstreamChecker->check('app:org.civicrm.foo', 'https://dmaster.l/sites/all/modules/civicrm/extern/cxn.php', NULL));
    $this->assertFalse($downstreamChecker->check('app:org.civicrm.foo', 'http://dmaster.l/sites/all/modules/civicrm/extern/cxn.php', 'via-example.com:123'));
    $this->assertFalse($downstreamChecker->check('app:org.civicrm.foo', 'http://snickerdoodle.com/dmaster.l/sites/all/modules/civicrm/extern/cxn.php', NULL));
  }

  /**
   * @return \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected function createContainer() {
    $kernel = $this->createKernel();
    $kernel->boot();
    $container = $kernel->getContainer();
    return $container;
  }

}
