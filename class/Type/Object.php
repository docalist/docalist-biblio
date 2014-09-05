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
namespace Docalist\Biblio\Type;

use Docalist\Schema\Schema;

/**
 * Type de base pour tous les champs structurés
 */
class Object extends \Docalist\Type\Object  implements BiblioField {
    use BiblioFieldTrait;

    static protected $formats;
    static protected $formatters;

    /**
     * Retourne la liste des formats d'affichage disponible pour cet objet.
     *
     * @return array Un tableau de la forme "nom de format" => callable
     *
     * La closure associée à chaque format reçoit en paramètre l'objet à
     * formatter et le schéma de l'objet parent.
     */
    public static final function formats() {
        return static::$formats;
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
        static::$formats[$name] = $label;
        static::$formatters[$name] = $callable;
    }

    /**
     * Initialise les formats d'affichage disponibles.
     *
     * Cette méthode est destinée à être surchargée dans les classes
     * descendantes, elle est appellée lors du premier appel à callFormatter().
     */
    protected static function initFormats() {
        static::$formats = static::$formatters =[];
    }

    /**
     * Appelle le formatteur dont le nom est passé en paramètre.
     *
     * Si le format indiqué n'existe pas, le premier format d'affichage
     * enregistré est utilisé comme format par défaut.
     *
     * Si aucun format n'est enregistré, la méthode __toString() de l'objet
     * est utilisée.
     *
     * @param string $name Nom de code du format à générer.
     * @param Schema $parent Schéma de l'objet parent.
     *
     * @return string
     */
    protected static function callFormat($name, Object $obj, BiblioField $parent) {
        // Initialise les formats au premier appel
        is_null(static::$formatters) && static::initFormats();

        // Utilise le format indiqué s'il existe
        if (isset(static::$formatters[$name])) {
            $formatter = static::$formatters[$name];
        }

        // Sinon, utilise le premier format qui a été enregistré
        else {
            // ou __toString() si on n'a aucun format enregistré
            if (false === $formatter = reset(static::$formatters[$name])) {
                return (string) $obj;
            }
        }

        // Exécute le formatter
        return $formatter($obj, $parent);
    }
}