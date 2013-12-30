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

    $.sendRequest('onGetPopularPackages').done(function(data){
        Installer.Pages.packageInstall.suggestedPlugins = data
        Installer.Pages.packageInstall.renderSuggested()
    })

}

Installer.Pages.packageInstall.next = function() {

    var eventChain = [],
        result

    /*
     * Process each step
     */
    $.each(Installer.Pages.packageInstall.steps, function(index, step){

        /*
         * Step mutator exists
         */
        if (Installer.Pages.packageInstall.execStep[step.code]) {
            result = Installer.Pages.packageInstall.execStep[step.code](step)
            if (!$.isArray(result)) result = [result]
            eventChain = $.merge(eventChain, result)
        }
        /*
         * Fall back on default logic
         */
        else {
            eventChain.push(function(){
                return Installer.Pages.packageInstall.execDefaultStep(step)
            })
        }

    })


    $.waterfall.apply(this, eventChain).done(function(){
        alert('Fin!')
    }).fail(function(reason){
        alert('Failed ' + reason)
    })

}

Installer.Pages.packageInstall.renderSuggested = function() {
    if (Installer.Pages.packageInstall.suggestedPlugins.length == 0) {
        $('#suggestedPluginsContainer').hide()
    }
    else {
        $('#suggestedPluginsContainer').show()
        $.each(Installer.Pages.packageInstall.suggestedPlugins, function(index, plugin){
            $('#suggestedPlugins').renderPartial('packages/suggestion', plugin, { append:true })
        })
    }
}

Installer.Pages.packageInstall.execDefaultStep = function(step, options) {
    var deferred = $.Deferred(),
        options = options || {},
        postData = { step: step.code }

    if (options.extraData)
        $.extend(postData, options.extraData)

    Installer.setLoadingBar(true, step.label)

    $('#configFormElement').sendRequest('onInstallStep', postData)
        .fail(function(data){
            deferred.reject(data.responseText)
        })
        .done(function(data){
            options.onSuccess && options.onSuccess(data)
            Installer.setLoadingBar(false)
            setTimeout(function() { deferred.resolve() }, 300)
        })

    return deferred
}

Installer.Pages.packageInstall.execIterationStep = function(step, handlerCode, collection) {
    var eventChain = []

    // Item must contain a code property
    $.each(collection, function(index, item){
        eventChain.push(function(){
            return Installer.Pages.packageInstall.execDefaultStep({
                code: handlerCode,
                label: step.label + item.code
            })
        })
    })

    return eventChain
}

/*
 * Specific logic to execute for each step
 *
 * These must return an anonymous function, or an array of anonymous functions,
 * that each return a deferred object
 */

Installer.Pages.packageInstall.execStep = {}

Installer.Pages.packageInstall.execStep.getMetaData = function(step) {
    return function() {
        return Installer.Pages.packageInstall.execDefaultStep(step, {
            extraData: {
                plugins: Installer.Pages.packageInstall.includedPlugins
            },
            onSuccess: function(data) {
                // Save the result for later usage
                Installer.Pages.packageInstall.meta = data.result
                console.log(data.result)
            }
        })
    }
}

Installer.Pages.packageInstall.execStep.downloadCore = function(step) {
    return function() {
        return Installer.Pages.packageInstall.execDefaultStep(step, {
            extraData: {
                hash: Installer.Pages.packageInstall.meta.core
            }
        })
    }
}

Installer.Pages.packageInstall.execStep.downloadPlugins = function(step) {
    return Installer.Pages.packageInstall.execIterationStep(step, 'downloadPlugin', Installer.Pages.packageInstall.includedPlugins)
}

Installer.Pages.packageInstall.execStep.extractPlugins = function(step) {
    return Installer.Pages.packageInstall.execIterationStep(step, 'extractPlugin', Installer.Pages.packageInstall.includedPlugins)
}
