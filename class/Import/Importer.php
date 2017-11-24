<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist\Biblio\Export
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 */
namespace Docalist\Biblio\Import;

use Docalist\Biblio\Database;

/**
 * Classe de base pour les importeurs.
 *
 */
abstract class Importer
{
    /**
     * Les paramètres par défaut pour ce type d'objet.
     *
     * Chaque classe peut surcharger cette propriété. La méthode
     * defaultSettings() se charge de fusionner les paramètres avec ceux des
     * classes ascendantes.
     *
     * @var array
     */
    protected static $defaultSettings = [];

    /**
     * Les paramètres actuels de l'objet.
     *
     * @var array
     */
    protected $settings;

    /**
     * Initialise l'objet.
     *
     * @param array $settings Les paramètres de l'objet.
     */
    public function __construct(array $settings = [])
    {
        $this->settings = array_merge(static::defaultSettings(), $settings);
    }

    /**
     * Retourne les paramétres par défaut de ce type d'objet.
     *
     * Les paramètres par défaut son obtenus en fusionnant les paramètres
     * indiqués dans la propriété statique $defaultSettings avec les paramètres
     * par défaut de la classe parent (i.e. configuration en cascade).
     *
     * @return array
     */
    public static function defaultSettings()
    {
        $parent = get_parent_class(get_called_class());

        if ($parent === false) {
            return self::$defaultSettings;
        }

        return array_merge($parent::defaultSettings(), static::$defaultSettings);
    }

    /**
     * Retourne les paramètres de configuration de l'objet.
     *
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Retourne un paramètre de configuration.
     *
     * @param string    $name       Nom du paramètre.
     * @param mixed     $default    Valeur par défaut à retourner si le paramètre demandé n'existe pas.
     *
     * @return mixed
     */
    public function getSetting($name, $default = null)
    {
        if (array_key_exists($name, $this->settings)) {
            return $this->settings[$name];
        }

        return $default;
    }

    /**
     * Retourne le formulaire de paramètrage de l'objet.
     *
     * @return Fragment
     */
    public function getSettingsForm()
    {
        return null;
    }

    /**
     * Valide les paramètres de l'objet.
     *
     * Cette méthode est appellée quand le formulaire retourné par getSettingsForm() est soumis par l'utilisateur.
     * Les données transmises correspondent au tableau $_POST contenant les données de la requête.
     *
     * La méthode doit extraire et valider les settings et retourner le tableau obtenu.
     *
     * @param array $settings Les paramètres à valider.
     *
     * @return array Les paramètres validés.
     */
    public function validateSettings(array $settings)
    {
        return $settings;
    }

    /**
     * Retourne l'identifiant unique de l'objet.
     *
     * L'identifiant est généré à partir du nom complet de la classe PHP. Les antislashs sont remplacés par un tiret
     * et l'ensemble est converti en minuscules. Exemple : retourne "docalist-biblio-export-xml" pour la classe
     * "Docalist\Biblio\Export\Xml".
     *
     * Remarque : Le résultat obtenu est utilisable comme nom de classe CSS ou comme id dans du code html.
     *
     * @return string
     */
    final public function getID()
    {
        return strtolower(strtr(get_class($this), '\\', '-'));
    }

    /**
     * Retourne le nom usuel utilisé pour identifier l'objet.
     *
     * Par défaut, le nom usuel correspond à la version en minuscules du nom court (sans namespace) de la classe PHP.
     * Par Exemple : retourne "xml" pour la classe "Docalist\Biblio\Export\Xml".
     *
     * Les classes descendantes peuvent surcharger la méthode pour retourner un nom plus approprié.
     *
     * @return string
     */
    public function getName()
    {
        return strtolower(substr(strrchr(get_class($this), '\\'), 1));
    }

    /**
     * Retourne le libellé de l'objet.
     *
     * Par défaut, la méthode retourne simplement le nom de l'objet.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->getName();
    }

    /**
     * Retourne la description de l'objet.
     *
     * @return string
     */
    public function getDescription()
    {
        return '';
    }

    protected function report($message, ...$args)
    {
        $message = sprintf($message, ...$args);
        do_action('docalist_biblio_import_progress', $message);
    }

    protected function reportWarning($message, ...$args)
    {
        $this->report('<span style="color:orange">' . $message . '</span>', ...$args);
    }

    protected function reportError($message, ...$args)
    {
        $this->report('<span style="color:red">' . $message . '</span>', ...$args);
    }

    abstract public function import($filename, Database $database, array $options = []);
}
