/*!
 * System Check Page (Step 1)
 */

Installer.Pages.systemCheck.init = function() {
    var checkList = $('#systemCheckList'),
        appEula = $('#appEula').hide(),
        systemCheckFailed = $('#systemCheckFailed').hide(),
        nextButton = $('#nextButton').addClass('disabled'),
        eventChain = [],
        success = true,
        reasonCodes = []

    /*
     * Loops each requirement, posts it back and processes the result
     * as part of a waterfall
     */
    $.each(this.requirements, function(index, requirement){
        eventChain.push(function(){
            var deferred = $.Deferred();

            var item = $('<li />').addClass('animated-content move_right').text(requirement.label)
            item.addClass('load animate fade_in')
            checkList.append(item)

            $.sendRequest('onCheckRequirement', { code: requirement.code }, { loadingIndicator: false })
                .done(function(data){
                    setTimeout(function() {
                        if (data.result) {
                            item.removeClass('load').addClass('pass')
                            deferred.resolve()
                        }
                        else {
                            /*
                             * Fail the item but continue the waterfall.
                             */
                            item.removeClass('load').addClass('fail')
                            success = false
                            reasonCodes.push({ code: requirement.code, reason: requirement.reason })
                            deferred.resolve()
                        }
                    }, 500)
                }).fail(function(data){
                    item.removeClass('load').addClass('fail')
                    if (data.responseText) console.log('Failure reason: ' + data.responseText)
                    deferred.reject('ajaxFailure')
                })

            return deferred;
        })
    })

    /*
     * Handle the waterfall result
     */
    $.waterfall.apply(this, eventChain).done(function(){
        if(!success) {
            // Failure            
            // Assemble all reason codes into a string.
            var codeString = "";
            var reasonString = "";
            reasonCodes.forEach(function(element, index, array) {
                if(element.code) {
                    if(codeString !== "") {
                        codeString += ", "
                    }
                
                    codeString += element.code;
                }
                
                if(element.reason) {
                    reasonString += element.reason + " "
                }
            })
        
            // Specific reasons are not currently being used.
            systemCheckFailed.show().addClass('animate fade_in')
            systemCheckFailed.renderPartial('check/fail', { code: codeString, reason: reasonString })
        } else {
            // Success
            appEula.show().addClass('animate fade_in')
            nextButton.removeClass('disabled')
        }
    })
}

Installer.Pages.systemCheck.next = function() {
    Installer.showPage('configForm')
}

Installer.Pages.systemCheck.retry = function() {
    var self = Installer.Pages.systemCheck
    $('#containerBody').html('').renderPartial('check', self)
    self.init()
}