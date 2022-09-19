/*!
 * Packages & Install (Step 3)
 */

Installer.Pages.projectForm.title = 'webinstaller.license_details';
Installer.Pages.projectForm.nextButton = 'webinstaller.install_button';

Installer.Pages.projectForm.init = function() {
    var projectForm = $('#projectForm').addClass('animate fade_in');

    Installer.Pages.projectForm.refreshSections();

    $('#nextButton').addClass('disabled');

    Installer.Pages.projectForm.bindAll();
}

Installer.Pages.projectForm.next = function() {
    Installer.showPage('installProgress');
}

Installer.Pages.projectForm.startClean = function() {
    Installer.showPage('installProgress');
}

Installer.Pages.projectForm.bindAll = function() {
    Installer.Pages.projectForm.bindIncludeManager('#pluginList');
    Installer.Pages.projectForm.bindIncludeManager('#themeList');
}

Installer.Pages.projectForm.detachProject = function(el) {
    Installer.Data.project = null;
    Installer.DataSet.includedPlugins = [];
    Installer.DataSet.includedThemes = [];
    Installer.Pages.projectForm.refreshSections();
    Installer.Pages.projectForm.bindAll();

    $('#nextButton').addClass('disabled');
}

Installer.Pages.projectForm.attachProject = function(el) {
    var
        $el = $(el),
        $input = $el.find('.project-id-input:first'),
        code = $input.val(),
        projectFormFailed = $('#projectFormFailed').hide().removeClass('animate fade_in');

    $.sendRequest('onProjectDetails', { project_id: code })
        .done(function(result){
            Installer.Data.project = result;
            Installer.Data.project.code = code;
            Installer.DataSet.includedPlugins = result.plugins ? result.plugins : [];
            Installer.DataSet.includedThemes = result.themes ? result.themes : [];
            Installer.Pages.projectForm.refreshSections({
                projectId: code,
                projectName: result.name,
                projectOwner: result.owner,
                projectDescription: result.description
            });

            Installer.Pages.projectForm.bindAll();
            $('#nextButton').removeClass('disabled');
        })
        .fail(function(data){
            projectFormFailed.show().addClass('animate fade_in');
            projectFormFailed.renderPartial('project/fail', { reason: data.responseText });
            $('#nextButton').addClass('disabled');
        })
}

Installer.Pages.projectForm.bindIncludeManager = function(el) {
    var
        $el = $(el),
        $list = $el.find('.product-list:first'),
        $empty = $el.find('.product-list-empty:first'),
        $counter = $el.find('.product-counter:first'),
        partial = $el.data('view'),
        dataSetId = $el.data('set'),
        includedProducts = Installer.DataSet[dataSetId];

    if (!$el.length) {
        return;
    }

    if (includedProducts.length == 0) {
        $empty.show();
    }
    else {
        $.each(includedProducts, function(index, product){
            $list.renderPartial(
                partial,
                $.extend(true, {}, product, { projectId: Installer.Data.project.code }),
                { append: true }
            )
        });
        $empty.hide();
    }

    $counter.text(includedProducts.length);
}

Installer.Pages.projectForm.refreshSections = function(vars) {
    $('#projectForm').find('.section-content:first').renderPartial('project/project', vars);
}
