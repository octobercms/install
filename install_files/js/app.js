/*!
 * October Logic
 */

$(document).ready(function(){
    Installer.Pages.systemCheck.isRendered = true
    Installer.showPage(Installer.ActivePage, true)
})

var Installer = {
    // ActivePage: 'systemCheck',
    ActivePage: 'configForm',
    Pages: {
        systemCheck: { isStep1: true, body: 'check' },
        configForm: { isStep2: true, body: 'config' },
        packageInstall: { isStep3: true, body: 'packages' }
    },
    Events: {}
}

Installer.Events.retry = function() {
    var pageEvent = Installer.Pages[Installer.ActivePage].retry
    pageEvent && pageEvent()
}

Installer.Events.next = function() {
    var nextButton = $('#nextButton')
    if (nextButton.hasClass('disabled'))
        return

    var pageEvent = Installer.Pages[Installer.ActivePage].next
    pageEvent && pageEvent()
}

Installer.showPage = function(pageId, noPush) {
    $("html, body").scrollTop(0)
    var obj = Installer.Pages[pageId]
    $('#containerHeader').renderPartial('header', obj)
    $('#containerTitle').renderPartial('title', obj).find('.steps > .last.pass:first').addClass('animate fade_in')
    $('#containerFooter').renderPartial('footer', obj)

    /*
     * @todo Cache the body load instead of resetting it on a secondary load 
     */
    $('#containerBody').renderPartial(obj.body, obj)
    obj.init && obj.init()

    // New page, add it to the history
    if (history.pushState && !noPush) {
        window.history.pushState({page:pageId}, '', window.location.pathname)
        obj.isRendered = true
    }

    Installer.ActivePage = pageId
}

Installer.setLoadingBar = function(state) {
    var progressBarContainer = $('#progressBar'),
        progressBar = $('#progressBar .progress-bar:first')

    if (state) {
        controlPanel.addClass('loading')
        progressBarContainer.addClass('loading').removeClass('loaded');
        progressBar.addClass('animate infinite_loader')
    }
    else {
        controlPanel.removeClass('loading')
        progressBarContainer.addClass('loaded').removeClass('loading')
        progressBar.removeClass('animate infinite_loader')
    }

    nextButton.attr('disabled', state)
}

$.fn.extend({
    renderPartial: function(name, data, options) {
        var container = $(this),
            template = $('[data-partial="' + name + '"]'),
            contents = Mustache.to_html(template.html(), data)

        options = $.extend(true, {
            append: false
        }, options)

        if (options.append) container.append(contents)
        else container.html(contents)
        return this
    },

    sendRequest: function(handler, data, options) {
        var form = $(this),
            postData = [form.serialize()],
            controlPanel = $('#formControlPanel'),
            nextButton = $('#nextButton')

        options = $.extend(true, {
            loadingIndicator: true
        }, options)

        if (options.loadingIndicator) {
            nextButton.attr('disabled', true)
            controlPanel.addClass('loading')
        }

        if (!data)
            data = {handler: handler}
        else
            data.handler = handler

        postData.push($.param(data))

        var postObj = $.post(window.location.pathname, postData.join('&'))
        postObj.always(function(){
            if (options.loadingIndicator) {
                nextButton.attr('disabled', false)
                controlPanel.removeClass('loading')
            }
        })
        return postObj
    }
})

$.extend({
    sendRequest: function(handler, data, options) {
        return $('<form />').sendRequest(handler, data, options)
    }
})

window.onpopstate = function(event) {
    // Navigate back/foward through a known push state
    if (event.state) {
        // Only allow navigation to previously rendered pages
        var noPop = (!Installer.Pages[event.state.page].isRendered || Installer.ActivePage == event.state.page)
        if (!noPop)
            Installer.showPage(event.state.page, true)
    }
    // Otherwise show the first page, if not already on it
    else if (Installer.ActivePage != 'systemCheck') {
        Installer.showPage('systemCheck', true)
    }
}
