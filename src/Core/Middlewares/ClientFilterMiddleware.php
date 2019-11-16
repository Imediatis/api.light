<?php

namespace Digitalis\Core\Middlewares;

use Slim\Container;
use Slim\Http\Body;
use Slim\Http\Request;
use Slim\Http\Response;
use Digitalis\Core\Models\Data;
use Digitalis\Core\Models\EnvironmentManager;

/**
 * ClientFilterMiddleware Middleware qui permet de filtrer les client qui adresse des requêtes à l'api
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 */
class ClientFilterMiddleware
{
    /**
     * Conteneur
     *
     * @var Slim\Container
     */
    private $container;

    /**
     * client
     *
     * @var \Digitalis\core\Models\Reseller
     */
    private $reseller;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->reseller = $container->reseller;
    }

    /**
     * Détermine que le client qui fait l'appel existe bien dans la liste de client autorisé
     *
     * @return boolean
     */
    private function authorizedClient()
    {
        $clientName = $this->container->clientCaller;
        $ipClient = $this->container->serverIpAddress;
        $file = realpath(EnvironmentManager::getBaseDir()) . DIRECTORY_SEPARATOR . join(DIRECTORY_SEPARATOR, ['repository', 'authorizedclient.json']);
        $authorizedclient = [];
        if (file_exists($file))
            $authorizedclient = json_decode(file_get_contents($file));
        if (count($authorizedclient) == 0)
            return false;
        foreach ($authorizedclient as $client) {
            if ($client->name == $clientName && $client->ip == $ipClient)
                return true;
        }
        return false;
    }

    public function __invoke(Request $request, Response $response, $next)
    {
        if (!$this->authorizedClient()) {
            $body = new Body(fopen('php://temp', 'r+'));
            $body->write("Forbiden");
            $nre = new Response();
            return $nre->withStatus(403, 'Forbiden')
                ->withHeader('Content-Type', 'applicaiton/json')
                ->withBody($body);
        }

        return $next($request, $response);
    }
}
