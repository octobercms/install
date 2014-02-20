/*!
 * Packages & Install (Step 3)
 */

Installer.Pages.installExtras.init = function() {

    $('#suggestedProductsContainer').hide()

    // Template for search results
    var template = Mustache.compile([
        '<div class="product-details">',
        '<div class="product-image"><img src="{{image}}" alt=""></div>',
        '<div class="product-name ">{{name}}</div>',
        '<div class="product-description text-overflow">{{description}}</div>',
        '</div>'
    ].join(''))

    // Source for product search
    var engine = new Bloodhound({
        name: 'extras',
        remote: window.location.pathname + '?handler=onSearchPackages&query=%QUERY',
        datumTokenizer: function(d) {
            return Bloodhound.tokenizers.whitespace(d.val)
        },
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        limit: 5
    })

    engine.initialize()

    /*
     * Bind autocomplete to search field
     */
    $('#productSearchInput')
        .typeahead(null, {
            displayKey: 'code',
            source: engine.ttAdapter(),
            minLength: 3,
            templates: {
                suggestion: template
            }
        })
        .on('typeahead:opened', function(){
            $('#productSearchInput .tt-dropdown-menu').css('width', $('#productSearchInput').width() + 'px')
        })
        .on('typeahead:selected', function(object, datum){
            $('#installExtrasForm').submit()
        })
        .on('keypress', function(e) {
            if (e.keyCode == 13) // ENTER key
                $('#installExtrasForm').submit()
        })

    Installer.Pages.installExtras.renderIncluded()

    /*
     * If no suggested extras are provided, pull them from the server
     */
    if (!Installer.Pages.installExtras.suggestedProducts || Installer.Pages.installExtras.suggestedProducts.length == 0) {
        $.sendRequest('onGetPopularPackages', {}, { loadingIndicator: false }).done(function(data){
            if (!$.isArray(data)) return
            Installer.Pages.installExtras.suggestedProducts = data
            Installer.Pages.installExtras.renderSuggested()
        })
    }
    else {
        Installer.Pages.installExtras.renderSuggested()
    }

}

Installer.Pages.installExtras.next = function() {
    Installer.showPage('installProgress')
}

Installer.Pages.installExtras.updateIncluded = function() {
    var includedPlugins = Installer.Pages.installExtras.includedPlugins,
        productListCounter = $('#installExtrasCounter')

    productListCounter.text(includedPlugins.length)
}

Installer.Pages.installExtras.renderIncluded = function() {
    var includedPlugins = Installer.Pages.installExtras.includedPlugins,
        productListEmpty = $('#productListEmpty')

    if (includedPlugins.length == 0) {
        productListEmpty.show()
    }
    else {
        $.each(includedPlugins, function(index, plugin){
            $('#productList').renderPartial('extras/product', plugin, { append:true })
        })
        productListEmpty.hide()
    }

    Installer.Pages.installExtras.updateIncluded()
}

Installer.Pages.installExtras.renderSuggested = function() {
    var suggestedProducts = Installer.Pages.installExtras.suggestedProducts

    if (suggestedProducts.length == 0) {
        $('#suggestedProductsContainer').hide()
    }
    else {
        $('#suggestedProductsContainer').show().addClass('animate fade_in')
        $.each(suggestedProducts, function(index, plugin){
            $('#suggestedProducts').renderPartial('extras/suggestion', plugin, { append:true })
        })
    }
}

Installer.Pages.installExtras.searchSubmit = function() {
    var searchInput = $('#productSearchInput'),
        searchValue = searchInput.val()

    Installer.Pages.installExtras.includePackage(searchValue)
    searchInput.typeahead('val', '')
}

Installer.Pages.installExtras.includePackage = function(code) {
    var includedPlugins = Installer.Pages.installExtras.includedPlugins,
        productListEmpty = $('#productListEmpty'),
        pluginExists = false

    $.each(includedPlugins, function(index, plugin){
        if (plugin.code == code)
            pluginExists = true
    })

    if (pluginExists)
        return

    $.sendRequest('onPluginDetails', { code: code })
        .done(function(plugin){
            Installer.Pages.installExtras.includedPlugins.push(plugin)
            productListEmpty.hide()
            $('#productList').renderPartial('extras/product', plugin, { append:true })
            Installer.Pages.installExtras.hilightIncludedPackages()
            Installer.Pages.installExtras.updateIncluded()
        })
        .fail(function(data){
            alert(data.responseText)
        })
}

Installer.Pages.installExtras.removePackage = function(code) {
    var includedPlugins = Installer.Pages.installExtras.includedPlugins,
        productListEmpty = $('#productListEmpty')

    $('#productList [data-code="'+code+'"]').fadeOut(500, function(){
        $('[data-code="'+code+'"]').removeClass('product-included')
        $(this).remove()

        Installer.Pages.installExtras.includedPlugins = includedPlugins = $.grep(includedPlugins, function(plugin) {
            return plugin.code != code;
        })

        if (includedPlugins.length == 0) {
            productListEmpty.show()
        }

        Installer.Pages.installExtras.updateIncluded()
    })
}

Installer.Pages.installExtras.hilightIncludedPackages = function() {
    var includedPlugins = Installer.Pages.installExtras.includedPlugins
    $.each(includedPlugins, function(index, plugin){
        $('[data-code="'+plugin.code+'"]').addClass('product-included')
    })
}
