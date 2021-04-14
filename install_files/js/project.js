/*!
 * Packages & Install (Step 3)
 */

Installer.Pages.projectForm.init = function() {

    var projectForm = $('#projectForm').addClass('animate fade_in')

    Installer.renderSections(Installer.Pages.projectForm.sections)

    $('#suggestedProductsContainer').hide()

    Installer.Pages.projectForm.bindAll()
}

Installer.Pages.projectForm.next = function() {
    Installer.showPage('installProgress')
}

Installer.Pages.projectForm.startClean = function() {
    Installer.showPage('installProgress')
}

Installer.Pages.projectForm.bindAll = function() {
    Installer.Pages.projectForm.bindIncludeManager('#pluginList')
    Installer.Pages.projectForm.bindIncludeManager('#themeList')
}

Installer.Pages.projectForm.detachProject = function(el) {
    if (!confirm('Are you sure?')) return

    Installer.Data.project = null
    Installer.DataSet.includedPlugins = []
    Installer.DataSet.includedThemes = []
    Installer.refreshSections()
    Installer.Pages.projectForm.bindAll()
}

Installer.Pages.projectForm.attachProject = function(el) {
    var
        $el = $(el),
        $input = $el.find('.project-id-input:first'),
        code = $input.val(),
        projectFormFailed = $('#projectFormFailed').hide().removeClass('animate fade_in')

    $.sendRequest('onProjectDetails', { project_id: code })
        .done(function(result){
            Installer.Data.project = result
            Installer.Data.project.code = code
            Installer.DataSet.includedPlugins = result.plugins ? result.plugins : []
            Installer.DataSet.includedThemes = result.themes ? result.themes : []
            Installer.refreshSections({
                projectId: code,
                projectName: result.name,
                projectOwner: result.owner,
                projectDescription: result.description
            })
            Installer.Pages.projectForm.bindAll()
        })
        .fail(function(data){
            projectFormFailed.show().addClass('animate fade_in')
            projectFormFailed.renderPartial('project/fail', { reason: data.responseText })
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
        includedProducts = Installer.DataSet[dataSetId]

    if (!$el.length)
        return

    if (includedProducts.length == 0) {
        $empty.show()
    }
    else {
        $.each(includedProducts, function(index, product){
            $list.renderPartial(
                partial,
                $.extend(true, {}, product, { projectId: Installer.Data.project.code }),
                { append: true }
            )
        })
        $empty.hide()
    }

    $counter.text(includedProducts.length)
}

Installer.Pages.projectForm.findIncludeManagerFromEl = function(el) {
    var $el = $(el)

    if ($el.hasClass('product-list-manager'))
        return el

    return $(el).closest('[data-manager]').data('manager')
}
