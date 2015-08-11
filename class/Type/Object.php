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
 */
namespace Docalist\Biblio\Type;

use Docalist\Schema\Schema;

/**
 * Type de base pour tous les champs structurés
 */
class Object extends \Docalist\Type\Object implements BiblioField {
    use BiblioFieldTrait;

    /**
     * Nom et description des formats d'affichage disponibles pour cet objet.
     *
     * @var array Un tableau de la forme type => [nom => label].
     */
    static protected $formats = [];

    /**
     * Liste des formatteurs pour cet objet.
     *
     * @var array Un tableau de la forme type => [nom => callable].
     */
    static protected $formatters = [];

    /**
     * Initialise les formats d'affichage disponibles.
     *
     * Cette méthode est destinée à être surchargée dans les classes
     * descendantes, elle est appellée lors du premier appel à formats(),
     * defaultFormat(), registerFormat() ou callFormat().
     */
    protected static function initFormats() {
    }

    /**
     * Initialise les formats du type indiqué si ce n'est pas déjà fait.
     *
     * @param string $type Nom de classe du type.
     */
    private static function maybeInit($type) {
        if (!isset(self::$formatters[$type])){
            self::$formatters[$type] = self::$formats[$type] = [];
            $type::initFormats();
            if (empty(self::$formats[$type])) {
                echo "EXCEPTION : AUCUN FORMAT POUR $type<br />";
            }
        }
    }

    /**
     * Retourne la liste des formats d'affichage disponible pour cet objet.
     *
     * @return array Un tableau de la forme "nom de format" => callable
     *
     * La closure associée à chaque format reçoit en paramètre l'objet à
     * formatter et le schéma de l'objet parent.
     */
    public static final function formats() {
        self::maybeInit($type = get_called_class());

        return self::$formats[$type];
    }

    /**
     * Retourne le nom du format d'affichage par défaut pour cet objet.
     *
     * Par convention, le premier format d'affichage enregistré est considéré
     * comme étant le format par défaut. Cependant, les classes descendantes
     * peuvent surcharger cette méthode pour retourner un format différent si
     * besoin.
     *
     * @return string|false Retourne le nom du format par défaut ou false si
     * aucun format d'affichage n'est disponible.
     */
    public static function defaultFormat() {
        self::maybeInit($type = get_called_class());

        return key(static::$formatters[$type]);
    }

    /**
     * Enregistre un nouveau format d'affichage pour cet objet.
     *
     * Le premier format d'affichage enregistré est considéré comme le format
     * par défaut.
     *
     * @param string $name Nom de code du format.
     * @param string $label Libellé du format (une courte description ou un
     * exemple de formattage obtenu).
     * @param callable $callable Le callback ou la closure a appeller pour
     * formatter un objet dans ce format. Doit avoir une signature de la forme
     *
     * formatter(Object $obj, Schema $parent) : string
     */
    public static final function registerFormat($name, $label, callable $callable) {
        self::maybeInit($type = get_called_class());

        static::$formats[$type][$name] = $label;
        static::$formatters[$type][$name] = $callable;
    }

    /**
     * Appelle le formatteur dont le nom est passé en paramètre.
     *
     * Si le format indiqué n'existe pas, le format par défaut est utilisé.
     *
     * Si aucun format n'est enregistré, la méthode __toString() de l'objet
     * est utilisée.
     *
     * @param string $name Nom de code du format à générer.
     * @param Schema $parent Schéma de l'objet parent.
     *
     * @return string
     */
    public static function callFormat($name, Object $obj, BiblioField $parent) {
        self::maybeInit($type = get_called_class());

        // Utilise le format par défaut si le format indiqué n'existe pas
        if (!isset(static::$formatters[$type][$name])) {
            $name = static::defaultFormat();
        }

        // Exécute le formatter
        $formatter = static::$formatters[$type][$name];
        return $formatter($obj, $parent);
    }
}