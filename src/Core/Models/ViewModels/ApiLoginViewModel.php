<?php
namespace Digitalis\Core\Models\ViewModels;

/**
 * LoginViewModel Modèle de connexion des opérateurs
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM <sylvin@imediatis.net> (Back-end Developper)
 */
class LoginViewModel
{
	/**
	 * Login de l'opérateur
	 *
	 * @IME\Required{"errMsg":"Ce champ est obligatoire"}
	 * @IME\Length{"max":20,"errMsg":"Ce champ n'admet pas plus de 20 caractères"}
	 * @var string
	 */
	public $login;

	/**
	 * mot de passe de l'opérateur
	 *
	 * @IME\Required{"errMsg":"Ce champ est obligatoire"}
	 * @IME\Length{"max":15,"errMsg":"Ce champ n'admet pas plus de 15 caractères"}
	 * @var string
	 */
	public $pwd;

	/**
	 * Code de la caisse à laquelle l'opérateur se connecte
	 *
	 * @IME\Required{"errMsg":"Ce champ est obligatoire"}
	 * @IME\Length{"max":12,"errMsg":"Ce champ n'admet pas plus de 12 caractères"}
	 * @var string
	 */
	public $boxKey;
}