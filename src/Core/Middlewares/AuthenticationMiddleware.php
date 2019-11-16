<?php

namespace Digitalis\core\Middlewares;

use Slim\Container;
use Slim\Http\Body;
use Slim\Http\Request;
use Slim\Http\Response;
use Digitalis\Core\Models\ApiResponse;
use Digitalis\Core\Models\DbAdapters\OperatorDbAdapter;
use Digitalis\Core\Models\DbAdapters\UserDbAdapter;
use Digitalis\Core\Models\SysConst;
use Imediatis\EntityAnnotation\Security\InputValidator;

/**
 * AuthenticationMiddleware Middleware qui perme de vérifier la validité du token
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 */
class AuthenticationMiddleware
{
    /**
     * Conteneur
     *
     * @var Slim\Container
     */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response, $next)
    {
        //
        //AUTHORISATION DES ROUTES PARTICULIERES
        //
        $allowedRouteName = [
            'api.ope.getOperator',
            'api.ope.checkLogin', 
            'api.ope.setLastLogout', 
            'api.ope.setLastLogin', 
            'api.ope.setLastAction',
            'api.ope.getToken',
            'api.ope.lockAccount',
            'api.ope.checkToken',

            'api.usr.getUser',
            'api.usr.checkLogin',
            'api.usr.setLastLogout',
            'api.usr.setLastLogin',
            'api.usr.setLastAction',
            'api.usr.getToken'
        ];
        $this->route = $request->getAttribute('route');
        $name = $this->route->getName();
        if (in_array($name, $allowedRouteName))
            return $next($request, $response);

        //
        //VERIFICATION DU TOKEN D'UN OPERATEUR
        //
        if ($request->hasHeader(SysConst::HTTP_OPE_TOKEN)) {
            $token = trim($request->getHeader(SysConst::HTTP_OPE_TOKEN)[0]);
            $operator = OperatorDbAdapter::getOperatorByToken($token);
            if ($operator && !$operator->tokenExpired()) {
                if (!$operator->tokenExpired())
                    return $next($request, $response);
            }
        }
        //
        //VERIFICATION DU TOKEN D'UN UTILISATEUR
        //
        elseif ($request->hasHeader(SysConst::HTTP_USR_TOKEN)) {
            $token = trim($request->getHeader(SysConst::HTTP_USR_TOKEN)[0]);
            $user = UserDbAdapter::getUserByToken($token);
            if ($user && !$user->tokenExpired()) {
                if (!$user->tokenExpired())
                    return $next($request, $response);
            }
        }

        $output = new ApiResponse();
        $output->status = false;

        $output->message = 'Unauthorized action:token';
        $output->code = 401;
        $body = new Body(fopen('php://temp', 'r+'));
        $body->write(json_encode($output), JSON_PRETTY_PRINT);
        return $response->withStatus(401, 'Unauthorized action')
            ->withHeader('Content-Type', 'applicaiton/json')
            ->withBody($body);
    }
}
