<?php
namespace Civi\MemberStatusBundle\Reader;

use Ddeboer\DataImport\Reader\ArrayReader;
use Ddeboer\DataImport\Reader\DbalReader;
use Ddeboer\DataImport\Reader\ReaderInterface;
use Doctrine\DBAL\Connection;

class CivicrmSqlReader implements ReaderFactoryInterface {

  /**
   * @var Connection
   */
  protected $connection;

  /**
   * WebServiceReader constructor.
   * @param \Doctrine\DBAL\Connection $connection
   */
  public function __construct(\Doctrine\DBAL\Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * @param array $params
   * @return ReaderInterface
   */
  public function createReader($params) {
    //    $reader = new DbalReader($this->connection, '
    //        SELECT cstm.member_site_216 AS url, null AS via_port, status.is_current_member AS is_active
    //        FROM civicrm_membership m
    //        INNER JOIN civicrm_membership_status status ON m.status_id = status.id
    //        INNER JOIN civicrm_value_sid_22 cstm ON cstm.entity_id = m.contact_id
    //        WHERE cstm.member_site_216 LIKE concat("%\/\/", :cxn_site_domain , "%")
    //        ');

    $reader = new DbalReader($this->connection, '
        SELECT url, via_port, is_active FROM cxn_member_urls
        WHERE url LIKE concat("%\/\/", :cxn_site_domain , "%")
        ');

    $reader->setSqlParameters(array(
      'cxn_site_domain' => parse_url($params['cxn_site_url'], PHP_URL_HOST),
    ));
    return $reader;
  }

}
