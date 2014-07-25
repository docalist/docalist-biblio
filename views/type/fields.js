/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012, 2013 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
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
    
    /**
     * Met à jour le titre de la postbox lorsque le libellé d'un champ est
     * modifié.
     */
    $(document).on('input propertychange', '.label', function() {
        // event input : le seul nécessaire en html3
        // propertychange : pour ie
        // ajouter keyup et paste ? (cf SO ci dessous)
        
        // @see http://stackoverflow.com/a/9042710
        // @see http://www.greywyvern.com/?post=282
        
        var input = $(this); // le input.label qui a changé
        var postbox = input.parents('.postbox') // la postbox parent
        var title = $('h3 span', postbox); // le titre de la box
        title.text(input.val() || $('input.name', postbox).val());
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