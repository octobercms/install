/*!
 * Language
 */

Installer.Lang = {
    toggleLang: function(el) {
        var selectedValue = $(el).val(),
            url = location.href,
            baseUrl = url.split('?')[0]

        url = baseUrl + '?lang=' + selectedValue
        location.href = url
    }
}