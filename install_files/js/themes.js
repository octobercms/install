/*!
 * Themes Form (Step 3)
 */

Installer.Pages.themesForm.suggestedThemes = null

Installer.Pages.themesForm.init = function() {

    if (Installer.Pages.themesForm.suggestedThemes) {
        Installer.Pages.themesForm.renderThemes(Installer.Pages.themesForm.suggestedThemes)
        Installer.Pages.themesForm.pageReady()
    }
    else {
        $.sendRequest('onGetPopularThemes', {}, { loadingIndicator: false })
            .done(function(result){
                Installer.Pages.themesForm.suggestedThemes = result
                Installer.Pages.themesForm.renderThemes(result)
                Installer.Pages.themesForm.pageReady()
            })
    }
}

Installer.Pages.themesForm.renderThemes = function(suggestedThemes) {
    var themesList = $('#themesList')

    $.each(suggestedThemes, function(index, theme){
        themesList.renderPartial('themes/theme', theme, { append: true })
    })
}

Installer.Pages.themesForm.pageReady = function() {
    $('#themesForm').addClass('animate fade_in')
    $('#themesFormLoading').hide()
}

Installer.Pages.themesForm.confirmSelection = function(el) {
    $(el).closest('.theme-item')
        .find('.theme-item-confirm').show().end()
        .find('.list-inline').hide()
}

Installer.Pages.themesForm.cancelSelection = function(el) {
    $(el).closest('.theme-item')
        .find('.theme-item-confirm').hide().end()
        .find('.list-inline').show()
}

Installer.Pages.themesForm.next = function() {
}
