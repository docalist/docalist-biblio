<?php
/**
 * This file is part of Docalist Biblio.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Docalist\Biblio\Aggregation;

use Docalist\Search\Aggregation\Bucket\TableEntriesAggregation;

/**
 * Une agrégation standard de type "terms" sur le champ "media".
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class TermsMedia extends TableEntriesAggregation
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
        !isset($options['title']) && $options['title'] = __('Media', 'docalist-search');
        parent::__construct('media.filter', 'medias', $parameters, $options);
    }
}
