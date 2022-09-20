/*!
 * October Logic
 */

$(document).ready(function(){
    Installer.Pages.langPicker.isRendered = true;
    Installer.showPage(Installer.ActivePage, true);
});

var Installer = {
    ActivePage: 'langPicker',
    PageLocked: false,
    Pages: {
        langPicker:      { isStep0: true, body: 'lang' },
        systemCheck:     { isStep1: true, body: 'check' },
        configForm:      { isStep2: true, body: 'config' },
        projectForm:     { isStep3: true, body: 'project' },
        installProgress: { isStep4: true, body: 'progress' },
        installComplete: { isStep5: true, body: 'complete' }
    },
    Locale: 'en',
    ActiveSection: null,
    Sections: {},
    Events: {},
    Data: {
        meta:   null, // Meta information from the server
        config: null, // Configuration from the user
        project: null // Project for the installation
    },
    DataSet: {
        includedPlugins: [],  // Plugins to install
        includedThemes: []    // Themes to install
    }
}

Installer.Events.retry = function() {
    var pageEvent = Installer.Pages[Installer.ActivePage].retry
    pageEvent && pageEvent()
}

Installer.Events.next = function() {
    var nextButton = $('#nextButton')
    if (nextButton.hasClass('disabled')) {
        return;
    }

    var pageEvent = Installer.Pages[Installer.ActivePage].next;
    pageEvent && pageEvent();
}

Installer.showPage = function(pageId, noPush) {
    $('html, body').scrollTop(0);
    var page = Installer.Pages[pageId],
        oldPage = (pageId != Installer.ActivePage) ? Installer.Pages[Installer.ActivePage] : null;

    // Page events
    oldPage && oldPage.beforeUnload && oldPage.beforeUnload();
    Installer.ActivePage = pageId;
    page.beforeShow && page.beforeShow();

    $('#containerHeader').renderPartial('header', page);
    $('#containerTitle').renderPartial('title', page).find('.steps > .last.pass:first').addClass('animate fade_in');
    $('#containerFooter').renderPartial('footer', page);

    // Check if the content container exists already, if not, create it
    var pageContainer = $('#containerBody').find('.pageContainer-' + pageId);
    if (!pageContainer.length) {
        pageContainer = $('<div />').addClass('pageContainer-' + pageId);
        pageContainer.renderPartial(page.body, page);
        $('#containerBody').append(pageContainer);
        page.init && page.init();
    }
    else {
        page.reinit && page.reinit();
    }

    pageContainer.show().siblings().hide();
    Installer.renderLangMessages(pageContainer);

    // New page, add it to the history
    if (history.pushState && !noPush) {
        window.history.pushState({ page: pageId }, '', window.location.pathname);
        page.isRendered = true;
    }
}

Installer.setLoadingBar = function(state, message) {
    var progressBarContainer = $('#progressBar'),
        progressBar = $('#progressBar .progress-bar:first'),
        progressBarMessage = $('#progressBarMessage');

    if (message) {
        progressBarMessage.text(message);
    }

    progressBar.removeClass('progress-bar-danger');
    progressBarContainer.removeClass('failed');

    if (state == 'failed') {
        progressBar.addClass('progress-bar-danger').removeClass('animate infinite_loader');
        progressBarContainer.addClass('failed');
    }
    else if (state) {
        progressBarContainer.addClass('loading').removeClass('loaded');
        progressBar.addClass('animate infinite_loader');
    }
    else {
        progressBarContainer.addClass('loaded').removeClass('loading');
        progressBar.removeClass('animate infinite_loader');
    }
}

Installer.renderLangMessages = function(container) {
    // Render language string
    $('[data-lang]', container).each(function() {
        var langKey = $(this).attr('data-lang') ? $(this).attr('data-lang') : $(this).text();
        $(this).text(Installer.getLang(langKey));
        $(this).attr('data-lang', langKey);
    });
}

Installer.getLang = function(langKey) {
    var activeLocale = installerLang[Installer.Locale] ? Installer.Locale : 'en';

    // Access dot notation
    var langValue = langKey.split('.').reduce(function(a, b) {
        return a[b] ? a[b] : '';
    }, installerLang[activeLocale]);

    if (!langValue) {
        langValue = langKey.split('.').reduce(function(a, b) {
            return a[b] ? a[b] : '';
        }, installerLang['en']);
    }

    if (!langValue) {
        return langKey;
    }

    return langValue;
}

$.fn.extend({
    renderPartial: function(name, data, options) {
        var container = $(this),
            template = $('[data-partial="' + name + '"]'),
            contents = Mustache.to_html(template.html(), data);

        options = $.extend(true, {
            append: false
        }, options);

        if (options.append) {
            container.append(contents);
        }
        else {
            container.html(contents);
        }

        Installer.renderLangMessages(container);

        return this;
    },

    sendRequest: function(handler, data, options) {
        var form = $(this),
            postData = form.serializeObject(),
            controlPanel = $('#formControlPanel'),
            nextButton = $('#nextButton');

        options = $.extend(true, {
            loadingIndicator: true
        }, options);

        if (options.loadingIndicator) {
            nextButton.attr('disabled', true);
            controlPanel.addClass('loading');
        }

        if (!data) {
            data = { handler: handler };
        }
        else {
            data.handler = handler;
        }

        if (data) {
            $.extend(postData, data);
        }

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
        return $('<form />').sendRequest(handler, data, options);
    }
})

window.onpopstate = function(event) {
    // If progress page has rendered, disable navigation
    if (Installer.PageLocked) {
        // Do nothing
    }
    // Navigate back/foward through a known push state
    else if (event.state) {
        // Only allow navigation to previously rendered pages
        var noPop = (!Installer.Pages[event.state.page].isRendered || Installer.ActivePage == event.state.page)
        if (!noPop) {
            Installer.showPage(event.state.page, true);
        }
    }
    // Otherwise show the first page, if not already on it
    else if (Installer.ActivePage != 'langPicker') {
        Installer.showPage('langPicker', true);
    }
}
