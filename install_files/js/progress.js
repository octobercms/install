/*!
 * Install Progress (Step 4)
 */

Installer.Pages.installProgress.init = function() {

    var eventChain = [],
        result

    /*
     * Process each step
     */
    $.each(Installer.Pages.installProgress.steps, function(index, step){

        /*
         * Step mutator exists
         */
        if (Installer.Pages.installProgress.execStep[step.code]) {
            result = Installer.Pages.installProgress.execStep[step.code](step)
            if (!$.isArray(result)) result = [result]
            eventChain = $.merge(eventChain, result)
        }
        /*
         * Fall back on default logic
         */
        else {
            eventChain.push(function(){
                return Installer.Pages.installProgress.execDefaultStep(step)
            })
        }

    })


    $.waterfall.apply(this, eventChain).done(function(){
        alert('Fin!')
    }).fail(function(reason){
        alert('Failed ' + reason)
    })

}

Installer.Pages.installProgress.execDefaultStep = function(step, options) {
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

Installer.Pages.installProgress.execIterationStep = function(step, handlerCode, collection) {
    var eventChain = []

    // Item must contain a code property
    $.each(collection, function(index, item){
        eventChain.push(function(){
            return Installer.Pages.installProgress.execDefaultStep({
                code: handlerCode,
                label: step.label + item.code
            }, { extraData: { name: item.code } })
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

Installer.Pages.installProgress.execStep = {}

Installer.Pages.installProgress.execStep.getMetaData = function(step) {
    return function() {
        return Installer.Pages.installProgress.execDefaultStep(step, {
            extraData: {
                plugins: Installer.Pages.installProgress.includedPlugins
            },
            onSuccess: function(data) {
                // Save the result for later usage
                Installer.Pages.packageInstall.meta = data.result
                console.log(data.result)
            }
        })
    }
}

Installer.Pages.installProgress.execStep.downloadCore = function(step) {
    return function() {
        return Installer.Pages.installProgress.execDefaultStep(step, {
            extraData: {
                hash: Installer.Pages.packageInstall.meta.core
            }
        })
    }
}

Installer.Pages.installProgress.execStep.downloadPlugins = function(step) {
    return Installer.Pages.installProgress.execIterationStep(step, 'downloadPlugin', Installer.Pages.packageInstall.includedPlugins)
}

Installer.Pages.installProgress.execStep.extractPlugins = function(step) {
    return Installer.Pages.installProgress.execIterationStep(step, 'extractPlugin', Installer.Pages.packageInstall.includedPlugins)
}
