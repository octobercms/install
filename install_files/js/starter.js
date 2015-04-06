/*!
 * Starter Form (Step 3)
 */

Installer.Pages.starterForm.init = function() {
    var starterForm = $('#starterForm').addClass('animate fade_in')
}

Installer.Pages.starterForm.next = function() {
}

Installer.Pages.starterForm.startThemes = function() {
    Installer.showPage('themesForm')
}

Installer.Pages.starterForm.startClean = function() {
    Installer.showPage('installProgress')
}

Installer.Pages.starterForm.useProject = function() {
    Installer.showPage('projectForm')
}
