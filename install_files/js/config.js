/*!
 * Configuration Form (Step 2)
 */

Installer.Pages.configForm.init = function() {
    var configForm = $('#configForm').addClass('animate fade_in')

    $.each(Installer.Pages.configForm.sections, function(index, section){
        var container = section.isAdvanced ? $('#advancedOptions') : $('#regularOptions'),
            sectionElement = $('<div />').addClass('config-section').attr('data-config-code', section.code)

        sectionElement
            .renderPartial(section.partial)
            .prepend($('<h3 />').text(section.label))
            .appendTo(container)
    })

    var configFormFailed = $('#configFormFailed').hide(),
        configFormDatabase = $('#configFormDatabase')

    configFormDatabase.renderPartial('config/mysql')
}

Installer.Pages.configForm.next = function() {

    var eventChain = [],
        configFormFailed = $('#configFormFailed').hide().removeClass('animate fade_in')

    $('.config-section').removeClass('fail')

    /*
     * Validate each section
     */
    $.each(Installer.Pages.configForm.sections, function(index, section){
        eventChain.push(function() {
            return $('#configFormElement').sendRequest(section.handler).fail(function(data){

                configFormFailed.show().addClass('animate fade_in')
                configFormFailed.renderPartial('config/fail', { label: section.label, reason: data.responseText })

                var sectionElement = $('[data-config-code="'+section.code+'"]').addClass('fail')
                configFormFailed.appendTo(sectionElement)

                // Scroll browser to the bottom of the error
                var scrollTo = configFormFailed.offset().top - $(window).height() + configFormFailed.height() + 10
                $('body,html').animate({scrollTop: scrollTo })
            })
            return deferred
        })
    })

    $.waterfall.apply(this, eventChain).done(function(){
        Installer.showPage('packageInstall')
    })
}

Installer.Pages.configForm.toggleDatabase = function(el) {
    var selectedValue = $(el).val(),
        configFormDatabase = $('#configFormDatabase'),
        databasePartial = 'config/' + selectedValue

    configFormDatabase.renderPartial(databasePartial)
}

Installer.Pages.configForm.showAdvanced = function() {
    $('#configFormShowAdvanced').parent().fadeOut(250, function() {
        $(this).remove()
        $('#advancedOptions').toggleClass('visible')
    })
}