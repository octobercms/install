/*!
 * Configuration Form (Step 2)
 */

Installer.Pages.configForm.activeCategory = null

Installer.Pages.configForm.init = function() {
    var configForm = $('#configForm').addClass('animate fade_in')

    $.each(Installer.Pages.configForm.sections, function(index, section){
        var container = $('#configFormOptions'),
            sectionElement = $('<div />').addClass('config-section').attr('data-config-code', section.code)

        sectionElement
            .renderPartial(section.partial)
            .prepend($('<h3 />').text(section.label))
            .hide()
            .appendTo(container)

        /*
         * Side navigation
         */
        var sideNav = $('#configSideNav'),
            menuItem = $('<li />').attr('data-config-code', section.code),
            menuItemLink = $('<a />').attr({ href: "javascript:Installer.Pages.configForm.showCategory('"+section.code+"')"}).text(section.label),
            sideNavCategory = sideNav.find('[data-config-category="'+section.category+'"]:first')

        if (sideNavCategory.length == 0) {
            sideNavCategory = $('<ul />').addClass('nav').attr('data-config-category', section.category)
            sideNavCategoryTitle = $('<h3 />').text(section.category)
            sideNav.append(sideNavCategoryTitle).append(sideNavCategory)
        }

        sideNavCategory.append(menuItem.append(menuItemLink))
    })

    var configFormFailed = $('#configFormFailed').hide(),
        configFormDatabase = $('#configFormDatabase')

    configFormDatabase.renderPartial('config/mysql')

    Installer.Pages.configForm.showCategory(Installer.Pages.configForm.sections[0].code)
}

Installer.Pages.configForm.next = function() {

    var eventChain = [],
        configFormFailed = $('#configFormFailed').hide().removeClass('animate fade_in')

    Installer.Data.config = $('#configFormElement').serializeObject()

    $('.config-section').removeClass('fail')

    /*
     * Validate each section
     */
    $.each(Installer.Pages.configForm.sections, function(index, section){
        eventChain.push(function() {
            return $('#configFormElement').sendRequest(section.handler).fail(function(data){

                configFormFailed.show().addClass('animate fade_in')
                configFormFailed.renderPartial('config/fail', { label: section.label, reason: data.responseText })

                var sectionElement = $('.config-section[data-config-code="'+section.code+'"]').addClass('fail')
                configFormFailed.appendTo(sectionElement)

                Installer.Pages.configForm.showCategory(section.code)

                // Scroll browser to the bottom of the error
                var scrollTo = configFormFailed.offset().top - $(window).height() + configFormFailed.height() + 10
                $('body,html').animate({scrollTop: scrollTo })
            })
        })
    })

    $.waterfall.apply(this, eventChain).done(function(){
        Installer.showPage('installExtras')
    })
}

Installer.Pages.configForm.toggleDatabase = function(el) {
    var selectedValue = $(el).val(),
        configFormDatabase = $('#configFormDatabase'),
        databasePartial = 'config/' + selectedValue

    configFormDatabase.renderPartial(databasePartial)
}

Installer.Pages.configForm.renderPageNav = function() {
    var pageNav = $('#configPageNav').empty(),
        sections = Installer.Pages.configForm.sections,
        activeCategory = Installer.Pages.configForm.activeCategory

    $.each(sections, function(index, section){
        if (section.code == activeCategory) {

            var nextStep = sections[index+1] ? sections[index+1] : null,
                lastStep = sections[index-1] ? sections[index-1] : null

            if (lastStep) {
                $('<a />')
                    .text(lastStep.label)
                    .addClass('btn btn-default prev')
                    .attr('href', "javascript:Installer.Pages.configForm.showCategory('"+lastStep.code+"')")
                    .appendTo(pageNav)
            }

            if (nextStep) {
                $('<a />')
                    .text(nextStep.label)
                    .addClass('btn btn-default next')
                    .attr('href', "javascript:Installer.Pages.configForm.showCategory('"+nextStep.code+"')")
                    .appendTo(pageNav)
            }

            return false
        }
    })
}

Installer.Pages.configForm.showCategory = function(code) {
    var sideNav = $('#configSideNav'),
        menuItem = sideNav.find('[data-config-code="'+code+'"]:first'),
        container = $('#configFormOptions'),
        sectionElement = container.find('[data-config-code="'+code+'"]:first')

    sideNav.find('li.active').removeClass('active')
    menuItem.addClass('active')
    sectionElement.show().siblings().hide()

    Installer.Pages.configForm.activeCategory = code
    Installer.Pages.configForm.renderPageNav()
}
