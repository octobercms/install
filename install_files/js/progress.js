/*!
 * Install Progress (Step 4)
 */

Installer.Pages.installProgress.activeStep = null

Installer.Pages.installProgress.init = function() {

    var self = Installer.Pages.installProgress,
        eventChain = []

    /*
     * Process each step
     */
    $.each(self.steps, function(index, step){
        eventChain = self.spoolStep(step, eventChain)
    })

    self.run(eventChain)
}

Installer.Pages.installProgress.retry = function() {
    var self = Installer.Pages.installProgress,
        eventChain = [],
        skipStep = true

    /*
     * Process each step
     */
    $.each(self.steps, function(index, step){

        if (step == self.activeStep)
            skipStep = false

        if (skipStep)
            return true // Continue

        eventChain = self.spoolStep(step, eventChain)
    })

    self.run(eventChain)
}

Installer.Pages.installProgress.run = function(eventChain) {
    var installProgressFailed = $('#installProgressFailed').hide()

    $.waterfall.apply(this, eventChain).done(function(){
        Installer.showPage('installComplete')
    }).fail(function(reason){
        Installer.setLoadingBar('failed')
        installProgressFailed.show().addClass('animate fade_in')
        installProgressFailed.renderPartial('progress/fail', { reason: reason })
    })
}

Installer.Pages.installProgress.spoolStep = function(step, eventChain) {
    var self = Installer.Pages.installProgress,
        result

    /*
     * Set the active step
     */
    eventChain.push(function(){
        self.activeStep = step
        return $.Deferred().resolve()
    })

    /*
     * Step mutator exists
     */
    if (self.execStep[step.code]) {
        result = self.execStep[step.code](step)
        if (!$.isArray(result)) result = [result]
        eventChain = $.merge(eventChain, result)
    }
    /*
     * Fall back on default logic
     */
    else {
        eventChain.push(function(){
            return self.execDefaultStep(step)
        })
    }

    return eventChain
}

Installer.Pages.installProgress.execDefaultStep = function(step, options) {
    var deferred = $.Deferred(),
        options = options || {},
        postData = { step: step.code, meta: Installer.Data.meta }

    if (options.extraData)
        $.extend(true, postData, options.extraData)

    Installer.setLoadingBar(true, step.label)

    $.sendRequest('onInstallStep', postData, { loadingIndicator: false })
        .fail(function(data){
            if (data.status = 504) {
                deferred.reject("The operation timed out. Please increase the server's timeout and try again.<br/><br/>\
                    Relevant documentation for <a target=\"_blank\" rel=\"noopener noreferrer\" href=\"https://httpd.apache.org/docs/2.4/mod/core.html#timeout\">Apache</a>,\
                    <a target=\"_blank\" rel=\"noopener noreferrer\" href=\"http://nginx.org/en/docs/http/ngx_http_fastcgi_module.html#fastcgi_read_timeout\">Nginx</a>.");
            } else {
                deferred.reject(data.responseText)
            }
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

        var data = { name: item.code }
        if (Installer.Data.project && Installer.Data.project.code)
            data.project_id = Installer.Data.project.code

        eventChain.push(function(){
            return Installer.Pages.installProgress.execDefaultStep({
                code: handlerCode,
                label: step.label + item.code
            }, { extraData: data })
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

        var data = {
            plugins: Installer.DataSet.includedPlugins,
            themes: Installer.DataSet.includedThemes
        }
        if (Installer.Data.project && Installer.Data.project.code)
            data.project_id = Installer.Data.project.code

        return Installer.Pages.installProgress.execDefaultStep(step, {
            extraData: data,
            onSuccess: function(data) {
                // Save the result for later usage
                Installer.Data.meta = data.result
            }
        })
    }
}

Installer.Pages.installProgress.execStep.downloadPlugins = function(step) {
    return Installer.Pages.installProgress.execIterationStep(step, 'downloadPlugin', Installer.DataSet.includedPlugins)
}

Installer.Pages.installProgress.execStep.extractPlugins = function(step) {
    return Installer.Pages.installProgress.execIterationStep(step, 'extractPlugin', Installer.DataSet.includedPlugins)
}

Installer.Pages.installProgress.execStep.downloadThemes = function(step) {
    return Installer.Pages.installProgress.execIterationStep(step, 'downloadTheme', Installer.DataSet.includedThemes)
}

Installer.Pages.installProgress.execStep.extractThemes = function(step) {
    return Installer.Pages.installProgress.execIterationStep(step, 'extractTheme', Installer.DataSet.includedThemes)
}

Installer.Pages.installProgress.execStep.setupConfig = function(step) {
    return function() {
        return Installer.Pages.installProgress.execDefaultStep(step, { extraData: Installer.Data.config })
    }
}

Installer.Pages.installProgress.execStep.createAdmin = function(step) {
    return function() {
        return Installer.Pages.installProgress.execDefaultStep(step, { extraData: Installer.Data.config })
    }
}

Installer.Pages.installProgress.execStep.setupProject = function(step) {
   return function() {
        var data = $.extend(true, {}, Installer.Data.project, { disableLog: true })
        return Installer.Pages.installProgress.execDefaultStep(step, { extraData: data })
    }
}

Installer.Pages.installProgress.execStep.finishInstall = function(step) {
    return function() {
        return Installer.Pages.installProgress.execDefaultStep(step, { extraData: Installer.Data.meta.core })
    }
}