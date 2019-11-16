<?php
namespace Digitalis\Core\Controllers;

use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use Digitalis\core\Controllers\ApiController;
use Digitalis\core\Models\ApiResponse;

/**
 * BoxApiController Description of BoxApiController here
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 */
class BoxApiController extends ApiController
{
    public function __construct(Container $container)
    {
        parent::__construct($container);
        parent::setCurrentController(__class__);
    }

    public function index(Request $request, Response $response)
    {
        $output = new ApiResponse();
        //Code here...

        return $this->render($response,$output);
    }
}