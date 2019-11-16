<?php

use Slim\App;
use Digitalis\Core\Controllers\UserApiController;
use Digitalis\Core\Controllers\OperatorApiController;
use Digitalis\Core\Middlewares\BranchBoxLogMiddleware;
use Digitalis\Core\Middlewares\ClientFilterMiddleware;
use Digitalis\Core\Controllers\OperationsApiController;
use Digitalis\Core\Middlewares\ApiTokenValidationMiddleware;
use Digitalis\core\Middlewares\AuthenticationMiddleware;

//$app = new \Slim\App();

$c = $app->getContainer();

//
//ROUTES DE L'API
//
$app->group('', function (App $app) {
	$app->get('/operators/checklogin/{token}', OperatorApiController::class.':checkToken' )->setName('api.ope.checkToken');
	$app->group('/operators/{login}', function (App $app) {
		$app->get('', OperatorApiController::class . ':getOperator')->setName('api.ope.getOperator');
		$app->put('/lastlogout', OperatorApiController::class . ':setLastLogout')->setName('api.ope.setLastLogout');
		$app->put('/lastlogin', OperatorApiController::class . ':setLastLogin')->setName('api.ope.setLastLogin');
		$app->put('/lastaction', OperatorApiController::class . ':setLastAction')->setName('api.ope.setLastAction');
		$app->put('/token', OperatorApiController::class . ':genToken')->setName('api.ope.getToken');
		$app->put('/lockaccount',OperatorApiController::class.':lockAccount')->setName('api.ope.lockAccount');
		$app->put('/changepwd', OperatorApiController::class . ':changePwd')->setName('api.ope.ChangePwd');
	});

	$app->group('/users/{login}', function (App $app) {
		$app->get('', UserApiController::class . ':getUser')->setName('api.usr.getUser');
		$app->put('/lastlogout', UserApiController::class . ':setLastLogout')->setName('api.usr.setLastLogout');
		$app->put('/lastlogin', UserApiController::class . ':setLastLogin')->setName('api.usr.setLastLogin');
		$app->put('/lastaction', UserApiController::class . ':setLastAction')->setName('api.usr.setLastAction');
		$app->put('/token', UserApiController::class . ':genToken')->setName('api.usr.getToken');
		$app->put('/lockaccount', UserApiController::class . ':lockAccount')->setName('api.usr.lockAccount');

		$app->put('/changepwd', UserApiController::class . ':changePwd')->setName('api.usr.ChangePwd');
	});

	$app->group('/boxes', function (App $app) {
		$app->get('[/{boxCode}]', OperationsApiController::class . ':getBoxes')->setName("api-getboxes");
		$app->put('/{boxCode}/boxopenclose', OperationsApiController::class . ':openCloseBox')->setName('api.boxOpenClose')->add(new BranchBoxLogMiddleware($app->getContainer()));
	});

	$app->group('/branches', function (App $app) {
		$app->get('[/{branchCode}]', OperationsApiController::class . ':getBranches')->setName("api-getboxes");
		$app->put('/{branchCode}/branchopenclose', OperationsApiController::class . ':openCloseBranch')->setName('api.branchOpenClose')->add(new BranchBoxLogMiddleware($app->getContainer()));
	});

	$app->get('/partners/{branchCode}', OperationsApiController::class . ':getPartners')->setName('api.getPartners');
	$app->get('/typepieces', OperationsApiController::class . ':getTypePieces')->setName('api.getTpiece');
	$app->get('/clients/{numCpt}/{partner}', OperationsApiController::class . ':getClient')->setName('api.getClient');
})->add(new ApiTokenValidationMiddleware($c))
	->add(new ClientFilterMiddleware($c))
	->add(new AuthenticationMiddleware($c));
