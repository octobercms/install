/*!
 * Lang Picker Page (Step 0)
 */
Installer.Pages.langPicker.title = 'Select Language';
Installer.Pages.langPicker.nextButton = null;

Installer.Pages.langPicker.selectLanguage = function(lang) {
    Installer.Locale = lang;
    Installer.Pages.langPicker.next();
}

Installer.Pages.langPicker.next = function() {
    Installer.showPage('systemCheck');
}
