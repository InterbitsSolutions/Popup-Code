Drupal.locale = { 'pluralFormula': function ($n) { return Number(($n>1)); }, 'strings': {"":{"An AJAX HTTP error occurred.":"Une erreur HTTP AJAX s\u0027est produite.","HTTP Result Code: !status":"Code de statut HTTP : !status","An AJAX HTTP request terminated abnormally.":"Une requ\u00eate HTTP AJAX s\u0027est termin\u00e9e anormalement.","Debugging information follows.":"Informations de d\u00e9bogage ci-dessous.","Path: !uri":"Chemin : !uri","StatusText: !statusText":"StatusText: !statusText","ResponseText: !responseText":"ResponseText : !responseText","ReadyState: !readyState":"ReadyState : !readyState","Loading":"En cours de chargement","(active tab)":"(onglet actif)","Hide":"Masquer","Show":"Afficher","Re-order rows by numerical weight instead of dragging.":"R\u00e9-ordonner les lignes avec des poids num\u00e9riques plut\u00f4t qu\u0027en les d\u00e9pla\u00e7ant.","Show row weights":"Afficher le poids des lignes","Hide row weights":"Cacher le poids des lignes","Drag to re-order":"Cliquer-d\u00e9poser pour r\u00e9-organiser","Changes made in this table will not be saved until the form is submitted.":"Les changements effectu\u00e9s dans ce tableau ne seront pris en compte que lorsque la configuration aura \u00e9t\u00e9 enregistr\u00e9e.","Enabled":"Activ\u00e9","This field is required.":"Ce champ est requis.","Disabled":"D\u00e9sactiv\u00e9","Shortcuts":"Raccourcis","Hide shortcuts":"Cacher les raccourcis","Next":"Suivant","Edit":"Modifier","Search":"Rechercher","Sunday":"Dimanche","Monday":"Lundi","Tuesday":"Mardi","Wednesday":"Mercredi","Thursday":"Jeudi","Friday":"Vendredi","Saturday":"Samedi","results":"r\u00e9sultats","Upload":"Transf\u00e9rer","Configure":"Configurer","All":"Tout","Done":"Termin\u00e9","Prev":"Pr\u00e9c.","Mon":"lun","Tue":"mar","Wed":"mer","Thu":"jeu","Fri":"ven","Sat":"sam","Sun":"dim","January":"janvier","February":"F\u00e9vrier","March":"mars","April":"avril","May":"mai","June":"juin","July":"juillet","August":"ao\u00fbt","September":"septembre","October":"octobre","November":"novembre","December":"d\u00e9cembre","Select all rows in this table":"S\u00e9lectionner toutes les lignes du tableau","Deselect all rows in this table":"D\u00e9s\u00e9lectionner toutes les lignes du tableau","Today":"Aujourd\u0027hui","Jan":"jan","Feb":"f\u00e9v","Mar":"mar","Apr":"avr","Jun":"juin","Jul":"juil","Aug":"ao\u00fb","Sep":"sep","Oct":"oct","Nov":"nov","Dec":"d\u00e9c","Su":"Di","Mo":"Lu","Tu":"Ma","We":"Me","Th":"Je","Fr":"Ve","Sa":"Sa","Not published":"Non publi\u00e9","Please wait...":"Veuillez patienter...","mm\/dd\/yy":"dd\/mm\/yy","Only files with the following extensions are allowed: %files-allowed.":"Seuls les fichiers se terminant par les extensions suivantes sont autoris\u00e9s\u00a0: %files-allowed.","By @name on @date":"Par @name le @date","By @name":"Par @name","Not in menu":"Pas dans le menu","Alias: @alias":"Alias : @alias","No alias":"Aucun alias","New revision":"Nouvelle r\u00e9vision","The changes to these blocks will not be saved until the \u003Cem\u003ESave blocks\u003C\/em\u003E button is clicked.":"N\u0027oubliez pas de cliquer sur \u003Cem\u003EEnregistrer les blocs\u003C\/em\u003E pour confirmer les modifications apport\u00e9es ici.","Show shortcuts":"Afficher les raccourcis","This permission is inherited from the authenticated user role.":"Ce droit est h\u00e9rit\u00e9e du r\u00f4le de l\u0027utilisateur authentifi\u00e9.","No revision":"Aucune r\u00e9vision","Requires a title":"Titre obligatoire","Not restricted":"Non restreint","Not customizable":"Non personnalisable","Restricted to certain pages":"R\u00e9serv\u00e9 \u00e0 certaines pages","The block cannot be placed in this region.":"Le bloc ne peut pas \u00eatre plac\u00e9 dans cette r\u00e9gion.","Customize dashboard":"Personnaliser le tableau de bord","Hide summary":"Masquer le r\u00e9sum\u00e9","Edit summary":"Modifier le r\u00e9sum\u00e9","Don\u0027t display post information":"Ne pas afficher les informations de la contribution","@title dialog":"dialogue de @title","The selected file %filename cannot be uploaded. Only files with the following extensions are allowed: %extensions.":"Le fichier s\u00e9lectionn\u00e9 %filename ne peut pas \u00eatre transf\u00e9r\u00e9. Seulement les fichiers avec les extensions suivantes sont permis : %extensions.","Autocomplete popup":"Popup d\u0027auto-compl\u00e9tion","Searching for matches...":"Recherche de correspondances...","Close":"Fermer","Previous page":"Page pr\u00e9c\u00e9dente","Next page":"Page suivante","Select all":"S\u00e9lectionner tout","\/en\/search\/key-words\/@keywords\/misc\/all\/sort\/score\/cat\/all\/temps\/all\/ingredient-to-include\/all\/ingredient-to-exclude\/none\/tab\/recipe\/page\/1\/":"\/recherche\/mot-cle\/@keywords\/misc\/all\/sort\/score\/cat\/all\/temps\/all\/ingredient-a-inclure\/all\/ingredient-a-exclure\/none\/tab\/recipe\/page\/1\/","\u003Cspan\u003E@start-@end on\u003C\/span\u003E @total @result-word":"\u003Cspan\u003E@start-@end sur\u003C\/span\u003E @total @result-word","key-words":"mot-cle","ingredient-to-include":"ingredient-a-inclure","ingredient-to-exclude":"ingredient-a-exclure","Loading results ...":"Chargement des r\u00e9sultats","result":"r\u00e9sultat","No matches found":"Aucun r\u00e9sultat correspondant aux filtres s\u00e9lectionn\u00e9s.","Remove some or":"Supprimez certains filtres ci-dessus,","all of the filters":"supprimez tous les filtres","Category:":"Cat\u00e9gorie :","Include:":"Inclure :","Exclude:":"Exclure :","Erase":"Effacer"}} };;
/**
 * Search settings
 */

var recipesSettings = {
    baseUri: {
        en: '/en/recipes',
        fr: '/recettes'
    },
    baseUriApi: 'api/recipe-navigation/{groupingTypeId}/{groupingId}',
    countUriApi: 'api/recipe-navigation/count/{groupingTypeId}/{groupingId}',
    ingredientAutocompleteApi: 'api/ingredients/autocomplete',
    facetUriApi: 'api/recipe-navigation/facets/{groupingTypeId}/{groupingId}'
};

var recipesThemesSettings = {
    baseUri: {
        en: '/en/recipe-categories',
        fr: '/themes'
    },
    baseUriApi: 'api/recipe-navigation/{groupingTypeId}/{groupingId}',
    countUriApi: 'api/recipe-navigation/count/{groupingTypeId}/{groupingId}',
    ingredientAutocompleteApi: 'api/ingredients/autocomplete',
    facetUriApi: 'api/recipe-navigation/facets/{groupingTypeId}/{groupingId}'
}

Drupal.settings['recipeFiltersSettings'] = {
    recipes: recipesSettings,
    recettes: recipesSettings,
    recipecategories: recipesThemesSettings,
    themes: recipesThemesSettings
};

Drupal.getLocalizedSetting = function (key) {
    var language = $('html').attr('lang');
    try {
        var value = eval(key);

        if (value[language] != undefined) {
            return value[language];
        }
        return value;
    } catch (e) {
        console.log('Undefined settings : ' + key)
    }

    return null;
};;
/**
 * Search settings
 */

Drupal.settings['searchSettings'] = {
    baseUri: {
        en: '/en/search',
        fr: '/recherche'
    },
    baseUriApi: 'api/search',
    facetUriApi: 'api/search/facets',
    countUriApi: 'api/search/count',
    ingredientAutocompleteApi: 'api/ingredients/autocomplete',
    fullUri: {
        en: '/en/search/key-words/@keywords/misc/all/sort/score/cat/all/temps/all/ingredient-to-include/all/ingredient-to-exclude/none/tab/recipe/page/1/',
        fr: '/recherche/mot-cle/@keywords/misc/all/sort/score/cat/all/temps/all/ingredient-a-inclure/all/ingredient-a-exclure/none/tab/recipe/page/1/'
    }
};

Drupal.getLocalizedSetting = function (key){
    var language = $('html').attr('lang');
    try {
        var value = eval(key);

        if(value[language] != undefined)
        {
            return value[language];
        }
        return value;
    }catch(e){
        console.log('Undefined settings : ' + key)
    }

    return null;
};;
/**
 * Manage search input actions in header and search page
 *
 * @require settings.js
 */

(function ($, Drupal, window, document, undefined) {

    $(function() {
        if($('.search-keywords').length > 0){
            $('.search-keywords').on('submit',function(){
                return submitSearchForm(this);
            });
        }
    });

})(jQuery, Drupal, this, this.document);

function submitSearchForm(element) {
    var action = element.getAttribute('action');
    var keywords = $(element).find('input[type=text]').val();
    var search = new SearchForm(keywords);

    // Change form action to redirect on search full url
    if(url = search.getUri()) {
        window.location.href = search.getUri();
        return false;
    }
    return true;
}

function encodeKeywords(value) {
    return encodeURIComponent(value.trim().toLowerCase().replace(/([@"\*,\.])/, '').replace(/\s+/g,'-'));
}

/*
 * Search form
 * @param keywords
 * @constructor
 */

function SearchForm(keywords){
    this.keywords = keywords;
}

SearchForm.prototype = {
    constructor: SearchForm,
    getUri:function () {
        if( this.keywords.length < 1 && (baseUri = Drupal.getLocalizedSetting('Drupal.settings.searchSettings.baseUri'))) {
            return baseUri;
        }
        if((fullUri = Drupal.getLocalizedSetting('Drupal.settings.searchSettings.fullUri'))) {
            return fullUri.replace('@keywords', encodeKeywords(this.keywords));
        };

        return null;
    }
}




;
jQuery(document).ready(function($) {

    Ricardo.Theme.InitPersonalNotes();
    Ricardo.Theme.InitToggleContent();

    $('.note-perso-list-item').once(function () {
        var recipeId = $(this).data('recipe-id');
        var lang = $('html').attr('lang');
        var urlForGetRecipeHtml = "/api/user/" + lang + "/recipe-note/get-by-recipe/" + recipeId;
        $.ajax({
            method: "GET",
            url: urlForGetRecipeHtml,
            dataType: "html"
        }).done(function (userPersonalNoteBlock) {
            if (userPersonalNoteBlock != '') {
                var $notePersoListItem = $('.note-perso-list-item');
                $notePersoListItem.replaceWith(userPersonalNoteBlock);
                Ricardo.Theme.InitCharCounter();
            }
        });
    });
});
;
jQuery(document).ready(function() {
    Ricardo.Theme.InitCheckAllButton();

    if($('.recipe-detail-page').length) {

        Ricardo.User.addLoggedInCallback(function() {
            ShowGroceryButton();
            var recipeId = $('.recipeId').val();
            AddIngredients(recipeId);
        });

        $('.btn-grocery-login').click(function() {
            var form_to_send = $(this).data('form-to-send');
            var recipeId = $('#' + form_to_send).find('.recipeId').val();
            var checkboxes = $('#' + form_to_send).find('input[type="checkbox"]:checked');
            var ingredients = [];
            $(checkboxes).each(function(i, checkbox) {
                if($(checkbox).val() != 'on') {
                    ingredients.push($(checkbox).val());
                }
            });

            if(typeof(Storage) !== "undefined") {
                sessionStorage.grocery_id = recipeId;
                sessionStorage.grocery_ingredients = ingredients.join('-');
            }
        });
    }
});

var ShowGroceryButton = function() {
    $('.btn-grocery-login').hide();
    $('.btn-grocery-add').show();
};

var AddIngredients = function(recipeId) {
    if(typeof(Storage) !== "undefined") {
        if(sessionStorage.grocery_ingredients && sessionStorage.grocery_id) {
            if(sessionStorage.grocery_id == recipeId) {
                var lang = $('html').attr('lang');
                var apiGroceryAdd = '/api/user/grocery/add/' + lang;
                $.ajax({
                    url: apiGroceryAdd,
                    dataType: 'json',
                    method: 'POST',
                    data: {
                        'ingredients' : sessionStorage.grocery_ingredients
                    }
                }).done(function(response) {
                    if(response) {
                        sessionStorage.removeItem('grocery_ingredients');
                        sessionStorage.removeItem('grocery_id');
                        Ricardo.Theme.AddDrupalMessagesWithAjax();
                    }
                });
            }
            else {
                sessionStorage.removeItem('grocery_ingredients');
                sessionStorage.removeItem('grocery_id');
            }
        }
    }
};;
jQuery(document).ready(function() {
    var $form = $('#add-comment');
    $form.submit(function(){
        $form.submit(function(){
            return false;
        });
        return true;
    });

    var $comments = $('#comments');
    var $btn = $comments.find('.load-more');
    var $list = $comments.find('ol');

    Ricardo.User.addLoggedInCallback(function() {
        $('.comment-block').hide();
        $('.comment-form').show();
    });
    Ricardo.User.addNotLoggedInCallback(function() {
        $('.comment-form').hide();
        $('.comment-block').show();
    });

    Ricardo.Theme.InitCharCounter();

    Ricardo.Theme.AddDrupalMessagesWithAjax();

    LoadNextComments($list, $btn);

    $btn.click(function(e) {

        waitingComments = true;

        ShowNextComments($list, $btn);

        e.preventDefault();
    });

    var $submit = $comments.find('.primaryAction');
    var $sel = $comments.find('.selRating');
    var $textarea = $comments.find('#id_comment');
    var $errorRating = $comments.find('.error-rating');
    var $errorComment = $comments.find('.error-comment');

    $submit.click(function(e) {
        $errorRating.hide();
        $errorComment.hide();
        if($sel.val() == '') {
            $errorRating.show();
            e.preventDefault();

        }
        else if($sel.val() == 0 && $textarea.val().trim() == '') {
            $errorComment.show();
            e.preventDefault();

        }
    });
});

var comments = '';
var loadingComments = false;
var waitingComments = false;

var LoadNextComments = function($list, $btn) {
    var type = $btn.data('type');
    var id = $btn.data('id');
    var limit = $btn.data('limit');
    var lastId = $btn.data('last-id');

    if(type != null) {
        loadingComments = true;

        var lang = $('html').attr('lang');
        var apiUserComments = '/api/user/comments/' + type + '/' + id + '/' + limit + '/' + lastId + '/' + lang;
        $.ajax({
            url: apiUserComments,
            dataType: 'json'
        }).done(function (response) {
            loadingComments = false;
            PrepareNextComments($list, $btn, response);

        });
    }
};

var PrepareNextComments = function($list, $btn, response) {
    if(response.comments != '') {
        comments = response.comments;
        $btn.data('last-id', response.lastId);
        if(waitingComments) {
            ShowNextComments($list, $btn);
        }
    }
    else {
        $btn.hide();
    }
};

var ShowNextComments = function($list, $btn) {
    if(!loadingComments && comments != '') {
        waitingComments = false;
        $list.append(comments);
        LoadNextComments($list, $btn);
    }
};;
jQuery(document).ready(function() {
    
    Ricardo.Theme.InitEllipsis();
    Ricardo.Theme.InitPrint();

    Ricardo.User.addLoggedInCallback(function() {
        var apiUserRecipesUrl = "/api/user/recipes";
        if($('.recipe-and-categories').length || $('.search-results').length) {
            $.ajax({
                url: apiUserRecipesUrl,
                dataType: "json"
            }).done(function(userRecipesId) {
                userRecipesId.forEach(function(value) {
                    $('.recipe-'+value)
                        .find('.flags')
                        .prepend("<li class='flagFavorite'>Add to my recipes</li>");
                });
            });
        }

        if($('.recipe-detail-page').length) {

            $.ajax({
                url: apiUserRecipesUrl,
                dataType: "json"
            }).done(function(favoriteRecipes) {

                var $linkRecipeNotAdded = $('#recipeNotAdded');
                var recipeId = $linkRecipeNotAdded.data('recipe-id');
                var context = $linkRecipeNotAdded.data('context');

                if($.inArray(recipeId, favoriteRecipes) != -1) {
                    $linkRecipeNotAdded.replaceWith($('#recipeAdded'));
                } else {
                    $linkRecipeNotAdded
                        .attr('href', context)
                        .removeClass('fancybox-login');
                }
            });
        }
    });
});

;
jQuery(document).ready(function() {
    Ricardo.User.addLoggedInCallback(function() {
        //var recipeId = $('#recipeNotAdded').data('recipe-id');
      //  if($("#recipeNotAdded").length != 0){
      //      AddRecipe(0);
      //  }
    });

    $('#recipeNotAdded').click(function(e) {
        var recipeId = $(this).data('recipe-id');
        AddRecipe(recipeId);
        e.preventDefault();
    });

    var AddRecipe = function(recipeId) {
        var lang = $('html').attr('lang');
        var apiRecipeAdd = '/api/user/recipe/add/' + lang;
        $.ajax({
            url: apiRecipeAdd,
            dataType: 'json',
            method: 'POST',
            data: {
                'id' : recipeId
            }
        }).done(function(response) {
            if(response) {
                Ricardo.Theme.AddDrupalMessagesWithAjax();
                $('#recipeNotAdded').replaceWith($('#recipeAdded'));
            }
        });
    };

    function smart_popup_loadad(){

        sas.call("std", {
            siteId: 8587,
            pageId: 66288,
            formatId: 34570,
            target: 'test_env'
        }, {
            forceMasterFlag: false,
            resetTimestamp: true
        });
    }

    var recipeNutritionHander = function() {
        /* call smartAd pop up */
        smart_popup_loadad();
        $('#sas_34570').prepend('<span class="title"></span>');

        $(".nutritionLink").fancybox({
            beforeShow: function(){
                $('.pubOverlay').show();
            },
            afterClose: function(){
                $('.pubOverlay').hide();
            }
        });
    };

    $('body').prepend('<div class="pubOverlay"></div>');
    $('.pubOverlay').hide();
    $('.pubOverlay').append('<div id="sas_34570" class="nutritionPub blankWrap"></div>');

    $("a.nutritionLink").one('click', recipeNutritionHander );
});
;
