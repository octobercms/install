/*!
 * October Logic
 */

$(document).ready(function(){
    Installer.Pages.systemCheck.isRendered = true
    Installer.showPage(Installer.ActivePage, true)
})

var Installer = {
    ActivePage: 'systemCheck',
    Pages: {
        systemCheck: { isStep1: true, body: 'check' },
        configForm: { isStep2: true, body: 'config' },
        packageInstall: { isStep3: true, body: 'packages' },
        installProgress: { isStep4: true, body: 'progress' },
        installComplete: { isStep5: true, body: 'complete' }
    },
    Events: {},
    Data: {
        meta: null,  // Meta information from the server
        config: null // Configuration from the user
    }
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
    var page = Installer.Pages[pageId],
        oldPage = (pageId != Installer.ActivePage) ? Installer.Pages[Installer.ActivePage] : null

    /*
     * Page events
     */
    oldPage && oldPage.beforeUnload && oldPage.beforeUnload()

    page.beforeShow && page.beforeShow()

    $('#containerHeader').renderPartial('header', page)
    $('#containerTitle').renderPartial('title', page).find('.steps > .last.pass:first').addClass('animate fade_in')
    $('#containerFooter').renderPartial('footer', page)

    /*
     * @todo Cache the body load instead of resetting it on a secondary load 
     */
    $('#containerBody').renderPartial(page.body, page)
    page.init && page.init()

    // New page, add it to the history
    if (history.pushState && !noPush) {
        window.history.pushState({page:pageId}, '', window.location.pathname)
        page.isRendered = true
    }

    Installer.ActivePage = pageId
}

Installer.setLoadingBar = function(state, message) {

    var progressBarContainer = $('#progressBar'),
        progressBar = $('#progressBar .progress-bar:first'),
        progressBarMessage = $('#progressBarMessage')

    if (message)
        progressBarMessage.text(message)

    progressBar.removeClass('progress-bar-danger')
    progressBarContainer.removeClass('failed')

    if (state == 'failed'){
        progressBar.addClass('progress-bar-danger')
        progressBarContainer.addClass('failed')
    }
    else if (state) {
        progressBarContainer.addClass('loading').removeClass('loaded')
        progressBar.addClass('animate infinite_loader')
    }
    else {
        progressBarContainer.addClass('loaded').removeClass('loading')
        progressBar.removeClass('animate infinite_loader')
    }
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
            postData = form.serializeObject(),
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

        if (data)
            $.extend(postData, data)

        var postObj = $.post(window.location.pathname, postData)
        postObj.always(function(){
            if (options.loadingIndicator) {
                nextButton.attr('disabled', false)
                controlPanel.removeClass('loading')
            }
        })
        return postObj
    },

    serializeObject: function() {
        var o = {};
        var a = this.serializeArray();
        $.each(a, function() {
            if (o[this.name] !== undefined) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });
        return o;
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
