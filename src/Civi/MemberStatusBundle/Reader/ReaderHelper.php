<?php
namespace Civi\MemberStatusBundle\Reader;

use Ddeboer\DataImport\Reader\ReaderInterface;

class ReaderHelper {

  /**
   * Format $obj as a reader.
   *
   * @param ReaderInterface|ReaderFactoryInterface $obj
   * @return ReaderInterface
   */
  public static function toReader($obj, $params = NULL) {
    if ($obj instanceof ReaderInterface) {
      $reader = $obj;
    }
    elseif ($obj instanceof ReaderFactoryInterface) {
      $reader = $obj->createReader($params);
    }
    else {
      $class = $obj ? get_class($obj) : 'NULL';
      throw new \RuntimeException("toReader() expects ReaderInterface or ReaderFactoryInterface. Found $class.");
    }

    if ($reader instanceof \Civi\MemberStatusBundle\Reader\ParameterizedReaderInterface) {
      $reader->setParameters($params);
      return TRUE;
    }
    elseif ($reader instanceof \Ddeboer\DataImport\Reader\DbalReader) {
      $reader->setSqlParameters($params);
      return TRUE;
    }

    return $reader;
  }

}
