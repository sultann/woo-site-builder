/**
 * Woo Site Builder
 * http://pluginever.com
 *
 * Copyright (c) 2017 PluginEver
 * Licensed under the GPLv2+ license.
 */

/*jslint browser: true */
/*global jQuery:false */



jQuery(document).ready(function($) {

    window.builderIsSaved = false;

    'use strict';

    $('.preview').click(function(){
        $('body').toggleClass('preview');
        if ($('body').hasClass('preview')){
            $.cookie('toggle','preview', { expires: 1 });
        } else {
            $.cookie('toggle','normal', { expires: 1 });
        }
    });



    $('#builder-category-menu > ul').menuAim({
        activate: function(event){
            if (!$('#builder-category-menu').hasClass('disabled')){
                $("#builder-elements-menu, #builder-main-menu").removeClass('hidden');
                $("#builder-elements-menu li.selected").removeClass('selected');
                $(event).addClass('selected');

                var currentItem = $(event).data('menu-item');

                $('#builder-elements-menu').scrollTop(0);
                $('#builder-elements-menu ul.visible').removeClass('visible');
                $('#builder-elements-menu ul#'+currentItem).addClass('visible');
            }
        },
        exitMenu: function() {
            return true;
        }
    });


    // hide elements on mouse leave
    $("#builder-elements-menu").on( "mouseleave", function() {
        $("#builder-elements-menu, #builder-main-menu").addClass('hidden');
        $("#builder-main-menu li.selected").removeClass('selected');
    });


    /**
     * Generate Random string
     * @param len
     * @returns {string}
     */
    function randomString(len) {
        var charSet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        var randomString = '';
        for (var i = 0; i < len; i++) {
            var randomPoz = Math.floor(Math.random() * charSet.length);
            randomString += charSet.substring(randomPoz,randomPoz+1);
        }
        return randomString;
    }

    /**
     * Get User Reference
     * @returns {boolean}
     */
    function getUserReference(){
        var user_reference = $.cookie('user_reference');
        if (user_reference == undefined){
            return false;
        }
        return user_reference;
    }


    /**
     * Set User Permission
     */
    function setUserReference() {
        var user_reference = $.cookie('user_reference');

        if(undefined == user_reference){
            user_reference = randomString(10);
            $.cookie('user_reference', user_reference, { expires: 7, path: '/' });
        }
    }

    /**
     * Get Url pars as object
     * @returns {*}
     */
    function get_url_parts(){
        var urlHash = window.location.hash.substring(1);
        if(urlHash){
            return  $.getUrlVars(urlHash);
        }

        return null;
    }


    function hasHashUrl() {
        var urlHash = get_url_parts();
        if(urlHash == null) return false;
        return true;
    }


    function addSample() {
        var params = get_url_parts();

        var structure = params.structure;
        if (structure){
            var parts = structure.split(',');
            parts.forEach(function(product_id){
                if(isNaN(product_id)) return;
                var element_block = $('*[data-productid="'+product_id+'"]');
                if(element_block.length<1) return;
                var imageLink = null;
                var element_name = '';
                element_name = element_block.find('span').text();
                imageLink = element_block.find('img').attr('src');
                if((imageLink == null) || (imageLink == undefined)) return ;

                var item = $('<li data-productid="'+product_id+'" class="structural-elements"><span>'+element_name+'</span><img src="'+imageLink+'"></li>');

                if(item.find('.remove-this').length<1){
                    var removeicon = $("<span class='remove-block'>").on('click', function () {
                        removeItem(item);
                    });
                    removeicon.html("<i class='fa fa-trash' aria-hidden='true'></i>");
                    item.prepend(removeicon);
                }



                $('#builder-blocks').removeClass('empty').append(item);

            });
        }
    }


    function initialChecker() {
        if(!getUserReference()){
            setUserReference();
        }



        if(hasHashUrl()){
            var urlParts = get_url_parts();

            if(urlParts.page !== undefined){
                $('#page-name').text(readablePageName(urlParts.page));
            }
            addSample();







        }else{
            $('.remove-page').hide();
            $('#builder-blocks').addClass('create-page');
            $('#builder-category-menu').addClass('disabled');


        }



    }

    initialChecker();


    function setPageName(projectName) {
        var urlParts = [];
        if(!projectName){
            projectName = 'Untitled Project';
        }
        if (projectName) {
            projectName = encodeURIComponent( projectName.split(' ').join('_') );
            projectName = "page="+projectName;
            urlParts.push(projectName)
        }
        window.location.hash = urlParts.join('&');
        location.reload();

    }


    function getPageName() {
        var hash = window.location.hash.substring(1);
        if(hash){
            var url_parts = get_url_parts();

            return url_parts.page;
        }

        return null;
    }

    $('.create-page').on('click', function () {
        var page_name =  swal({
                title: "Page Name!",
                text: "Please type a page name to get started",
                type: "input",
                showCancelButton: true,
                closeOnConfirm: true,
                animation: "slide-from-top",
                inputPlaceholder: "eg.About Page",
                customClass:'page-create-modal'
            },
            function(inputValue){
                if (inputValue === "") {
                    swal.showInputError("You need to write something!");
                    return false
                }

                if (inputValue === false) {


                }else{

                    setPageName(inputValue);
                    initialChecker();



                }

            });
    });


    function readablePageName(page_name) {
        if(page_name == null) return;
        return page_name.replace('_', ' ');
    }


    function updateProductHash() {
        console.log('updating link');
        var urlParts = [];

        //get structure
        var blocks = [];
        $.map($("#builder-blocks").children('li'), function(el){
            blocks.push( $(el).data('productid'));
        });


        if (blocks.length > 0) {
            var hashURL = "structure=" + blocks.join(',');
        }
        var pageName = getPageName();

        if (hashURL) {

            urlParts.push(hashURL);

            //get name

            if (pageName !== null) {
                pageName = encodeURIComponent( pageName.split(' ').join('_') );
                pageName = "page="+pageName;
                urlParts.push(pageName);
            }

            window.location.hash = urlParts.join('&');
        }

        if(pageName && !hashURL){
            pageName = encodeURIComponent( pageName.split(' ').join('_') );
            pageName = "page="+pageName;
            urlParts.push(pageName);
            window.location.hash = urlParts.join('&');
        }



    }


    //Make Menu Items Draggable
    var draggableParams = {
        connectToSortable: "#builder-blocks",
        addClasses: false,
        scope: "#builder-blocks",
        helper: "clone",
        appendTo: 'body',
        distance: 50,
        drag: function(event, ui){
            setTimeout(function(){
                $('#builder-blocks li.placeholder').attr('style','height:100px');
            },50);
            $(window).mousemove(function( event ) {
                var windowY = event.pageY - $(window).scrollTop();
                var windowX = event.pageX;

                $('.ui-draggable-dragging').css('top',$(window).scrollTop() + windowY - 50).css('left',windowX-50).css('width','100px!important');
                //var precentage = windowX / $(window).width();
            });
        },
        start: function(event, ui){
           // $(event.target).addClass('dragging');
            console.log(event.target);

            window.droppedData = 'dragStart';
            window.draggingElement = $('.ui-draggable-dragging img');
            if (draggingElement.height() > 100)
                draggingElement.height('100px');

        },
        stop: function(event, ui){
            $(event.target).removeClass('dragging');
            setTimeout (function(){
                window.droppedData = '';
            },500);

            $('.placeholder').height($('.ui-draggable-dragging img').attr('src',draggingElement.attr('src').replace('270','900')).removeAttr('style').height());

            // checkMaxSize();
        }
    };

    $('img').on('dragstart', function(event) { event.preventDefault(); });

    $( "#builder-elements-menu li" ).draggable(draggableParams);

    // .click(function(){
    //     //Clickable
    //     var newElement = $(this).clone().appendTo($('#builder-blocks')).find('img').attr('src',$(this).attr('style','').find('img').attr('src').replace('270','900'));
    //     if(newElement.find('.remove-this').length<1){
    //         var removeicon = $("<span class='remove-block'>").on('click', function () {
    //             removeItem(item);
    //         });
    //         removeicon.html("<i class='fa fa-trash' aria-hidden='true'></i>");
    //         newElement.prepend(removeicon);
    //     }
    //     newElement.load(function(e) {
    //         setTimeout(function(){
    //             var position = $('#builder-blocks li:last-child').position();
    //             $('body').finish().animate({scrollTop: position.top},500);
    //         }, 250);
    //     });
    //
    //     $("#sortable").sortable("refresh");
    //     checkBlocksHeight();
    //     // updateHash();
    //     // checkMaxSize();
    // });

    var sortableParams = {
        opacity:0.75,
        placeholder: "placeholder",
        revert:300,
        distance: 10,
        refreshPositions: true,
        start: function(event, ui){
            $('.placeholder').height(ui.item.context.clientHeight);
            checkBlocksHeight();
        },
        out: function( event, ui ){
            setTimeout(function(){
                checkBlocksHeight();
            },50);
        },
        over: function( event, ui ){
            checkBlocksHeight();
        },
        stop: function( event, ui ){
            if (window.droppedData){

                if(ui.item.data('productid') !== undefined){
                    updateProductHash();
                }

                $('body').trigger('blockUpdate');


                $(ui.item).attr('style','').find('img').attr('src',$(ui.item).attr('style','').find('img').attr('src').replace('270','900'));
            }
        }
    };


    $("#builder-blocks").sortable(sortableParams).on( "sortstop", function( event, ui ) {
        // updateHash();
        // checkBlocksHeight();
    });


    function removeItem(item) {
        if(!item) return;
        $(item).remove()
        updateProductHash();
        if($("#builder-blocks li").length<1){
            $("#builder-blocks").addClass('empty');
        }


    }

    checkBlocksHeight();
    function checkBlocksHeight(){
        if ($("#builder-blocks li").length){
            $('#builder-blocks').removeClass('empty');
            $('#builder-project-canvas').removeClass('hide-ui');
            //$('.footer').addClass('hidden');
        } else {
            $('#builder-blocks').addClass('empty');
            $('#builder-project-canvas').addClass('hide-ui');
            $('.footer').removeClass('hidden');
        }
    }

    $('body').on('blockUpdate', function () {
       $('#builder-blocks li').each(function (item, key) {
           if($(key).find('.remove-this').length<1){
               var removeicon = $("<span class='remove-block'>").on('click', function () {
                   removeItem(key);
               });
               removeicon.html("<i class='fa fa-trash' aria-hidden='true'></i>");
               $(key).prepend(removeicon);
           }
       })
    });




    jQuery(window).bind(
        "beforeunload",
        function() {
            var currentLink = window.location.href;
            if(window.builderIsSaved ==false){

                swal({
                        title: "Are you sure?",
                        text: "You have unsaved page leaving this page, you will loose those.",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: "Leave",
                        cancelButtonText: "No, cancel please!",
                        closeOnConfirm: false,
                        closeOnCancel: false
                    },
                    function(isConfirm){
                        if (isConfirm) {
                            return ;
                        } else {
                            window.location.href = currentLink;
                           location.reload();
                        }
                    });
            }
        }
    );


    $('#builder-save').on('click', function () {
        save_project();
    });

    function save_project() {
        console.log('save_project was called');
        var user_reference = getUserReference();

        var userPars = get_url_parts();

        var structures = userPars.structure;
        var page = userPars.page;


        domtoimage.toJpeg(document.getElementById("builder-blocks"), { quality: 0.95 })
            .then(function (imageLink) {

                jQuery.post({
                    url: wsb.ajaxurl,
                    data: {
                        'action':'save_builder_project',
                        'blocks' : structures,
                        'page' : page,
                        'img' : imageLink,
                        'user_reference' : user_reference
                    },
                    success:function(response) {
                        updateSavedData(response);


                    },
                    error: function(errorThrown){
                        console.log(errorThrown);
                    }

                });
            });

    }




    function updateSavedData(response, hideMessage) {
        console.log('hideMessage' , hideMessage);
        if(hideMessage == undefined) {
            hideMessage = false;
        }
        if(response.success == true){
            window.builderIsSaved = true;

            if(response.data){
                make_preview_list(response.data);
                if(!hideMessage){
                    swal("Good job!", "Page Saved!", "success")
                }

            }else{
                $('.pages-preview').html('');
            }
        }

        if(response.price>0){
            $('#builder-checkout').show();
            $('#builder-cart').show();
            $('#builder-cart .builder-price').text(response.price);
        }else{
            $('#builder-checkout').hide();
            $('#builder-cart').hide();
        }
    }


    function make_preview_list(data) {

        if(!data) return;
        var html = '';
        var parent_link = window.location.href.split('#', 2);
        var ul = $('<ul>');
        $.each(data, function (key, item) {

            if(!item['page']) return;
            if(!item['blocks']) return;

            var page_name = readablePageName(item['page']);


            var hash  = '#structure='+item['blocks'];
            hash += '&page='+item['page'];

            var li = $('<li>').append('<a href="#" data-link="'+parent_link[0]+hash+'">');
            li.find('a').on('click', function (e) {
                location.href = $(this).data('link');
                location.reload();
                return false;
            });
            li.prepend('<span class="page-name">'+page_name+'</span>');

            li.find('a').append('<img src="'+item['image_link']+'" alt="">');
            ul.append(li);


        });


        $('.pages-preview').html('');
        $('.pages-preview').append(ul);

    }


    $('body').on('blockUpdate', function () {
        window.builderIsSaved = false;
        $('#builder-checkout').hide();
    });

    update_preview();
    function update_preview() {
        var user_reference = $.cookie('user_reference');
        if ((user_reference !== undefined)) {
            jQuery.post({
                url: wsb.ajaxurl,
                data: {
                    'action': 'get_builder_preview',
                    'user_reference': user_reference
                },
                success: function (response) {

                    updateSavedData(response, true);



                },
                error: function (errorThrown) {
                    console.log(errorThrown);
                }

            });
        }
    }



    $('.remove-page').on('click', function() {
        setTimeout(function(){
            if (confirm('Do you really want to remove this page?')){
                var urlParts = get_url_parts();
                var user_reference = $.cookie('user_reference');
                if((urlParts !== null) && (urlParts.page !== undefined) && (user_reference !== undefined)){
                    jQuery.post({
                        url: wsb.ajaxurl,
                        data: {
                            'action':'remove_builder_page',
                            'page' : urlParts.page,
                            'user_reference' : user_reference
                        },
                        success:function(response) {
                            clear_project();
                            updateSavedData(response);
                        },
                        error: function(errorThrown){
                            console.log(errorThrown);
                        }

                    });
                }


            }
        },250);
    });


    function clear_project() {
        $("#remove-page").empty();

        checkBlocksHeight();
        window.location.href = wsb.siteurl+"site-builder/";
        // location.reload();
        //updateHash();

        $('body').removeClass('preview');
        setTimeout(function(){
            // $('#subMenu').removeClass('hidden');
            // $('#header').addClass('visible');
            // $('#sideMenu ul li:first-child').addClass('selected');
        },500);

    }





});

function change_page($link){

    location.href = $link;
}
function openWindow(theURL, w, h) {
    var winName, scrollbars;
    LeftPosition = (screen.width) ? (screen.width - w) / 2 : 100;
    TopPosition = (screen.height) ? (screen.height - h) / 2 : 100;
    settings = 'width=' + w + ',height=' + h + ',top=' + TopPosition + ',left=' + LeftPosition + ',scrollbars=' + scrollbars + ',location=no,directories=no,status=0,menubar=no,toolbar=no,resizable=no';
    URL = theURL;
    window.open(URL, winName, settings);
}

