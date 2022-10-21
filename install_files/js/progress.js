/*!
 * Install Progress (Step 4)
 */
Installer.Pages.installProgress.title = 'Installation progress...'
Installer.Pages.installProgress.steps = [
    { code: 'getMetaData', label: 'Requesting package information' },
    { code: 'downloadCore', label: 'Downloading application files' },
    { code: 'extractCore', label: 'Unpacking application files' },
    { code: 'setupConfig', label: 'Building configuration files' },
    { code: 'setupProject', label: 'Setting website project' },
    { code: 'composerUpdate', label: 'Updating package manager' },
    { code: 'composerInstall', label: 'Installing application files' },
    { code: 'migrateDatabase', label: 'Migrating database' },
    { code: 'cleanInstall', label: 'Clean installation files' }
]

Installer.Pages.installProgress.activeStep = null;

Installer.Pages.installProgress.init = function() {
    var self = Installer.Pages.installProgress,
        eventChain = [];

    // Process each step
    $.each(self.steps, function(index, step){
        eventChain = self.spoolStep(step, eventChain);
    });

    // Lock navigation, forever
    Installer.PageLocked = true;

    self.run(eventChain);
}

Installer.Pages.installProgress.retry = function() {
    var self = Installer.Pages.installProgress,
        eventChain = [],
        skipStep = true;

    // Process each step
    $.each(self.steps, function(index, step){
        if (step == self.activeStep) {
            skipStep = false;
        }

        if (skipStep) {
            return true; // Continue
        }

        eventChain = self.spoolStep(step, eventChain);
    })

    self.run(eventChain);
}

Installer.Pages.installProgress.run = function(eventChain) {
    var installProgressFailed = $('#installProgressFailed').hide();

    $.waterfall.apply(this, eventChain)
        .done(function() {
            Installer.showPage('installComplete');
        })
        .fail(function(reason){
            Installer.setLoadingBar('failed');
            installProgressFailed.show().addClass('animate fade_in');
            installProgressFailed.renderPartial('progress/fail', { reason: reason });
        });
}

Installer.Pages.installProgress.spoolStep = function(step, eventChain) {
    var self = Installer.Pages.installProgress,
        result;

    // Set the active step
    eventChain.push(function(){
        self.activeStep = step;
        return $.Deferred().resolve();
    });

    // Step mutator exists
    if (self.execStep[step.code]) {
        result = self.execStep[step.code](step);
        if (!$.isArray(result)) {
            result = [result];
        }
        eventChain = $.merge(eventChain, result);
    }
    // Fall back on default logic
    else {
        eventChain.push(function(){
            return self.execDefaultStep(step);
        });
    }

    return eventChain;
}

Installer.Pages.installProgress.execDefaultStep = function(step, options) {
    var deferred = $.Deferred(),
        options = options || {},
        postData = { step: step.code, meta: Installer.Data.meta };

    if (options.extraData) {
        $.extend(true, postData, options.extraData);
    }

    Installer.setLoadingBar(true, step.label);

    $.sendRequest('onInstallStep', postData, { loadingIndicator: false })
        .fail(function(data){
            if (data.status == 504) {
                Installer.Pages.installProgress.timeoutRejection(deferred);
            }
            else {
                deferred.reject(data.responseText);
            }
        })
        .done(function(data){
            options.onSuccess && options.onSuccess(data);
            Installer.setLoadingBar(false);
            setTimeout(function() { deferred.resolve() }, 300);
        });

    return deferred;
}

Installer.Pages.installProgress.timeoutRejection = function(deferred) {
    var webserverHints = '';
    [
        ['Apache', 'https://httpd.apache.org/docs/2.4/mod/core.html#timeout'],
        ['Nginx', 'http://nginx.org/en/docs/http/ngx_http_fastcgi_module.html#fastcgi_read_timeout']
    ]
    .forEach(function(webserver, index) {
        webserverHints += (index !== 0 ? ', ' : '') + '<a target=\"_blank\" rel=\"noopener noreferrer\" href=\"'+ webserver[1] +'\">' + webserver[0] +'</a>';
    });

    deferred.reject(
        Installer.getLang('installer.operation_timeout_comment') +
        '<br/><br/>' +
        Installer.getLang('installer.operation_timeout_hint').replace(':name', webserverHints)
    );
}

Installer.Pages.installProgress.execIterationStep = function(step, handlerCode, collection) {
    var eventChain = [];

    // Item must contain a code property
    $.each(collection, function(index, item){
        var data = { name: item.code };
        if (Installer.Data.project && Installer.Data.project.code) {
            data.project_id = Installer.Data.project.code;
        }

        eventChain.push(function(){
            return Installer.Pages.installProgress.execDefaultStep({
                code: handlerCode,
                label: step.label + item.code
            }, { extraData: data });
        });
    });

    return eventChain;
}

/*
 * Specific logic to execute for each step
 *
 * These must return an anonymous function, or an array of anonymous functions,
 * that each return a deferred object
 */

Installer.Pages.installProgress.execStep = {};

Installer.Pages.installProgress.execStep.getMetaData = function(step) {
    return function() {
        var data = {
            plugins: Installer.DataSet.includedPlugins,
            themes: Installer.DataSet.includedThemes
        };

        if (Installer.Data.project && Installer.Data.project.code) {
            data.project_id = Installer.Data.project.code;
        }

        return Installer.Pages.installProgress.execDefaultStep(step, {
            extraData: data,
            onSuccess: function(data) {
                // Save the result for later usage
                Installer.Data.meta = data.result;
            }
        })
    }
}

Installer.Pages.installProgress.execStep.setupConfig = function(step) {
    return function() {
        var data = $.extend(true, {}, Installer.Data.config, { locale: Installer.Locale });
        return Installer.Pages.installProgress.execDefaultStep(step, { extraData: data });
    }
}

Installer.Pages.installProgress.execStep.migrateDatabase = function(step) {
    return function() {
        var data = $.extend(true, {}, Installer.Data.config, { is_clean_install: !Installer.Data.project });
        return Installer.Pages.installProgress.execDefaultStep(step, { extraData: data });
    }
}

Installer.Pages.installProgress.execStep.setupProject = function(step) {
   return function() {
        var data = $.extend(true, {}, Installer.Data.project, { disableLog: true });
        return Installer.Pages.installProgress.execDefaultStep(step, { extraData: data });
    }
}

Installer.Pages.installProgress.execStep.composerInstall = function(step) {
    return function() {
        var data = $.extend(true, {}, Installer.Data.meta.core, { is_clean_install: !Installer.Data.project });
        return Installer.Pages.installProgress.execDefaultStep(step, { extraData: data });
    }
}

Installer.Pages.installProgress.execStep.cleanInstall = function(step) {
    return function() {
        return Installer.Pages.installProgress.execDefaultStep(step, { extraData: Installer.Data.meta.core });
    }
}
