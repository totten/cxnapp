<?php
namespace Civi\MemberStatusBundle\Reader;

use Ddeboer\DataImport\Reader\ReaderInterface;

interface ReaderFactoryInterface {

  /**
   * @param array $params
   * @return ReaderInterface
   */
  public function createReader($params);

}
