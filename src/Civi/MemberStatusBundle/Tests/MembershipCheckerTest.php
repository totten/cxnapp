<?php
namespace Civi\MemberStatusBundle\Tests;

use Doctrine\Common\Cache\ArrayCache;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MembershipCheckerTest extends WebTestCase {

  public function testStaticType() {
    $kernel = $this->createKernel();
    $kernel->boot();
    $container = $kernel->getContainer();
    /** @var \Civi\MemberStatusBundle\MembershipChecker $checker */
    $checker = $container->get('memberships.checker');

    $checker->setCache(new ArrayCache());
    $checker->setSource('memberships.static');

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

}