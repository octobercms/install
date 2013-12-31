/*!
 * Install Progress (Step 5)
 */

Installer.Pages.installComplete.beforeUnload = function() {
    // Hide the leaves
    $(document).octoberLeaves('stop')
}

Installer.Pages.installComplete.beforeShow = function() {

    Installer.Pages.installComplete.baseUrl = installerBaseUrl
    Installer.Pages.installComplete.backendUrl = installerBaseUrl + 'backend';

}

Installer.Pages.installComplete.init = function() {
    // Purrty leaves
    $(document).octoberLeaves({ numberOfLeaves: 10, cycleSpeed: 40 })
    $(document).octoberLeaves('start')
}
