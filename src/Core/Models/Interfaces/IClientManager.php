<?php
namespace Digitalis\Core\Models\Interfaces;

use Digitalis\Core\Models\Client;

/**
 * IClientManager Interface de gestion des clients
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 */
interface IClientManager
{
    /**
     * Permet de récupérer un client en fonction des paramètres passé
     *
     * @param string $partner code du partenaire
     * @param string $accountNumber Numéro de compte du client
     * @return Client
     */
    public function getClient($partner, $accountNumber);

    /**
     * Permet de charger les clients du fichier json d'une certaine Banque
     *
     * @param string $partner
     * @return Client[]
     */
    public function loadClients($partner);
}