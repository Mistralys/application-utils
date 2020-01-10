<?php
/**
 * File containing the {@link RequestHelper_Exception} class.
 * @package Application Utils
 * @subpackage RequestHelper
 * @see RequestHelper_Exception
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Request helper exception class: all exceptions in the
 * request helper are of this type. If available, this
 * allows accessing the request response if the error 
 * occurred in the context of a sent request. 
 *
 * @package Application Utils
 * @subpackage RequestHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class RequestHelper_Exception extends BaseException
{
   /**
    * @var RequestHelper_Response|NULL
    */
    protected $response = null;
 
    public function setResponse(RequestHelper_Response $response)
    {
        $this->response = $response;
    }
    
   /**
    * Retrieves the related response instance, if available.
    * 
    * @return RequestHelper_Response|NULL
    */
    public function getResponse() : ?RequestHelper_Response
    {
        return $this->response;
    }
}
