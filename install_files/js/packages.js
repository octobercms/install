/*!
 * Packages & Install (Step 3)
 */

Installer.Pages.packageInstall.init = function() {

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

    $.each(Installer.Pages.packageInstall.includedPlugins, function(index, plugin){
        $('#pluginList').renderPartial('packages/plugin', plugin, { append:true })
    })

    $.each(Installer.Pages.packageInstall.suggestedPlugins, function(index, plugin){
        $('#suggestedPlugins').renderPartial('packages/suggestion', plugin, { append:true })
    })

}