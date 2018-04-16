<?php

namespace Drupal\jsonrpc\Encoder;

use Drupal\serialization\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\JsonDecode;

class JsonRpcEncoder extends JsonEncoder {

  /**
   * The formats that this Encoder supports.
   *
   * @var array
   */
  protected static $format = ['rpc_json'];

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    parent::__construct(NULL, new JsonDecode(FALSE));
  }

}