/*!
 * Packages & Install (Step 3)
 */

// Expected:
//   [{ code: 'October.Demo', name: 'Demo', author: 'October', description: 'Demonstration features.', image: 'http://placehold.it/100x100' }, ...]
Installer.Pages.installExtras.includedPlugins = []
Installer.Pages.installExtras.includedThemes = []
Installer.Pages.installExtras.suggestedPlugins = []
Installer.Pages.installExtras.suggestedThemes = []

Installer.Pages.installExtras.init = function() {

    var installExtras = $('#installExtras').addClass('animate fade_in')
    Installer.renderSections(Installer.Pages.installExtras.sections)

    $('#suggestedProductsContainer').hide()

    Installer.Pages.installExtras.bindSearch('#pluginSearchInput')
    Installer.Pages.installExtras.bindSearch('#themeSearchInput')

    Installer.Pages.installExtras.bindSuggested('#suggestedPlugins')
    Installer.Pages.installExtras.bindSuggested('#suggestedThemes')

    Installer.Pages.installExtras.bindIncludeManager('#pluginList')
    Installer.Pages.installExtras.bindIncludeManager('#themeList')
}

Installer.Pages.installExtras.next = function() {
    Installer.showPage('installProgress')
}

Installer.Pages.installExtras.bindSearch = function(el) {
    var $el = $(el),
        $form = $el.closest('form'),
        handler = $el.data('handler')

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
        remote: window.location.pathname + '?handler=' + handler + '&query=%QUERY',
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
    $(el)
        .typeahead(null, {
            displayKey: 'code',
            source: engine.ttAdapter(),
            minLength: 3,
            templates: {
                suggestion: template
            }
        })
        .on('typeahead:opened', function(){
            $(el + ' .tt-dropdown-menu').css('width', $(el).width() + 'px')
        })
        .on('typeahead:selected', function(object, datum){
            $form.submit()
        })
        .on('keypress', function(e) {
            if (e.keyCode == 13) // ENTER key
                $form.submit()
        })

}

Installer.Pages.installExtras.searchSubmit = function(el) {
    var
        $el = $(el),
        $input = $el.find('.product-search-input.tt-input:first')

    Installer.Pages.installExtras.includePackage(el, $input.val())
    $input.typeahead('val', '')
}

Installer.Pages.installExtras.bindSuggested = function(el) {

    var
        dataSetId = $(el).data('set'),
        productSet = Installer.Pages.installExtras[dataSetId]

    /*
     * If no suggested extras are provided, pull them from the server
     */
    if (!productSet || productSet.length == 0) {
        var handler = $(el).data('handler')
        $.sendRequest(handler, {}, { loadingIndicator: false }).done(function(result){
            if (!$.isArray(result)) return
            productSet = result
            Installer.Pages.installExtras.renderSuggested(el, productSet)
        })
    }
    else {
        Installer.Pages.installExtras.renderSuggested(el, productSet)
    }
}

Installer.Pages.installExtras.renderSuggested = function(el, suggestedProducts) {
    var $el = $(el),
        $container = $el.closest('.suggested-products-container')

    if (suggestedProducts.length == 0) {
        $container.hide()
    }
    else {
        $container.show().addClass('animate fade_in')
        $.each(suggestedProducts, function(index, product){
            $el.renderPartial('extras/suggestion', product, { append:true })
        })
    }
}

Installer.Pages.installExtras.bindIncludeManager = function(el) {
    var
        $el = $(el),
        $list = $el.find('.product-list:first'),
        $empty = $el.find('.product-list-empty:first'),
        $counter = $el.find('.product-counter:first'),
        partial = $el.data('view'),
        dataSetId = $el.data('set'),
        includedProducts = Installer.Pages.installExtras[dataSetId]

    if (includedProducts.length == 0) {
        $empty.show()
    }
    else {
        $.each(includedProducts, function(index, product){
            $list.renderPartial(partial, product, { append:true })
        })
        $empty.hide()
    }

    $counter.text(includedProducts.length)
}

Installer.Pages.installExtras.findIncludeManagerFromEl = function(el) {
    var $el = $(el)

    if ($el.hasClass('product-list-manager'))
        return el

    return $(el).closest('[data-manager]').data('manager')
}

Installer.Pages.installExtras.includePackage = function(el, code) {
    var
        $el = $(Installer.Pages.installExtras.findIncludeManagerFromEl(el)),
        $list = $el.find('.product-list:first'),
        $empty = $el.find('.product-list-empty:first'),
        $counter = $el.find('.product-counter:first'),
        handler = $el.data('handler'),
        partial = $el.data('view'),
        dataSetId = $el.data('set'),
        includedProducts = Installer.Pages.installExtras[dataSetId],
        productExists = false

    $.each(includedProducts, function(index, product){
        if (product.code == code)
            productExists = true
    })

    if (productExists)
        return

    $.sendRequest(handler, { code: code })
        .done(function(product){
            includedProducts.push(product)
            $empty.hide()
            $list.renderPartial(partial, product, { append:true })
            $counter.text(includedProducts.length)
            Installer.Pages.installExtras.hilightIncludedPackages($el)
        })
        .fail(function(data){
            alert(data.responseText)
        })
}

Installer.Pages.installExtras.removePackage = function(el, code) {
    var
        $el = $(Installer.Pages.installExtras.findIncludeManagerFromEl(el)),
        $counter = $el.find('.product-counter:first'),
        $empty = $el.find('.product-list-empty:first'),
        dataSetId = $el.data('set'),
        includedProducts = Installer.Pages.installExtras[dataSetId]

    $el.find('[data-code="'+code+'"]').fadeOut(500, function(){

        Installer.Pages.installExtras[dataSetId] = includedProducts = $.grep(includedProducts, function(product) {
            return product.code != code;
        })

        if (includedProducts.length == 0) $empty.show()
        $counter.text(includedProducts.length)

        $(this).remove()
        $('[data-code="'+code+'"]').removeClass('product-included')
    })
}

Installer.Pages.installExtras.hilightIncludedPackages = function(el) {
    var
        $el = $(el),
        dataSetId = $el.data('set'),
        includedProducts = Installer.Pages.installExtras[dataSetId]

    $.each(includedProducts, function(index, product){
        $('[data-code="'+product.code+'"]').addClass('product-included')
    })
}
