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

use InvalidArgumentException;
use Docalist\Biblio\Reference\ReferenceIterator;
use Docalist\Search\SearchRequest;

/**
 * Un format d'export composé d'un converter et d'un exporter.
 */
class Format {
    /**
     * Le nom du format
     *
     * @var string
     */
    protected $name;

    /**
     * Les paramètres du format.
     *
     * @var array
     */
    protected $format;

    /**
     * Le converter de ce format, créé à la demande par converter().
     *
     * @var Converter
     */
    protected $converter;

    /**
     * L'eexporter de ce format, créé à la demande par exporter().
     *
     * @var Exporter
     */
    protected $exporter;

    /**
     * Initialise l'objet
     *
     * @param array $settings Les paramètres de l'objet.
     * - label : optionnel, string, libellé du format.
     * - description : optionnel, string description du format.
     * - converter : obligatoire, string, nom de classe complet du converter.
     * - converter-settings : optionnel, array, paramètres du converter.
     * - exporter : obligatoire, string, nom de classe complet de l'exporter.
     * - exporter-settings : optionnel, array, paramètres de l'exporter.
     * - grid : optionnel, string, grille à utiliser pour charger les références.
     */
    public function __construct($name, array $format) {
        // Vérifie que le format indique le nom du convertisseur à utiliser
        if (!isset($format['converter'])) {
            $msg = sprintf(__("Aucun convertisseur indiqué dans le format %s.", 'docalist-biblio-export'), $name);
            throw new InvalidArgumentException($msg);
        }

        // Vérifie que le format indique le nom de l'exporter à utiliser
        if (!isset($format['exporter'])) {
            $msg = sprintf(__("Aucune exporteur indiqué dans le format %s.", 'docalist-biblio'), $name);
            throw new InvalidArgumentException($msg);
        }

        $this->name = $name;
        $this->format = $format;
    }

    /**
     * Retourne le nom du format.
     *
     * @return string
     */
    public function name() {
        return $this->name;
    }

    /**
     * Retourne le libellé du format.
     *
     * @return string
     */
    public function label() {
        if (isset($this->format['label'])) {
            return $this->format['label'];
        }

        return $this->name;
    }

    /**
     * Retourne la description du format.
     *
     * @return string
     */
    public function description() {
        if (isset($this->format['description'])) {
            return $this->format['description'];
        }

        return $this->converter()->description() . '<br />' . $this->exporter()->description();
    }

    /**
     * Crée et retourne le converter de ce format.
     *
     * @return Converter
     */
    public function converter() {
        if (! isset($this->converter)) {
            $converter = $this->format['converter'];
            $settings = isset($this->format['converter-settings']) ? $this->format['converter-settings'] : [];
            $this->converter = new $converter($settings);
        }

        return $this->converter;
    }

    /**
     * Crée et retourne l'exporter de ce format.
     *
     * @return Exporter
     */
    public function exporter() {
        if (! isset($this->exporter)) {
            $exporter = $this->format['exporter'];
            $settings = isset($this->format['exporter-settings']) ? $this->format['exporter-settings'] : [];
            $this->exporter = new $exporter($this->converter(), $settings);
        }

        return $this->exporter;
    }

    /**
     * Exporte le lot de notices passé en paramètre.
     *
     * @param SearchRequest $request La requête contenant les notices à exporter.
     */
    public function export(SearchRequest $request, $disposition = 'inline') {
        // Crée l'itérateur
        $grid = isset($this->format['grid']) ? $this->format['grid'] : 'base';
        $iterator = new ReferenceIterator($request, $grid);

        // Crée l'exporteur
        $exporter = $this->exporter();

        // Génère les entêtes http
        header('Content-Type: ' . $exporter->contentType());
        header('Content-disposition: ' . $exporter->contentDisposition($disposition));
        header('X-Content-Type-Options: ' . 'nosniff');

        // Lance l'export
        $this->exporter()->export($iterator);
    }
}