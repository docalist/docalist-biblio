<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Biblio\Aggregation;

use Docalist\Search\Aggregation\Bucket\TableEntriesAggregation;

/**
 * Une agrégation standard de type "terms" sur le champ "genre".
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class TermsGenre extends TableEntriesAggregation
{
    /**
     * Constructeur
     *
     * @param array $parameters     Autres paramètres de l'agrégation.
     * @param array $options        Options d'affichage.
     */
    public function __construct(array $parameters = [], array $options = [])
    {
        !isset($parameters['size']) && $parameters['size'] = 1000;
        !isset($options['title']) && $options['title'] = __('Genre', 'docalist-search');
        parent::__construct('genre.filter', 'genres', $parameters, $options);
    }
}
