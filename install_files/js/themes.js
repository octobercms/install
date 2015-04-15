/*!
 * Themes Form (Step 3)
 */

Installer.Pages.themesForm.init = function() {

    if (Installer.DataSet.suggestedThemes.length != 0) {
        Installer.Pages.themesForm.renderThemes(Installer.DataSet.suggestedThemes)
        Installer.Pages.themesForm.pageReady()
    }
    else {
        $.sendRequest('onGetPopularThemes', {}, { loadingIndicator: false })
            .done(function(result){
                Installer.DataSet.suggestedThemes = result
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

Installer.Pages.themesForm.installTheme = function(code) {

    Installer.DataSet.includedPlugins = []
    Installer.DataSet.includedThemes = []

    $.sendRequest('onThemeDetails', {
        name: code
    })
    .done(function(theme){
        Installer.Data.config.active_theme = code
        Installer.DataSet.includedThemes.push(theme)
        Installer.DataSet.includedPlugins = theme.require
        Installer.showPage('installProgress')
    })
    .fail(function(data){
        alert(data.responseText)
    })
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
