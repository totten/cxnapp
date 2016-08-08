<?php
namespace Civi\MemberStatusBundle\Reader;

use Ddeboer\DataImport\Reader\ArrayReader;
use Ddeboer\DataImport\Reader\ReaderInterface;

class WebServiceReader implements ReaderFactoryInterface {

  /**
   * @var string
   */
  protected $token;

  /**
   * @var string
   */
  protected $url;

  /**
   * @var \Symfony\Component\BrowserKit\Client
   */
  protected $http;

  /**
   * WebServiceReader constructor.
   */
  public function __construct() {
    $this->http = new \Goutte\Client();
  }

  /**
   * @param array $params
   * @return ReaderInterface
   */
  public function createReader($params) {
    $this->http->request('POST', $this->url, $params);
    // FIXME: $this->token

    if ($this->http->getInternalResponse()->getStatus() === 200) {
      $httpBody = $this->http->getInternalResponse()->getContent();
    }
    else {
      // FIXME log error
      $httpBody = '[]';
    }

    return new ArrayReader(json_decode($httpBody, TRUE));
  }

  /**
   * @return \Symfony\Component\BrowserKit\Client
   */
  public function getHttp() {
    return $this->http;
  }

  /**
   * @param \Symfony\Component\BrowserKit\Client $http
   * @return $this
   */
  public function setHttp($http) {
    $this->http = $http;
    return $this;
  }

  /**
   * @return string
   */
  public function getToken() {
    return $this->token;
  }

  /**
   * @param string $token
   * @return $this
   */
  public function setToken($token) {
    $this->token = $token;
    return $this;
  }

  /**
   * @return string
   */
  public function getUrl() {
    return $this->url;
  }

  /**
   * @param string $url
   * @return $this
   */
  public function setUrl($url) {
    $this->url = $url;
    return $this;
  }


}