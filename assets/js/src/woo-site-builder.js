/**
 * Woo Site Builder
 * http://pluginever.com
 *
 * Copyright (c) 2017 PluginEver
 * Licensed under the GPLv2+ license.
 */
jQuery(document).ready(function($) {
    'use strict';

    window.saved_project = false;

    // Toggle menu on click
    $('.toggle').click(function(){
        $('body').toggleClass('preview');
        if ($('body').hasClass('preview')){
            $.cookie('toggle','preview', { expires: 1 });
        } else {
            $.cookie('toggle','normal', { expires: 1 });
        }
    });

    get_preview();
    //show elemtns on hover
    $('#sideMenu > ul').menuAim({
        activate: function(event){
            if (!$('#sideMenu').hasClass('disabled')){
                $("#subMenu, #menu").removeClass('hidden');
                $("#sideMenu li.selected").removeClass('selected');
                $(event).addClass('selected');

                var currentItem = $(event).data('menu-item');

                $('#subMenu').scrollTop(0);
                $('#subMenu ul.visible').removeClass('visible');
                $('#subMenu ul#'+currentItem).addClass('visible');
            }
        },
        exitMenu: function() {
            return true;
        }
    });

    // hide elements on mouse leave
    $("#menu").on( "mouseleave", function() {
        $("#subMenu, #menu").addClass('hidden');
        $("#sideMenu li.selected").removeClass('selected');
    });

    var maxBlocks = 30;
    //load sample elements
    //Check URL's Hash
    var urlHash = window.location.hash.substring(1);
    if (urlHash){

        addSample();
    }


    // Add Sample by String
    function addSample(string){


        console.log('add sample called');
        if (!string) {
            var params = $.getUrlVars();

            var structure = params.structure;
            if (params.name){
                var projectName = params.name.split('_').join(' ');
                $('#project').val(projectName.substring(0,maxLetters));
            }
        }

        //should be like
        if (structure){
            var parts = structure.split(',').slice(0,maxBlocks);
            parts.forEach(function(product_id){
                if(isNaN(product_id)) return;
                var element_block = $('*[data-productid="'+product_id+'"]');
                if(element_block.length<1) return;
                var imageLink = null;
                var element_name = '';
                element_name = element_block.find('span').text();
                imageLink = element_block.find('img').attr('src');
                if((imageLink == null) || (imageLink == undefined)) return ;

                $('#blocks').removeClass('empty').append('<li data-productid="'+product_id+'"><span>'+element_name+'</span><img src="'+imageLink+'"></li>');

            });
        }

        $("#sortable").sortable("refresh");
        checkBlocksHeight();
        updateHash();
    }

    checkBlocksHeight();

    //Change Size of Blocks Holder
    function checkBlocksHeight(){
        if ($("#blocks li").length){
            $('#blocks').removeClass('empty');
            $('#blocksHolder').removeClass('hide-ui');
            $('.footer').addClass('hidden');
        } else {
            $('#blocks').addClass('empty');
            $('#blocksHolder').addClass('hide-ui');
            $('.footer').removeClass('hidden');
        }
    }





    //Update Hash
    function updateHash(){
        var hash = window.location.hash.substring(1);
        //if((hash == '') || (hash == undefined)) return;
        window.saved_project = false;


        var urlParts = [];

        //get structure
        var blocks = [];
        $.map($("#blocks").children('li'), function(el){
            blocks.push( $(el).data('productid'));
        });




        if (blocks.length > 0) {
            var hashURL = "structure=" + blocks.join(',');
        }


        if (hashURL) {

            urlParts.push(hashURL);

            //get name
            var projectName = $('#project').val().trim();
            if(!projectName){
                projectName = 'Untitled Project';
            }

            if (projectName) {
                projectName = encodeURIComponent( projectName.split(' ').join('_') );
                projectName = "name="+projectName;
                urlParts.push(projectName)
            }

            window.location.hash = urlParts.join('&');
        } else {
            window.location.hash = "";
        }


        get_preview();

       // var parts = $.getUrlVars(urlParts);





        //do all major stuff here





    }

    //Max Size
    function checkMaxSize(){
        if ($("#blocks li:not(.placeholder)").size() < maxBlocks) {
            $('#sideMenu').removeClass('disabled');
        } else {
            $('#sideMenu').addClass('disabled');
        }
    }

    //Name for Project
    var maxLetters = 35;
    $('#project').bind('keydown',function() {
        if($(this).hasClass('disabled')) return ;
        if ($('#project').val().length > maxLetters) $('#project').val($('#project').val().substring(0,maxLetters));
    });
    if($('#project').val() !== ''){
        console.log('project has value');
        $('#project').attr('disabled', 'disabled');
    }
    $('#project').bind('change',function() {
        if($('#project').val() !== ''){
            $('#project').attr('disabled', 'disabled');
            return false;
        }

        updateHash();
    });

    project_name_validation();

    function project_name_validation() {
        $('#project').on('change', function(){
            if($('#project').val().trim().length<1){
                $('#project').val('Untitled Project');
                updateHash();
            }
        });
    }


    //New Project




    function clear_project() {
        $("#blocks").empty();
        $("#project").val('').focus();

        checkBlocksHeight();
        checkMaxSize();
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

    //UX STUFF
    //Hide any window on click
    $('.overlay').click(function(event) {
        if (!$(event.target).closest('.window, .errors').length) {
            $(this).hide().addClass('hidden');
            $('body').removeClass('noscroll');
            $('.window').addClass('slideDown')
            $('.videobg').each(function(index, element) {
                element.pause();
            });
        }
    });

    //Bind ESC key
    $(document).keyup(function(e){
        if(e.keyCode === 27) $('.overlay').click();
    });


    //Show/Hide menu on empty
    $('#blocks').click(function(){
        if ($(this).hasClass('empty')){
            if ($('body').hasClass('preview')){
                $('.toggle').click();
            }
            $('#subMenu').removeClass('hidden');
            $('#header').addClass('visible');
            $('#sideMenu ul li:first-child').addClass('selected');

        }
    });


    //hide menu on bodyclick
    $(document).click(function(event) {
        if (!$(event.target).closest('#blocks, #menu').length) {
            $("#subMenu, #menu").addClass('hidden');
            $("#sideMenu li.selected").removeClass('selected');
        };
    });





    //Bind ESC to hide Overlays
    $(document).keydown(function(event){
        if (!$(".overlay").not('.hidden').exists()){
            var code = event.keyCode || event.which;
            if (code == '9') {
                $('.toggle').click();
                event.preventDefault();
            }
        }
    });






    //Dragging functions
    //SORT AND DRAG
    //Make Blocks Sotrable
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
                $(ui.item).attr('style','').find('img').attr('src',$(ui.item).attr('style','').find('img').attr('src').replace('270','900'));
            }
        }
    };
    $("#blocks").sortable(sortableParams).on( "sortstop", function( event, ui ) {
        updateHash();
        checkBlocksHeight();
    });



    //Make Menu Items Draggable
    var draggableParams = {
        connectToSortable: "#blocks",
        addClasses: false,
        scope: "#blocks",
        helper: "clone",
        appendTo: 'body',
        distance: 50,
        drag: function(event, ui){
            setTimeout(function(){
                $('#blocksHolder #blocks li.placeholder').attr('style','height:100px');
            },50);
            $(window).mousemove(function( event ) {
                var windowY = event.pageY - $(window).scrollTop();
                var windowX = event.pageX;

                $('.ui-draggable-dragging').css('top',$(window).scrollTop() + windowY - 50).css('left',windowX-50).css('width','100px!important');
                //var precentage = windowX / $(window).width();
            });
        },
        start: function(event, ui){

            window.droppedData = 'dragStart';
            window.draggingElement = $('.ui-draggable-dragging img');
            if (draggingElement.height() > 100)
                draggingElement.height('100px');

        },
        stop: function(event, ui){
            setTimeout (function(){
                window.droppedData = '';
            },500);

            $('.placeholder').height($('.ui-draggable-dragging img').attr('src',draggingElement.attr('src').replace('270','900')).removeAttr('style').height());

            checkMaxSize();
        }
    };
    $('img').on('dragstart', function(event) { event.preventDefault(); });


    $( "#subMenu li" ).draggable(draggableParams).click(function(){
        //Clickable
        if ($("#blocks li").size() < maxBlocks) {

            var newElement = $(this).clone().appendTo($('#blocks')).find('img').attr('src',$(this).attr('style','').find('img').attr('src').replace('270','900'));
            newElement.load(function(e) {
                setTimeout(function(){
                    var position = $('#blocks li:last-child').position();
                    $('body').finish().animate({scrollTop: position.top},500);
                }, 250);
            });

            $("#sortable").sortable("refresh");
            checkBlocksHeight();
            updateHash();
            checkMaxSize();
        }
    });


    //Trash Objects
    $('#sideMenu').droppable({
        accept: "#blocks li",
        activeClass: "active",
        hoverClass: "hovered",
        tolerance: "touch",
        drop: function(event, ui) {
            $(ui.draggable).remove();
            $('.placeholder').animate({height:0,opacity:0,borderWidth:0}, 250);
            checkMaxSize();
        }
    });

    $('#sideMenu img').droppable({
        drop: function( event, ui ) {
            $( this ).siblings( ".placeholder" ).remove();
            $( "<li></li>" ).text( ui.draggable.text() ).insertAfter( this );
        }
    });



    // additional functions
    $.fn.preload = function() { this.each(function(){ $('<img/>')[0].src = this; }); }
    $.fn.exists = function(){return this.length>0;}
    //extend for draggable
    var oldMouseStart = $.ui.draggable.prototype._mouseStart;
    $.ui.draggable.prototype._mouseStart = function (event, overrideHandle, noActivation) {
        this._trigger("beforeStart", event, this._uiHash());
        oldMouseStart.apply(this, [event, overrideHandle, noActivation]);
    };

    function get_url_parts(){

        var urlHash = window.location.hash.substring(1);
        if(urlHash){
            return  $.getUrlVars(urlHash);
        }

       return null;
    }

    get_url_parts();

    function make_layout_image() {
        domtoimage.toJpeg(document.getElementById("builder"), { quality: 0.95 })
            .then(function (imageLink) {
                return imageLink;
            });
    }


    function randomString(len) {
        var charSet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        var randomString = '';
        for (var i = 0; i < len; i++) {
            var randomPoz = Math.floor(Math.random() * charSet.length);
            randomString += charSet.substring(randomPoz,randomPoz+1);
        }
        return randomString;
    }


    function save_project(project_name,structures) {
        console.log('save_project was called');
        var user_reference = $.cookie('user_reference');

        if(undefined == user_reference){
            user_reference = randomString(10);
            $.cookie('user_reference', user_reference, { expires: 7, path: '/' });
        }

        // var imageUrl = '';
        // if(window.imageLink !== 'undefined'){
        //     imageUrl = window.imageLink;
        // }

        domtoimage.toJpeg(document.getElementById("builder"), { quality: 0.95 })
            .then(function (imageLink) {

                jQuery.post({
                    url: wsb.ajaxurl,
                    data: {
                        'action':'save_builder_project',
                        'blocks' : structures,
                        'name' : project_name,
                        'img' : imageLink,
                        'user_reference' : user_reference
                    },
                    success:function(response) {
                        console.log(response);
                       if(response.success == true){
                           if(response.data){
                               make_preview_list(response.data)
                           }else{
                               $('.pages-preview').html('');
                           }
                       }

                       if(response.price>0){
                           $('.builder-cart').show();
                           $('.builder-cart .cart-price').text('$'+response.price);
                       }else{
                           $('.builder-cart').hide();
                       }



                    },
                    error: function(errorThrown){
                        console.log(errorThrown);
                    }

                });
            });


    }


    function make_preview_list(data) {
        console.log('this was called');
        if(!data) return;
        var html = '';
        var parent_link = window.location.href.split('#', 2);
        var ul = $('<ul>');
        $.each(data, function (key, item) {
            console.log(item);
            if(!item['page']) return;
            if(!item['blocks']) return;
            var hash  = '#structure='+item['blocks'];
            hash += '&name='+item['page'];

            var li = $('<li>').append('<a href="#" data-link="'+parent_link[0]+hash+'">');
            li.find('a').on('click', function (e) {
                location.href = $(this).data('link');
                location.reload();
                return false;
            });

            li.find('a').append('<img src="'+item['image_link']+'" alt="">');
            ul.append(li);


        });


        $('.pages-preview').html('');
        $('.pages-preview').append(ul);

    }


    $('.clear').click(function() {
        setTimeout(function(){
            if (confirm('Do you really want to remove this page?')){
                var urlParts = get_url_parts();
                var user_reference = $.cookie('user_reference');
                if((urlParts !== null) && (urlParts.name !== undefined) && (user_reference !== undefined)){
                    jQuery.post({
                        url: wsb.ajaxurl,
                        data: {
                            'action':'remove_builder_page',
                            'page' : urlParts.name,
                            'user_reference' : user_reference
                        },
                        success:function(response) {
                            clear_project();
                            if(response.success == true){
                                make_preview_list(response.data)
                            }

                            if(response.price>0){
                                $('.builder-cart').show();
                                $('.builder-cart .cart-price').text('$'+response.price);
                            }else{
                                $('.builder-cart').hide();
                            }

                        },
                        error: function(errorThrown){
                            console.log(errorThrown);
                        }

                    });
                }





            }
        },250);
    });


    $('#builder-save').on('click', function () {
        var parts = get_url_parts();
        save_project(parts.name, parts.structure);


        return false;
    });

    $('.add-page').on('click', function(){
        if (confirm("Do you really want to create a new page?") == true) {

            var new_page_name = confirm("Please enter separate page name.");

            $('#project').val('');

            clear_project();
            // location.href = "http://sitebuilder.dev/site-builder/";
            get_preview();
            // location.reload();

        }
    });

    // console.log(wsb);


    function get_preview() {
        var user_reference = $.cookie('user_reference');
        if ((user_reference !== undefined)) {
            jQuery.post({
                url: wsb.ajaxurl,
                data: {
                    'action': 'get_builder_preview',
                    'user_reference': user_reference
                },
                success: function (response) {

                    if (response.success == true) {
                        make_preview_list(response.data)
                    }

                    if (response.price > 0) {
                        $('.builder-cart').show();
                        $('#builder-checkout').show();
                        // $('#builder-edit').show();
                        $('.builder-cart .cart-price').text('$' + response.price);
                    } else {
                        $('.builder-cart').hide();
                        $('#builder-checkout').hide();
                        // $('#builder-edit').hide();
                    }



                },
                error: function (errorThrown) {
                    console.log(errorThrown);
                }

            });
        }
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





