<?php
/**
 * This file is part of the 'Docalist Biblio Export' plugin.
 *
 * Copyright (C) 2012-2015 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist\Biblio\Export
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     $Id: BaseExport.php 1915 2015-01-05 10:23:28Z daniel.menard.35@gmail.com $
 */
namespace Docalist\Biblio\Export;

use Docalist\Forms\Fragment;

/**
 * Classe de base pour les convertisseurs et les exporteurs.
 *
 * Gère la configuration des objets d'export.
 *
 * Principe :
 *
 * Un "format d'export" est composé :
 * - d'un objet Converter qui se charge de convertir une notice docalist vers
 *   un autre format de notice (prisme, unimarc, marc21...)
 * - d'un objet Exporter qui se charge d'écrire les données converties dans
 *   un format de fichier donné (json, csv, ajp, iso2709...)
 *
 * Chacun de ces deux objets peut avoir des paramètres. Par exemple, l'exporteur
 * json a un paramètre pretty qui indique si on veut générer un fichier indenté
 * ou non et le convertisseur prisme2014 a un paramètre qui indique si on veut
 * générer des mots-clés en majuscules ou non.
 *
 * Un format d'export est ainsi définit par un ensemble de paramétres.
 *
 * Exemple :
 *   // Chaque format d'export doit avoir un identifiant unique
 *   $formats['prisme-uppercase-json-pretty'] = [
 *
 *       // Libellé utilisé pour désigner ce format
 *       'label' => 'Prisme - JSON',
 *
 *       // Description libre de ce que fait le format
 *       'description' => 'Notices Prisme, fichier JSON.',
 *
 *       // Nom de classe complet du convertisseur à utiliser
 *       'converter' => 'Prisme\Export\Prisme2014',
 *
 *       // Nom de classe complet de l'exporteur à utiliser
 *       'exporter' => 'Docalist\Biblio\Export\Json',
 *
 *       // Paramétres éventuels du convertisseur
 *       'converter-settings' => [
 *           'uppercase-topics' => true,
 *       ],
 *
 *       // Paramètres éventuels de l'exporteur
 *       'exporter-settings' => [
 *           'pretty' => false,
 *       ],
 *   ];
 *
 * Pour le moment, les formats d'export disponibles sont définis en répondant
 * au filtre wordpress 'docalist_biblio_get_exporters' :
 *
 *   add_filter('docalist_biblio_get_exporters', function(array $exporters) {
 *       $exporters['mon-format] = [ ... ];
 *       return $erporters;
 *   });
 *
 * Plus tard, il sera possible de créer un nouvel exporteur directement depuis
 * le back-office via des formulaires permettant de saisir les différents
 * paramètres.
 *
 * Pour cela, les exporteurs et les convertisseurs disposent d'une méthode
 * settingsForm() qui retourne le formulaire à utiliser.
 *
 * Lorsque l'utilisateur valide, la méthode validateSettings() est appelée et
 * les paramètres obtenus sont enregistrés.
 *
 * Par la suite, lorsqu'un exporteur ou un convertisseur est créé,
 * docalist-biblio lui fournit les paramètres enregistrés et il peut accéder
 * à ses paramètres en utilisant la méthode get() qui permet de récupérer une
 * option de configuration.
 */
abstract class BaseExport {
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
     * Initialise l'objet
     *
     * @param array $settings Les paramètres de l'objet.
     */
    public function __construct(array $settings = []) {
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
    public static function defaultSettings() {
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
    public function settings() {
        return $this->settings;
    }

    /**
     * Retourne un paramètre de configuration.
     *
     * @param string $setting Nom du paramètre.
     * @param mixed $default Valeur par défaut retournée si le paramètre
     * indiqué n'existe pas.
     *
     * @return mixed
     */
    public function get($setting, $default = null) {
        if (array_key_exists($setting, $this->settings)) {
            return $this->settings[$setting];
        }

        return $default;
    }

    /**
     * Retourne le formulaire de paramètrage de l'objet.
     *
     * @return Fragment
     */
    public static function settingsForm() {
        return null;
    }

    /**
     * Valide les settings de l'objet.
     *
     * Cette méthode est appellée quand le formulaire retourné par
     * settingsForm() est soumis par l'utilisateur. Les données transmises
     * correspondent au tableau $_POST contenant les données de la requête.
     *
     * La méthode doit extraire et valider les settings et retourner le
     * tableau obtenu.
     *
     * @param array $settings Les données brutes ($_POST).
     *
     * @return array Les settings validés.
     */
    public static function validateSettings(array $settings) {
        return $settings;
    }

    public static function label() {
        return strtolower(substr(strrchr(get_called_class(), '\\'), 1));
    }

    public static function description() {
        return 'description';
    }

    public static function id() {
        return strtolower(strtr(get_called_class(), '\\', '-'));
    }
}