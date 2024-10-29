jQuery(document).ready(function ($) {
    'use strict';

    var $settingsTable = $('.awl-label-settings-table');

    // remove edit links
    $('#misc-publishing-actions a.edit-timestamp').remove();


    $('.awl-color-picker').wpColorPicker( {
        palettes: false,
        change: function (event, ui) {
            var element = event.target;
            var color = ui.color.toString();
            $(element).val(color).trigger('change');
        }
    } );


    $( '.awl-help-tip' ).tipTip( {
        'attribute': 'data-tip',
        'fadeIn': 50,
        'fadeOut': 50,
        'delay': 200
    } );


    var awlUniqueID = function() {
        return Math.random().toString(36).substr(2, 11);
    };


    var awlGetRuleTemplate = function( groupID, ruleID) {

        var template = $(this).closest('.awl-rules').find('#awlRulesTemplate').html();

        if ( typeof groupID !== 'undefined' ) {
            template = template.replace( /\[group_(.+?)\]/gi, '[group_'+groupID+']' );
        }

        if ( typeof ruleID !== 'undefined' ) {
            template = template.replace( /\[rule_(.+?)\]/gi, '[rule_'+ruleID+']' );
            template = template.replace( /data-awl-rule="(.+?)"/gi, 'data-awl-rule="'+ruleID+'"' );
        }

        return template;

    };


    $(document).on( 'click', '[data-awl-remove-rule]', function(e) {
        e.preventDefault();
        var $table = $(this).closest('.awl-rules-table');
        $(this).closest('[data-awl-rule]').remove();

        if ( $table.find('[data-awl-rule]').length < 1 ) {
            $table.remove();
        }

    });


    $(document).on( 'click', '[data-awl-add-rule]', function(e) {
        e.preventDefault();

        var groupID = $(this).closest('.awl-rules-table').data('awl-group');
        var ruleID = awlUniqueID();
        var rulesTemplate = awlGetRuleTemplate.call(this, groupID, ruleID);

        $(this).closest('.awl-rules-table').find( '.awl-rule' ).last().after( rulesTemplate );

    });


    $(document).on( 'click', '[data-awl-add-group]', function(e) {
        e.preventDefault();

        var groupID = awlUniqueID();
        var rulesTemplate = awlGetRuleTemplate.call(this, groupID);

        rulesTemplate = '<table class="awl-rules-table" data-awl-group="' + groupID + '"><tbody>' + rulesTemplate + '</tbody></table>';
        $(this).closest('.awl-rules').find('.awl-rules-table').last().after( rulesTemplate );

    });


    $(document).on('change', '[data-awl-param]', function(evt, params) {

        var newParam = this.value;
        var ruleGroup = $(this).closest('[data-awl-rule]');

        var ruleOperator = ruleGroup.find('[data-awl-operator]');
        var ruleValues = ruleGroup.find('[data-awl-value]');
        var ruleParams = ruleGroup.find('[data-awl-param]');
        var ruleSuboptions = ruleGroup.find('[data-awl-suboption]');

        var ruleID = ruleGroup.data('awl-rule');
        var groupID = $(this).closest('[data-awl-group]').data('awl-group');

        ruleGroup.addClass('awl-pending');

        if ( ruleSuboptions.length ) {
            ruleSuboptions.remove();
        }

        $.ajax({
            type: 'POST',
            url: awl_vars.ajaxurl,
            data: {
                action: 'awl-getRuleGroup',
                name: newParam,
                ruleID: ruleID,
                groupID: groupID,
                _ajax_nonce: awl_vars.ajax_nonce
            },
            success: function (response) {
                if ( response ) {

                    var responseArr = $.parseJSON( response );

                    ruleGroup.removeClass('adv');

                    if ( typeof responseArr.aoperators !== 'undefined' ) {
                        ruleOperator.html( responseArr.aoperators );
                    }

                    if ( typeof responseArr.avalues !== 'undefined' ) {
                        ruleValues.html( responseArr.avalues );
                    }

                    if ( typeof responseArr.asuboptions !== 'undefined' ) {
                        ruleParams.after( responseArr.asuboptions );
                        ruleGroup.addClass('adv');
                    }

                    ruleGroup.removeClass('awl-pending');

                }
            }
        });

    });

    $(document).on('change', '[data-awl-suboption]', function(evt, params) {

        var suboptionParam = this.value;
        var ruleGroup = $(this).closest('[data-awl-rule]');
        var ruleParam = ruleGroup.find('[data-awl-param]').attr('value');
        var ruleValues = ruleGroup.find('[data-awl-value]');

        var ruleID = ruleGroup.data('awl-rule');
        var groupID = $(this).closest('[data-awl-group]').data('awl-group');

        ruleGroup.addClass('awl-pending');

        $.ajax({
            type: 'POST',
            url: awl_vars.ajaxurl,
            data: {
                action: 'awl-getSuboptionValues',
                param: ruleParam,
                suboption: suboptionParam,
                ruleID: ruleID,
                groupID: groupID,
                _ajax_nonce: awl_vars.ajax_nonce
            },
            success: function (response) {
                if ( response ) {

                    var responseArr = $.parseJSON( response );

                    ruleValues.html( responseArr );

                    ruleGroup.removeClass('awl-pending');

                }
            }
        });

    });

    $(document).on('change', '#awl-label-params-settings-custom-styles', function(evt, params) {
        $settingsTable.toggleClass('awl-show-styles');
    });

    $(document).on('change', '#awl-label-params-settings-position-type', function(evt, params) {

        var positionType = this.value;
        var positionClass = 'awl-position-on-line';
        var currentPositionClass = '';

        $settingsTable.attr('class', function(i, c){
            var classRegex = /awl-position-\S+/g;
            currentPositionClass = c.match(classRegex)[0];
            return c.replace(classRegex, '');
        });

        if ( positionType === 'on_image' ) {
            positionClass = 'awl-position-on-image';
        }

        // Change position value to best matched
        var positionFieldOld = $('#awl-label-params-settings-position');
        var positionFieldNew = $('#awl-label-params-settings-position-x');

        if ( currentPositionClass && currentPositionClass !== positionClass ) {

            if ( positionType === 'on_image' ) {

                positionFieldOld = $('#awl-label-params-settings-position-x');
                positionFieldNew = $('#awl-label-params-settings-position');

                if ( positionFieldNew.val().indexOf( positionFieldOld.val() ) !== 0 ) {
                    positionFieldNew.val( positionFieldOld.val() + '_top' );
                }

            } else {

                var regex = new RegExp('([\\w]+)_([\\w]+)', 'g');
                var positionsArr = regex.exec( positionFieldOld.val() );

                if ( positionsArr && positionsArr[1] !== positionFieldNew.val() ) {
                    positionFieldNew.val( positionsArr[1] );
                }

            }

        }

        $settingsTable.addClass( positionClass );
        positionFieldNew.trigger('change');

    });

    $(document).on('change', '#awl-label-params-settings-type', function(evt, params) {

        var labelType = this.value;

        var typeOption = $('[data-option-id="type"]');
        var templatesSelector = $('[data-template-select]');

        typeOption.addClass('awl-pending');

        window.setTimeout(function(){

            $settingsTable.attr('class', function(i, c){
                return c.replace(/awl-type-\S+/g, '');
            });

            $settingsTable.addClass( 'awl-type-' + labelType );

            var firstOption = templatesSelector.find('[data-templates="' + labelType + '"]').find('.option:first-of-type');
            awl_set_template( firstOption );
            typeOption.removeClass('awl-pending');

        }, 500);

    });

    $(document).on('click', '[data-awl-template]', function(e, params) {

        if (  ! $(e.target).closest('[data-template-select]').length ) {
            $(this).toggleClass('awl-active');
        }


    });

    $(document).on( 'click', function (e) {

        if (  ! $(e.target).closest('[data-awl-template]').length ) {
            $('[data-awl-template]').removeClass('awl-active');
        }

    });

    $(document).on('click', '[data-template-select] .option', function(evt, params) {
        awl_set_template( $(this) );
    });

    // Image upload
    $('[data-awl-upload]').on('click', function(e) {

        e.preventDefault();

        var option = $('[data-templates="image"]').find('.option:first-of-type');
        var size = 'thumbnail';
        var custom_uploader;

        //If the uploader object has already been created, reopen the dialog
        if (custom_uploader) {
            custom_uploader.open();
            return;
        }

        //Extend the wp.media object
        custom_uploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Image',
            button: {
                text: 'Choose Image'
            },
            multiple: false,
            type : 'image'
        });

        custom_uploader.on('select', function() {
            var attachment = custom_uploader.state().get('selection').first().toJSON();

            var image_size = attachment.sizes['full'];
            var image_src = image_size.url;

            var newOption = option.clone( true );
            newOption.data('val', image_src);
            newOption.find('.ico').css('background-image','url('+image_src+')');

            newOption.insertBefore( option );

            var templatesBox = $('[data-awl-template]');
            templatesBox.addClass('awl-pending');

            window.setTimeout(function(){
                awl_set_template(newOption);
                templatesBox.removeClass('awl-pending');
            }, 1000);

        });

        //Open the uploader dialog
        custom_uploader.open();

    });


    $('.additional-info').on('click', function(e) {
        e.preventDefault();
        if ( ! $(e.target).closest( '.info-spoiler' ).length ) {
            $(this).find('.info-spoiler').toggleClass('show');
        }
    });


    function awl_set_template( $el ) {
        $('.awl-template [data-template-select] .option').removeClass('awl-active');
        $('.awl-template [data-current-template]').attr('style', $el.find('.ico').attr('style') );
        $('.awl-template [data-template-val]').val( $el.data('val') ).trigger('change');
        $el.addClass('awl-active');
    }


    // Hooks table

    var awlGetHookTableTemplate = function( hookID) {

        var template = $('#awlHooksTableTemplate').html();

        if ( typeof hookID !== 'undefined' ) {
            template = template.replace( /\[hookid_(.+?)\]/gi, '[hookid_'+hookID+']' );
        }

        return template;

    };

    $(document).on( 'change', '.awl-hooks-table [data-awl-hook]', function(e) {
        e.preventDefault();

        var hook = this.value;
        var $container = $(this).closest('td');

        if ( hook === 'custom action' || hook === 'custom filter' ) {
            $container.addClass('awl-custom');
        } else {
            $container.removeClass('awl-custom');
        }

    });

    $(document).on( 'click', '.awl-hooks-table [data-awl-remove-hook]', function(e) {
        e.preventDefault();
        $(this).closest('[data-awl-hook-container]').remove();
    });

    $(document).on( 'click', '.awl-hooks-table [data-awl-add-hook]', function(e) {
        e.preventDefault();

        var hookID = awlUniqueID();
        var rulesTemplate = awlGetHookTableTemplate.call(this, hookID);

        $(this).closest('.awl-hooks-table').find( 'tbody' ).append( rulesTemplate );

    });

});