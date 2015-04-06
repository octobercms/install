/*!
 * Starter Form (Step 3)
 */

Installer.Pages.starterForm.init = function() {
    var starterForm = $('#starterForm').addClass('animate fade_in')

    // Installer.renderSections(Installer.Pages.starterForm.sections)

    // var starterFormFailed = $('#starterFormFailed').hide(),
    //     starterFormDatabase = $('#starterFormDatabase')

    // starterFormDatabase.renderPartial('config/mysql')

    // // Set the encryption code with a random string
    // $('#advEncryptionCode').val(Installer.Pages.starterForm.randomString(16))
}

Installer.Pages.starterForm.next = function() {

    // var eventChain = [],
    //     starterFormFailed = $('#starterFormFailed').hide().removeClass('animate fade_in')

    // Installer.Data.config = $('#starterFormElement').serializeObject()

    // $('.section-area').removeClass('fail')

    // /*
    //  * Validate each section
    //  */
    // $.each(Installer.Pages.starterForm.sections, function(index, section){
    //     eventChain.push(function() {
    //         return $('#starterFormElement').sendRequest(section.handler).fail(function(data){

    //             starterFormFailed.show().addClass('animate fade_in')
    //             starterFormFailed.renderPartial('config/fail', { label: section.label, reason: data.responseText })

    //             var sectionElement = $('.section-area[data-section-code="'+section.code+'"]').addClass('fail')
    //             starterFormFailed.appendTo(sectionElement)

    //             Installer.showSection(section.code)

    //             // Scroll browser to the bottom of the error
    //             var scrollTo = starterFormFailed.offset().top - $(window).height() + starterFormFailed.height() + 10
    //             $('body, html').animate({ scrollTop: scrollTo })
    //         })
    //     })
    // })

    // $.waterfall.apply(this, eventChain).done(function(){
    //     Installer.showPage('projectForm')
    // })
}

Installer.Pages.starterForm.startClean = function() {
    Installer.showPage('installProgress')
}

Installer.Pages.starterForm.useProject = function() {
    Installer.showPage('projectForm')
}
