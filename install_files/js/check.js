/*!
 * System Check Page (Step 1)
 */
Installer.Pages.systemCheck.title = 'System Check'
Installer.Pages.systemCheck.nextButton = 'Agree & Continue'

Installer.Pages.systemCheck.requirements = {
    phpVersion: { label: 'PHP version 8.0.0 or greater required' },
    liveConnection: { label: 'Test connection to the installation server' },
    writePermission: { label: 'Permission to write to directories and files', reason: 'The installer was unable to write to the installation directories and files.' },
    phpExtensions: { label: 'Required PHP Extensions' },
};

Installer.Pages.systemCheck.init = function() {
    var checkList = $('#systemCheckList'),
        appEula = $('#appEula').hide(),
        systemCheckFailed = $('#systemCheckFailed').hide(),
        nextButton = $('#nextButton').addClass('disabled'),
        eventChain = [],
        failCodes = [],
        failReasons = [],
        success = false,
        self = this;

    var showError = function(code, reason) {
        systemCheckFailed.show().addClass('animate fade_in');
        systemCheckFailed.renderPartial('check/fail', { code: code, reason: reason });
    }

    /*
     * Loops each requirement, posts it back and processes the result
     * as part of a waterfall
     */
    $.sendRequest('onCheckRequirements', {}, { loadingIndicator: false })
        .done(function(data){
            if (data.result) {
                success = true;
                $.each(data.result, function(key, pass) {
                    if (key === 'isPass') {
                        return;
                    }

                    eventChain.push(function() {
                        var deferred = $.Deferred(),
                            requirement = self.requirements[key],
                            item = $('<li />').addClass('animated-content move_right').text(requirement.label);
                        item.addClass('load animate fade_in');
                        checkList.append(item);

                        setTimeout(function() {
                            if (pass) {
                                item.removeClass('load').addClass('pass');
                            }
                            else {
                                success = false;
                                failCodes.push(key);
                                if (requirement.reason) {
                                    failReasons.push(requirement.reason);
                                }
                                item.removeClass('load').addClass('fail');
                            }

                            deferred.resolve();
                        }, 500);

                        return deferred;
                    })
                });
            }
            else {
                showError('501', 'Bad response from server');
            }
        })
        .fail(function(data) {
            showError('500', data.responseText);
        })
        .always(function() {
            $.waterfall.apply(this, eventChain).done(function() {
                if (success) {
                    appEula.show().addClass('animate fade_in');
                    nextButton.removeClass('disabled');
                }
                else {
                    showError(failCodes.join(', '), failReasons.join(', '));
                }
            });
        });

    // /*
    //  * Fail the item but continue the waterfall.
    //  */
    // success = false
    // failCodes.push(requirement.code)
    // if (requirement.reason) failReasons.push(requirement.reason)
    // item.removeClass('load').addClass('fail')
    // deferred.resolve()


    // $.each(this.requirements, function(index, requirement){
    //     eventChain.push(function(){
    //         var deferred = $.Deferred();

    //         var item = $('<li />').addClass('animated-content move_right').text(requirement.label)
    //         item.addClass('load animate fade_in')
    //         checkList.append(item)
    //         return deferred;
    //     })
    // })

    /*
     * Handle the waterfall result
     */
    // $.waterfall.apply(this, eventChain).done(function() {
    //     console.log(success);
    //     if (success) {
    //         appEula.show().addClass('animate fade_in');
    //         nextButton.removeClass('disabled');
    //     }
    // });
}

Installer.Pages.systemCheck.next = function() {
    Installer.showPage('configForm')
}

Installer.Pages.systemCheck.retry = function() {
    var self = Installer.Pages.systemCheck
    $('#containerBody').html('').renderPartial('check', self)
    self.init()
}