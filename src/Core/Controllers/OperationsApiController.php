<?php
namespace Digitalis\Core\Controllers;

use Digitalis\Core\Controllers\ApiController;
use Digitalis\Core\Models\ApiResponse;
use Digitalis\Core\Models\Data;
use Digitalis\Core\Models\DataBase\DBase;
use Digitalis\Core\Models\DbAdapters\AgenceDbAdapter;
use Digitalis\Core\Models\DbAdapters\CaisseDbAdapter;
use Digitalis\Core\Models\DbAdapters\OperatorDbAdapter;
use Digitalis\Core\Models\DbAdapters\TypePieceDbAdapter;
use Digitalis\Core\Models\Lexique;
use Imediatis\EntityAnnotation\Security\InputValidator;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * OperationsApiController Description of OperationsApiController here
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM <sylvin@imediatis.net> (Back-end Developper)
 */
class OperationsApiController extends ApiController
{
	/**
	 * Gestionnaire des clients
	 *
	 * @var \Digitalis\Core\Models\Interfaces\IClientManager
	 */
	private $clientManager;

	public function __construct(Container $container)
	{
		parent::__construct($container);
		parent::setCurrentController(__class__);
		$this->clientManager = $this->container->get(clientManager);
	}

	public function openCloseBox(Request $request, Response $response)
	{
		$output = new ApiResponse();
		InputValidator::InitSlimRequest($request);
		$boxCode = base64_decode($request->getAttribute('boxCode'));
		$status = InputValidator::getInt('status');
		$login = base64_decode(InputValidator::getString('login'));

		if (Data::isDecodeString($boxCode) && !is_null($status) && Data::isDecodeString($login)) {
			try {
				$caisse = CaisseDbAdapter::getByCode($boxCode);
				if ($caisse && $caisse->getOperator()) {
					if (strcmp($caisse->getOperator()->getLogin(), $login) == 0) {
						if ($status) {
							if ($caisse->getAgence()->getIsOpened()) {
								$caisse->setIsOpened(true);
								DBase::getEntityManager()->flush();
								$output->updated = true;
								$output->data = $caisse->getOperator()->toLoggedUser();
								$output->message = 'box-successfully-opened-';
							} else {
								$output->message = "unable-to-open-the-box-wh";
							}
						} else {
							$caisse->setIsOpened(false);
							DBase::getEntityManager()->flush();
							$output->updated = true;
							$output->data = $caisse->getOperator()->toLoggedUser();
							$output->message = 'box-successfully-closed-';
						}
					} else {
						$output->message = 'you-are-not-affected-to-t';
					}
				} else {
					$output->message = $status ? "unable-to-open-the-box" : "unable-to-close-the-box";
				}
			} catch (\Exception $exc) {
				Data::setErrorMessage(an_error_occured);
				ErrorHandler::writeLog($exc);
				$output->message = an_error_occured;
			}
		} else {
			$output->message = $status ? "unable-to-open-the-box" : "unable-to-close-the-box";
		}
		return $this->render($response, $output);
	}

	public function openCloseBranch(Request $request, Response $response)
	{
		$output = new ApiResponse();
		InputValidator::InitSlimRequest($request);
		$branchCode = base64_decode($request->getAttribute('branchCode'));
		$status = InputValidator::getInt('status');
		$login = base64_decode(InputValidator::getString('login'));

		if (Data::isDecodeString($branchCode) && !is_null($status) && Data::isDecodeString($login)) {
			try {
				$agence = AgenceDbAdapter::getByCode($branchCode);
				if ($agence) {
					if (AgenceDbAdapter::operatorWorkWithThisBranch($branchCode, $login)) {
						$operateur = OperatorDbAdapter::getByLogin($login);
						if ($status) {
							$agence->setIsOpened(true);
							DBase::getEntityManager()->flush();
							$output->updated = true;
							$output->data = !is_null($operateur) ? $operateur->toLoggedUser() : null;
							$output->message = 'agency-successfully-opene';
						} else {
							if (!AgenceDbAdapter::hasOpenedBoxes($branchCode)) {
								$agence->setIsOpened(false);
								DBase::getEntityManager()->flush();
								$output->updated = true;
								$output->data = !is_null($operateur) ? $operateur->toLoggedUser() : null;
								$output->message = "agency-successfully-close";
							} else {
								$output->message = 'impossible-to-close-the-b';
							}
						}
					} else {
						$output->message = 'you-are-not-affected-to-branch';
					}
				} else {
					$output->message = $status ? "unable-to-open-the-box" : "unable-to-close-the-box";
				}
			} catch (\Exception $exc) {
				Data::setErrorMessage(an_error_occured);
				ErrorHandler::writeLog($exc);
				$output->message = an_error_occured;
			}
		} else {
			$output->message = $status ? "unable-to-open-the-box" : "unable-to-close-the-box";
		}
		return $this->render($response, $output);
	}

	public function getPartners(Request $request, Response $response)
	{
		$output = new ApiResponse();
		$branchcode =  base64_decode($request->getAttribute('branchCode'));
		if (Data::isDecodeString($branchcode)) {
			$partners = AgenceDbAdapter::getPartners($branchcode);
			$array = [];
			foreach ($partners as  $value) {
				$array[$value->getCode()] = $value->getName();
			}
			$output->found = true;
			$output->data = $array;
		}
		return $this->render($response, $output);
	}

	public function getTypePieces(Request $request, Response $response)
	{
		$output = new ApiResponse();
		$output->found = true;
		$output->data = TypePieceDbAdapter::getAssoc();

		return $this->render($response, $output);
	}


	public function getClient(Request $request, Response $response)
	{
		$output = new ApiResponse();
		$output->found = false;
		$partner = $request->getAttribute('partner');
		$numCpt = $request->getAttribute('numCpt');
		$client = $this->clientManager->getClient($partner, $numCpt);
		$output->data = $client;
		$output->found = !is_null($client);
		$output->message = Data::getErrorMessage();

		return $this->render($response, $output);
	}
}