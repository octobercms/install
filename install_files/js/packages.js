/*!
 * Packages & Install (Step 3)
 */

Installer.Pages.packageInstall.init = function() {

    $('#suggestedPluginsContainer').hide()

    var template = Mustache.compile([
        '<p class="plugin-image"><img src="{{image}}" alt=""></p>',
        '<p class="plugin-name">{{name}}</p>',
        '<p class="plugin-description">{{description}}</p>'
    ].join(''))

    $('#packageSearchInput').typeahead({
        name: 'plugins',
        remote: window.location.pathname + '?handler=onSearchPackages&query=%QUERY',
        template: template,
        minLength: 3,
        limit: 5
    }).on('typeahead:opened', function(){
        $('#packageSearchInput .tt-dropdown-menu').css('width', $('#packageSearchInput').width() + 'px')
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
            $('#pluginList').renderPartial('packages/plugin', plugin, { append:true })
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
