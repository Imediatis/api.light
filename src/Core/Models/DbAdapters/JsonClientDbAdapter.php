<?php
namespace Digitalis\Core\Models\DbAdapters;

use Digitalis\Core\Models\Client;
use Digitalis\Core\Handlers\ErrorHandler;
use Digitalis\Core\Models\EnvironmentManager;
use Digitalis\Core\Models\Interfaces\IClientManager;
use Digitalis\Core\Models\Data;

/**
 * JsonClientDbAdapter Gestionnaire de client dans les fichiers JSON
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 */
class JsonClientDbAdapter implements IClientManager
{
    const rootFileName = "fakeclients-%s.json";
    /**
     * Permet de charger les clients du fichier json d'une certaine Banque
     *
     * @param string $partner
     * @return Client[]
     */
    public function loadClients($partner)
    {
        $output = [];
        $tempFolder = EnvironmentManager::getTempFolder();
        $filename = $tempFolder . sprintf(self::rootFileName, strtolower($partner));
        try {
            if (file_exists($filename)) {
                $content = file_get_contents($filename);
                $tcontent = json_decode($content, true);
                foreach ($tcontent as $value) {
                    $client = Client::buildInstance($value);
                    if ($client) {
                        $output[$client->getNumCpt()] = $client;
                    }
                }
            } else {
                Data::setErrorMessage("File not found " . $filename);
            }
        } catch (\Exception $exc) {
            ErrorHandler::writeLog($exc);
            Data::setErrorMessage($exc->getMessage());
        }
        return $output;
    }

    /**
     * Permet de récupérer un client en fonction des paramètres passé
     *
     * @param string $partner code du partenaire
     * @param string $accountNumber Numéro de compte du client
     * @return Client
     */
    public function getClient($partner, $accountNumber)
    {
        $clients = $this->loadClients($partner);
        if (count($clients) > 0 && isset($clients[$accountNumber])) {
            return $clients[$accountNumber];
        }
        return null;
    }
}