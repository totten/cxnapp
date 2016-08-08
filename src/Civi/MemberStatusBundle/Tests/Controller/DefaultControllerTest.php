<?php

namespace Civi\MemberStatusBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase {
  public function testHealth() {
    $client = static::createClient();
    $client->request('GET', '/memberstatus/health');
    $this->assertEquals(
      $client->getInternalResponse()->getContent(),
      "ok memberships.static"
    );
  }
}
