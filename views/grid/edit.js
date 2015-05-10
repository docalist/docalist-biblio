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
 * @version     $Id$
 */
/*
 * Gère la liste des champs pour un type de notice.
 */
(function($) {
    // Traque la dernière boite cliquée ou sélectionnée
    var last;
    
    // Permet à l'utilisateur de déplacer les boites
    $('.metabox-holder').sortable({
        helper: 'clone',
        placeholder: 'sortable-placeholder',
        forcePlaceholderSize: true,
        opacity: 0.65
    });
    
    // Affiche ou masque le formulaire du champ quand on clique sur le titre
    $(document).on('click', '.postbox h3, .postbox .handlediv', function() {
        $(this).parent('.postbox').toggleClass('closed');
    });

    /*
     * Remarque pour le code qui suit : pour détecter en temps réel les 
     * modifications faites dans un input, on utilise les événements "input" et 
     * "propertychange".
     * - input est le seul nécessaire en html5
     * - mais pour IE, on est obligé d'ajouter propertychange.
     * Il faudrait éventuellement ajouter keyup et paste :
     * - http://stackoverflow.com/a/9042710
     * - http://www.greywyvern.com/?post=282
     */
    
    /**
     * Met à jour le titre de la postbox lorsque le libellé d'un champ est
     * modifié.
     */
    $(document).on('input propertychange', '.label,.labelspec', function() {
        var input = $(this); // le input.label qui a changé
        var postbox = input.parents('.postbox') // la postbox parent
        var title = $('h3 span', postbox); // le titre de la box
        title.text(input.val() || input.attr('placeholder') || $('input.name', postbox).val());
    });
    
    /**
     * Met à jour l'icone "champ restreint" lorsque le champ "capability" est 
     * modifié.
     */
    $(document).on('input propertychange', '.capability,.capabilityspec', function() {
        var input = $(this); // le input.label qui a changé
        var postbox = input.parents('.postbox') // la postbox parent
        if (input.val() || input.attr('placeholder')) {
            postbox.addClass('has-cap');
        } else {
            postbox.removeClass('has-cap');
        }
    });
    
    /**
     * Mémorise la dernière boite cliquée pour permettre à add-group de savoir
     * ou insérer la nouvelle boite.
     */
    $(document).on('focusin click', '.postbox', function() {
        last = $(this);
    });
    
    /**
     * Ajoute une nouvelle boite.
     */
    $('.add-group').click(function() {
        var template = $('#group-template');
        var number = $('.group').length + 1;
        while ($('#group' + number).length) ++number;
        
        var html = template.html().replace(/\{group-number\}/g, number);
        var group = $(html);
        
        last ? group.insertAfter(last) : group.appendTo($('#fields'));
        group.hide().fadeIn();
        last = group;
        $('.label', group).focus();
    });
    
    /**
     * Supprime une boite
     */
    $(document).on('click', '.delete-group', function() {
        $(this).parents('.postbox').remove();
        $last = null;
    });
}(jQuery));