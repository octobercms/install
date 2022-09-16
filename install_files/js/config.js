/*!
 * Configuration Form (Step 2)
 */
Installer.Pages.configForm.title = 'installer.app_config_section';
Installer.Pages.configForm.nextButton = 'webinstaller.continue';
Installer.Pages.configForm.activeCategory = null;

Installer.Pages.configForm.init = function() {
    $('#configForm').addClass('animate fade_in');
    $('#configForm .section-content:first').renderPartial('config/config');
    $('#configFormFailed').hide();
    $('#configFormDatabase').renderPartial('config/sql');
}

Installer.Pages.configForm.next = function() {
    var configFormFailed = $('#configFormFailed').hide().removeClass('animate fade_in');
    Installer.Data.config = $('#configFormElement').serializeObject();

    $('#configFormElement').sendRequest('onValidateConfig')
        .fail(function(data){
            configFormFailed.show().addClass('animate fade_in');
            configFormFailed.renderPartial('config/fail', { reason: data.responseText });

            // Scroll browser to the bottom of the error
            var scrollTo = configFormFailed.offset().top - $(window).height() + configFormFailed.height() + 10;
            $('body, html').animate({ scrollTop: scrollTo });
        })
        .done(function(){
            Installer.showPage('projectForm');
        });
}

Installer.Pages.configForm.toggleDatabase = function(el) {
    var selectedValue = $(el).val(),
        configFormDatabase = $('#configFormDatabase'),
        databasePartial = 'config/' + selectedValue;

    if (selectedValue === 'mysql' || selectedValue === 'pgsql' || selectedValue === 'sqlsrv') {
        databasePartial = 'config/sql';
    }

    configFormDatabase.renderPartial(databasePartial);
}
