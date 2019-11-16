<?php

use Digitalis\Core\Models\Data;
use Digitalis\Core\Models\EnvironmentManager as EnvMngr;
use Digitalis\Core\Models\MailWorker;
use Digitalis\Core\Models\Reseller;
use Digitalis\Core\Models\SessionManager;
use Digitalis\Core\Models\SysConst;
use Digitalis\Core\Models\DbAdapters\JsonClientDbAdapter;

//$app = new Slim\App();

$container = $app->getContainer();

SessionManager::set(SysConst::T_CORE_SHARE_VIEW_F, SysConst::CORE_SHARED_VIEW_F);

$container['debug'] = function () {
    return EnvMngr::isDebug();
};

$container['baseUrl'] = function ($c) {
    return $c->request->getUri()->getScheme() . '://' . $c->request->getUri()->getHost() . (!is_null($c->request->getUri()->getPort()) ? ':' . $c->request->getUri()->getPort() : '') . '/';
};

$container['baseDir'] = function () {
    return realpath(__DIR__ . join(DIRECTORY_SEPARATOR, [DIRECTORY_SEPARATOR, '..'])) . DIRECTORY_SEPARATOR;
};

$container['reseller'] = function ($c) {
    $sreseller =  SessionManager::getReseller();
    $reseller = !is_null($sreseller) ? $sreseller : new Reseller(EnvMngr::getResellerFile());
    return $reseller;
};

//Récupère l'adresse ip du serveur qui demande une requette.
//cette adresse sera utilisé pour implémenter le middleware de filtrage des @ip des serveurs
$container['serverIpAddress'] = function ($c) {
    return MailWorker::getIpAddress();
};

//Récupère l'adresse du client ayant fait appel au serveur ci-dessus (client ayant appelé front)
//Cette adresse est également transmise à api.light via le header : original-client-ip
$container['ipAddress'] = function ($c) {
    $ip =  $c->request->getHeader(SysConst::HTTP_ORIGINAL_CLIENT_IP);
    $clientIp = is_array($ip) && count($ip) > 0 ? $ip[0] : $c->serverIpAddress;
    SessionManager::set(SysConst::ORIGINAL_CLIENT_IP, $clientIp);
    return $clientIp;
};

$container['clientManager'] = function ($c) {
    return new JsonClientDbAdapter();
};

$container['clientCaller'] = function ($c) {
    $client = $c->request->getHeader(SysConst::HTTP_CLIENT_CALLER);
    if(is_array($client) && count($client)>=1)
        return strtolower($client[0]);
    return null;
};

//
//RECUPERATION ET SAUVEGARDE EN SESSION DU SYSTEME D'EXPLOITATION DU CLIENT
//
SessionManager::set(SysConst::CLIENT_OS, Data::cgetOS($container->request->getServerParam("HTTP_USER_AGENT")));
//
//RECUPERATION DE LA ROUTE DEMANDE
//
SessionManager::set(SysConst::R_ROUTE, $container->request->getUri()->getPath());
