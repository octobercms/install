/*!
 * Packages & Install (Step 3)
 */

Installer.Pages.packageInstall.init = function() {

    $('#suggestedPluginsContainer').hide()

    var template = Mustache.compile([
        '<div class="package-details">',
        '<div class="package-image"><img src="{{image}}" alt=""></div>',
        '<div class="package-name ">{{name}}</div>',
        '<div class="package-description text-overflow">{{description}}</div>',
        '</div>'
    ].join(''))

    $('#packageSearchInput')
        .typeahead({
            name: 'packages',
            valueKey: 'code',
            remote: window.location.pathname + '?handler=onSearchPackages&query=%QUERY',
            template: template,
            minLength: 3,
            limit: 5
        })
        .on('typeahead:opened', function(){
            $('#packageSearchInput .tt-dropdown-menu').css('width', $('#packageSearchInput').width() + 'px')
        })
        .on('typeahead:selected', function(object, datum){
            console.log('a')
            $('#packageInstallForm').submit()
        })
        .on('keypress', function(e) {
            if (e.keyCode == 13) // ENTER key
                $('#packageInstallForm').submit()
        })

    Installer.Pages.packageInstall.renderIncluded()

    /*
     * If no suggested packages are provided, pull them from the server
     */
    if (!Installer.Pages.packageInstall.suggestedPlugins || Installer.Pages.packageInstall.suggestedPlugins.length == 0) {
        $.sendRequest('onGetPopularPackages', {}, { loadingIndicator: false }).done(function(data){
            if (!$.isArray(data)) return
            Installer.Pages.packageInstall.suggestedPlugins = data
            Installer.Pages.packageInstall.renderSuggested()
        })
    }
    else {
        Installer.Pages.packageInstall.renderSuggested()
    }

}

Installer.Pages.packageInstall.next = function() {
    Installer.showPage('installProgress')
}

Installer.Pages.packageInstall.renderIncluded = function() {
    var includedPlugins = Installer.Pages.packageInstall.includedPlugins,
        pluginListEmpty = $('#pluginListEmpty')

    if (includedPlugins.length == 0) {
        pluginListEmpty.show()
    }
    else {
        $.each(includedPlugins, function(index, plugin){
            $('#pluginList').renderPartial('packages/package', plugin, { append:true })
        })
        pluginListEmpty.hide()
    }

}

Installer.Pages.packageInstall.renderSuggested = function() {
    var suggestedPlugins = Installer.Pages.packageInstall.suggestedPlugins

    if (suggestedPlugins.length == 0) {
        $('#suggestedPluginsContainer').hide()
    }
    else {
        $('#suggestedPluginsContainer').show().addClass('animate fade_in')
        $.each(suggestedPlugins, function(index, plugin){
            $('#suggestedPlugins').renderPartial('packages/suggestion', plugin, { append:true })
        })
    }
}

Installer.Pages.packageInstall.searchSubmit = function() {
    var searchInput = $('#packageSearchInput'),
        searchValue = searchInput.val()

    Installer.Pages.packageInstall.includePackage(searchValue)
    searchInput.val('')
}

Installer.Pages.packageInstall.includePackage = function(code) {
    var includedPlugins = Installer.Pages.packageInstall.includedPlugins,
        pluginListEmpty = $('#pluginListEmpty'),
        pluginExists = false

    $.each(includedPlugins, function(index, plugin){
        if (plugin.code == code)
            pluginExists = true
    })

    if (pluginExists)
        return

    $.sendRequest('onPluginDetails', { code: code })
        .done(function(plugin){
            Installer.Pages.packageInstall.includedPlugins.push(plugin)
            pluginListEmpty.hide()
            $('#pluginList').renderPartial('packages/package', plugin, { append:true })
            Installer.Pages.packageInstall.hilightIncludedPackages()
        })
        .fail(function(data){
            alert(data.responseText)
        })
}

Installer.Pages.packageInstall.removePackage = function(code) {
    var includedPlugins = Installer.Pages.packageInstall.includedPlugins,
        pluginListEmpty = $('#pluginListEmpty')

    $('#pluginList [data-code="'+code+'"]').fadeOut(500, function(){
        $('[data-code="'+code+'"]').removeClass('package-included')
        $(this).remove()

        Installer.Pages.packageInstall.includedPlugins = includedPlugins = $.grep(includedPlugins, function(plugin) {
            return plugin.code != code;
        })

        if (includedPlugins.length == 0) {
            pluginListEmpty.show()
        }
    })

}

Installer.Pages.packageInstall.hilightIncludedPackages = function() {
    var includedPlugins = Installer.Pages.packageInstall.includedPlugins
    $.each(includedPlugins, function(index, plugin){
        $('[data-code="'+plugin.code+'"]').addClass('package-included')
    })
}
