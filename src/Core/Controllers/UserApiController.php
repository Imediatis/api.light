<?php

namespace Digitalis\Core\Controllers;

use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use Digitalis\Core\Models\Data;
use Digitalis\Core\Models\Lexique;
use Digitalis\Core\Models\SysConst;
use Digitalis\core\Models\ApiResponse;
use Digitalis\Core\Models\DataBase\DBase;
use Digitalis\Core\Models\SessionManager;
use Digitalis\core\Controllers\ApiController;
use Digitalis\Core\Models\DbAdapters\UserDbAdapter;

/**
 * UserApiController Description of UserApiController here
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 */
class UserApiController extends ApiController
{
    public function __construct(Container $container)
    {
        parent::__construct($container);
        parent::setCurrentController(__class__);
    }

    public function getUser(Request $request, Response $response)
    {
        $output = new ApiResponse();
        $output->data = null;
        $login = $request->getAttribute('login');
        if ($login) {
            $login = base64_decode($login);
            if (Data::isDecodeString($login)) {
                $user = UserDbAdapter::getByLogin($login);
                if ($user) {
                    $output->data = $user->toArray();
                    $output->found = true;
                } else {
                    $output->message = is_null($user) ? data_unavailable : Data::getErrorMessage();
                }
            }
        } else {
            $output->message = data_unavailable;
        }

        return $this->render($response, $output);
    }

    
    public function lockAccount(Request $request, Response $response)
    {
        $output = new ApiResponse();
        $output->data = null;
        $login = $request->getAttribute('login');
        if ($login) {
            $login = base64_decode($login);
            $output->data = UserDbAdapter::deactivateUser($login);
            $output->saved = true;
        }
        return $this->render($response, $output);
    }

    public function genToken(Request $request, Response $response)
    {
        $output = new ApiResponse();
        $login = base64_decode($request->getAttribute('login'));
        if(Data::isDecodeString($login)){
            $reponse  = UserDbAdapter::genToken($login);
            $output->data = !is_null($reponse) ? $reponse->toArray() : null;
            $output->found = !is_null($output->data);
        }
        $output->message = is_null($output->data) ? data_unavailable : Data::getErrorMessage();

        return $this->render($response, $output);
    }

    public function setLastLogout(Request $request, Response $response)
    {
        $output = new ApiResponse();
        $output->data = false;
        $login = $request->getAttribute('login');
        $login = base64_decode($login);
        if (Data::isDecodeString($login)) {
            $output->data = UserDbAdapter::setLastLogout($login);
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
                $output->data = UserDbAdapter::setLastLogin($login);
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
                $user = UserDbAdapter::getByLogin($login);
                if ($user) {
                    $user->setLastAction(new \DateTime());
                    DBase::getEntityManager()->flush();
                    $output->data = true;
                }
            }
        } else {
            $output->message = "Impossible de traiter votre requete";
        }

        return $this->render($response, $output);
    }
}
