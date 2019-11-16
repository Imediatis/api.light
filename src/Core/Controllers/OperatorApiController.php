<?php
namespace Digitalis\Core\Controllers;

use DateTime;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use Digitalis\Core\Models\Data;
use Digitalis\Core\Models\Lexique;
use Digitalis\Core\Models\SysConst;
use Digitalis\Core\Models\ApiResponse;
use Digitalis\Core\Handlers\ErrorHandler;
use Digitalis\Core\Models\DataBase\DBase;
use Digitalis\Core\Models\SessionManager;
use Digitalis\Core\Controllers\ApiController;
use Digitalis\Core\Models\DbAdapters\OperatorDbAdapter;
use Imediatis\EntityAnnotation\Security\InputValidator;

/**
 * AccountApiController Description of AccountApiController here
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM <sylvin@imediatis.net> (Back-end Developper)
 */
class OperatorApiController extends ApiController
{
	public function __construct(Container $container)
	{
		parent::__construct($container);
		parent::setCurrentController(__class__);
	}

	public function setLastLogout(Request $request, Response $response)
	{
		$output = new ApiResponse();
		$output->data = false;
		$login = $request->getAttribute('login');
		$login = base64_decode($login);
		if (Data::isDecodeString($login)) {
			$output->data = OperatorDbAdapter::setLastLogout($login);
		}

		return $this->render($response, $output);
	}

	public function setLastLogin(Request $request, Response $response)
	{
		$output = new ApiResponse();
		$output->data = false;
		$login = $request->getAttribute('login');
		if ($login) {
			$login = base64_decode($login);
			if (Data::isDecodeString($login)) {
				$output->data = OperatorDbAdapter::setLastLogin($login);
			}
		} else {
			$output->message = "Impossible de traiter votre requete";
		}

		return $this->render($response, $output);
	}

	public function setLastAction(Request $request, Response $response)
	{
		$output = new ApiResponse();
		$output->data = false;
		$login = $request->getAttribute('login');
		if ($login) {
			$login = base64_decode($login);
			if (Data::isDecodeString($login)) {
				$operator = OperatorDbAdapter::getByLogin($login);
				if ($operator) {
					$operator->setLastAction(new \DateTime());
					DBase::getEntityManager()->flush();
					$output->data = true;
				}
			}
		} else {
			$output->message = "Impossible de traiter votre requete";
		}

		return $this->render($response, $output);
	}

	public function getOperator(Request $request, Response $response)
	{
		$output = new ApiResponse();
		$output->data = null;
		$login = $request->getAttribute('login');
		if ($login) {
			$login = base64_decode($login);
			if (Data::isDecodeString($login)) {
				$operator = OperatorDbAdapter::getByLogin($login);
				if ($operator) {
					$output->data = $operator->toArray();
					$output->found = true;
				} else {
					$output->message = is_null($operator)? data_unavailable: Data::getErrorMessage();
				}
			}
		} else {
			$output->message = "Impossible de traiter votre requete";
		}

		return $this->render($response, $output);
	}

	public function lockAccount(Request $request, Response $response)
	{
		$output = new ApiResponse();
		$output->data = null;
		$login = $request->getAttribute('login');
		if($login){
			$login = base64_decode($login);
			$output->data = OperatorDbAdapter::deactivateUser($login);
			$output->saved =true;
		}
		return $this->render($response,$output);
	}

	public function genToken(Request $request, Response $response)
	{
		$output = new ApiResponse();
		$login = base64_decode($request->getAttribute('login'));
		$reponse  = OperatorDbAdapter::genToken($login);
		$output->data = !is_null($reponse)?$reponse->toArray():null;
		$output->found = !is_null($output->data);
		$output->message = is_null($output->data)?data_unavailable:Data::getErrorMessage();
		
		return $this->render($response,$output);
	}

	
	public function checkToken(Request $request, Response $response)
	{
		$output = new ApiResponse();
		$token = $request->getAttribute('token');
		$operator = OperatorDbAdapter::getOperatorByToken($token);
		if($operator && !$operator->tokenExpired()){
			OperatorDbAdapter::extendTokenLife($token);
			$output->data = $operator->toLoggedUser();
			$output->found = true;
		}
		
		$output->message = Data::getErrorMessage();
		return $this->render($response,$output);
	}

	public function changePwd(Request $request, Response $response)
	{
		$output = new ApiResponse();
		InputValidator::InitSlimRequest($request);
		try {
			$login = base64_decode($request->getAttribute('login'));
			$plogin = base64_decode(InputValidator::getString('login'));
			$pwd = base64_decode(InputValidator::getString('currentPwd'));
			$newPwd = base64_decode(InputValidator::getString('newPwd'));
			if (strcmp($login, $plogin) == 0 && Data::isDecodeString($plogin) && Data::isDecodeString($login)) {
				$operator = OperatorDbAdapter::getByLogin($login);
				if ($operator) {
					if (password_verify($pwd, $operator->getPassword())) {
						$operator->setPassword(Data::cryptPwd($newPwd));
						$operator->setStatus(1);
						$operator->setLastLogin(new \DateTime());
						$date = new \DateTime();
						$operator->setLastLogout($date->add(new \DateInterval('PT30S')));
						$ip = SessionManager::get(SysConst::ORIGINAL_CLIENT_IP);
						$operator->setLastIpLogin($ip);
						$operator->setLastIpLogout($ip);
						$operator->setToken(null);
						$operator->setTokenExpireDate(null);
						DBase::getEntityManager()->flush();
						$output->saved = true;
						$output->data = true;
					} else {
						$output->data = false;
						$output->message = "Invalide username or password: operateur non trouve";
					}
				} else {
					$output->data = false;
					$output->message = "Invalide username or password: operateur non trouve";
				}
			} else {
				$output->message = "Invalide username or password: login non correspondant";
				$output->data = false;
			}
		} catch (\Exception $exc) {
			Data::setErrorMessage(an_error_occured);
			ErrorHandler::writeLog($exc);
			$output->message = an_error_occured;
		}
		return $this->render($response, $output);
	}
}