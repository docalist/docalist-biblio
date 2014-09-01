<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2014 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     $Id$
 */
namespace Docalist\Biblio\Export;

use Docalist\Biblio\Reference\ReferenceIterator;
use Docalist\Forms\Fragment;

/**
 * Classe de base pour les exporteurs.
 *
 * Principe :
 * - Chaque exporteur est un objet qui sait exporter des notices dans un
 *   format ou une famille de formats.
 * - La méthode principale est la fonction export() qui prend en paramètre
 *   un itérateur de références et se charge de générer le fichier d'export
 *   sur la sortie standard.
 * - Chaque exporteur peut avoir des settings.
 * - Plus tard, il sera possible de créer un nouvel exporteur directement
 *   depuis le back-office en paramétrant une classe existante (choix de la
 *   classe de base, saisie des paramètres).
 * - Pour cela, les exporteurs disposent d'une méthode settingsForm() qui
 *   retourne le formulaire de paramétrage de cet exporteur.
 * - Lorsque l'utilisateur valide, la méthode validateSettings() est appelée
 *   et les paramètres obtenus sont enregistrés.
 * - Lorsqu'un exporteur est créé, docalist-biblio lui passe en paramètre les
 *   paramètres enregistrés.
 * - Enfin, la méthode get() est un helper qui permet de récupérer une option
 *   de configuration.
 */
abstract class AbstractExporter {

    /**
     * Les paramètres de l'exporteur.
     *
     * @var array
     */
    protected $settings;

    /**
     * Retourne le formulaire de paramètrage de l'exporteur.
     *
     * @return Fragment
     */
    public static function settingsForm() {
        return null;
    }

    /**
     * Valide les settings de l'exporteur.
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

    /**
     * Initialise l'exporteur.
     *
     * @param array $settings Les paramètres de l'exporteur, tels que retournés
     * par validateSettings().
     */
    public function __construct(array $settings = []) {
        $this->settings = $settings;
    }

    /**
     * Retourne un paramètre de l'exporteur.
     *
     * @param string $setting Nom de l'option.
     * @param mixed $default Valeur par défaut retournée si le paramètre indiqué
     * n'existe pas dans les paramètres.
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
     * Exporte le lot de notices passé en paramètre.
     *
     * @param ReferenceIterator $references Un itérateur contenant les
     * notices à exporter.
     */
    abstract public function export(ReferenceIterator $references);

    /**
     * Retourne le type MIME du fichier généré.
     *
     * @return string Retourne la chaine "text/plain" par défaut.
     */
    public function mimeType() {
        return 'text/plain';
    }

    /**
     * Retourne le jeu de caractères du fichier généré.
     *
     * @return string Retourne la chaine "utf-8" par défaut.
     */
    public function charset() {
        return 'utf-8';
    }

    /**
     * Indique si le fichier généré en envoyé en ligne ou sous forme de fichier
     * attaché.
     *
     * @return boolean Retourne true par défaut.
     */
    public function inline() {
        return true;
    }

    /**
     * Retourne le nom du fichier exporté (sans la partie extension qui est
     * propre à chaque format).
     *
     * @return string Retourne la chaine "export" par défaut.
     */
    public function filename() {
        return __('export', 'docalist-biblio');
    }

    /**
     * Retourne l'extension du fichier généré (sans point).
     *
     * @return string "utf-8" par défaut.
     */
    public function extension() {
        return 'txt';
    }

    /**
     * Retourne l'entête "content-type" de la réponse http générée lors de
     * l'export.
     *
     * L'entête est généré en combinant le résultat des méthodes mimeType() et
     * charset().
     *
     * @return string Par défaut, retourne la chaine
     * "text/plain; charset=utf-8"
     */
    public final function contentType() {
        return sprintf('%s; charset=%s', $this->mimeType(), $this->charset());
    }

    /**
     * Retourne l'entête "content-disposition" de la réponse http générée lors
     * de l'export.
     *
     * L'entête est généré en combinant le résultat des méthodes inline(),
     * filename() et extension().
     *
     * @return string Par défaut, retourne la chaine
     * "inline; filename=export.txt"
     */
    public final function contentDisposition() {
        $disposition = $this->inline() ? 'inline' : 'attachment';
        $extension = '.' . $this->extension();
        $filename = $this->filename();
        $fallback = sanitize_title($filename) . $extension;
        $filename.= $extension;

        $header = sprintf('%s; filename="%s"', $disposition, $fallback);

        if ($filename !== $fallback) {
            $header .= sprintf("; filename*=utf-8''%s", rawurlencode($filename));
        }

        return $header;
    }
}