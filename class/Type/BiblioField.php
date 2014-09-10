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

use Docalist\Forms\Fragment;
use Docalist\Schema\Field;

/**
 * Interface pour un champ de premier niveau dans une Reference.
 */
interface BiblioField {
    /**
     * Retourne le formulaire qui permet de paramétrer la saisie de ce champ.
     *
     * @return Fragment
     */
    public function editSettings(); // renommer en editSettings()

    /**
     * Retourne le formulaire permettant de saisir ce champ.
     *
     * @return Fragment
     */
    public function editForm(); // renommer en edit()

    /**
     * Retourne le formulaire qui permet de paramétrer le format d'affichage
     * du champ.
     *
     * @return Fragment
     */
    public function displaySettings();

    /**
     * Formatte le champ pour affichage.
     *
     * @return string
     */
    public function format();

    /**
     * Convertit et stocke les données du champ dans le document ElasticSearch
     * passé en paramètre.
     *
     * Cette méthode est utilisé par Reference::map() pour construire le
     * document envoyé à ELasticSearch pour indexer la notice.
     *
     * @param array $doc
     */
    public function map(array & $doc);

    /**
     * Modifie les mappings ElasticSearch passés en paramètre pour permettre à
     * chaque champ de définir la façon dont il est indexé.
     *
     * La totalité des mappings de l'index sont passés en paramètre. Chaque
     * champ indexé doit ajouter dans la clé "properties" le mapping de ses
     * données et peut ajouter dans la clé "dynamic_templates" les modèles
     * dont il a besoin.
     *
     * @param array $mappings
     */
    public static function ESmapping(array & $mappings, Field $schema);
}