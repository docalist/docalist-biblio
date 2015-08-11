<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2015 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 */
namespace Docalist\Biblio\Type;

use Docalist\Forms\Fragment;
use Docalist\Search\MappingBuilder;

/**
 * Interface pour un champ de premier niveau dans une Reference.
 */
interface BiblioField {
    /**
     * Retourne le formulaire "paramètres de base" du champ.
     *
     * @return Fragment
     */
    public function baseSettings();

    /**
     * Retourne le formulaire "paramètres de saisie" du champ.
     *
     * @return Fragment
     */
    public function editSettings();

    /**
     * Retourne le formulaire "paramètres d'affichage" du champ.
     *
     * @return Fragment
     */
    public function displaySettings();

    /**
     * Retourne le formulaire permettant de saisir ce champ.
     *
     * @return Fragment
     */
    public function editForm(); // renommer en edit() ?

    /**
     * Formatte le champ pour affichage.
     *
     * @return string
     */
    public function format();

    /**
     * Construit le mapping ElasticSearch du champ.
     *
     * @param MappingBuilder $mapping Le mapping à modifier.
     */
    public function mapping(MappingBuilder $mapping);

    /**
     * Convertit et stocke les données du champ dans le document ElasticSearch
     * passé en paramètre.
     *
     * @param array $document Le document ELasticSearch à modifier.
     */
    public function map(array & $document);
}