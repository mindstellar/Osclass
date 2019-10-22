    function check_form_country() {
        var error = 0;
        if ( $("#c_country").val().length != 2 ) {
            $("#c_country").css('border','1px solid red');
            error = error + 1;
        }
        if ( $("#country").val().length < 2 ) {
            $("#country").css('border','1px solid red');
            error = error + 1;
        }
        if(error > 0) {
            $('#c_code_error').css('display','block');
            return false;
        }
        return true;
    }

    function edit_countries(element) {
        var d_country = $('#d_edit_country');

        d_country.css('display','block');
        $('#fade').css('display','block');

        $("input[name=country_code]").val(element.attr('code'));
        $("input[name='e_country']").val(element.attr('data'));
        console.log(element);
        $("input[name='e_country_slug']").val(element.attr('slug'));
        renderEditCountry();
        return false;
    }

    function edit_region(element, id) {
        var d_region = $('#d_edit_region');

        d_region.css('display','block');
        $('#fade').css('display','block');

        $("input[name=region_id]").val(id);
        $("input[name=e_region]").val(element.html());
        $("input[name=e_region_slug]").val(element.attr('slug'));

        renderEditRegion();
        return false;
    }

    function edit_city(element, id) {
        var d_city = $('#d_edit_city');

        d_city.css('display','block');
        $('#fade').css('display','block');

        $("input[name=city_id]").val(id);
        $("input[name=e_city]").val(element.html());
        $("input[name=e_city_slug]").val(element.attr('slug'));
        renderEditCity();
        return false;
    }

    function show_region(c_code, s_country) {
        $.ajax({
            "url": base_url + "index.php?page=ajax&action=regions&countryId=" + c_code,
            "dataType": 'json',
            success: function( json ) {
                var div_regions = $("#i_regions").html('');
                $('#i_cities').html('');
                $.each(json, function(i, val){
                    var clear = $('<div>').css('clear','both');
                    var container = $('<div>');
                    var s_country = $('<div>').css('float','left');
                    var more_region = $('<div>').css('float','right');
                    var link = $('<a>').addClass('view-more');

                    s_country.append('<div class="trr"><span class="checkboxr" style="visibility:hidden;"><input type="checkbox" name="region[]" value="'+val.pk_i_id+'" ></span><a class="close" onclick="return delete_dialog(\'' + val.pk_i_id + '\', \'delete_region\');" href="' + base_url + 'index.php?page=settings&action=locations&type=delete_region&id[]=' + val.pk_i_id + '"><img src="' + base_url + 'images/close.png" alt="' + s_close + '" title="' + s_close + '" /></a><a href="javascript:void(0);" class="edit" onclick="edit_region($(this), ' + val.pk_i_id + ');" style="padding-right: 15px;" slug="'+val.s_slug+'" >' + val.s_name + '</a></div>');
                    link.attr('href', 'javascript:void(0)');
                    link.click(function(){
                        show_city(val.pk_i_id);
                    });
                    link.append(s_view_more + ' &raquo;');
                    more_region.append(link);
                    container.append(s_country).append(more_region);
                    div_regions.append(container);
                    div_regions.append(clear);
                });

                $(".trr").off("mouseenter");
                $(".trr").off("mouseleave");

                $(".trr").on("mouseenter", function() {
                    $(this).find(".checkboxr").css({ 'visibility': ''});
                });

                $(".trr").on("mouseleave", function() {
                    if (!$(this).find(".checkboxr input").is(':checked')) {
                        $(this).find(".checkboxr").css({ 'visibility': 'hidden'});
                    };
                    if($(".checkboxr input:checked").size()>0) {
                        $("#b_remove_region").show();
                    } else {
                        $("#b_remove_region").hide();
                    };
                });
                resetLayout();
                hook_load_cities();
            }
        });
        $('input[name=country_c_parent]').val(c_code);
        $('input[name=country_parent]').val(s_country);
        $('#b_new_region').css('display','block');
        $('#b_new_city').css('display','none');
        return false;
    }

    function show_city(id_region) {
        $.ajax({
            "url": base_url + "index.php?page=ajax&action=cities&regionId=" + id_region,
            "dataType": 'json',
            success: function( json ) {
                var div_regions = $("#i_cities").html('');
                $.each(json, function(i, val){
                    var clear = $('<div>').css('clear','both');
                    var container = $('<div>');
                    var s_region = $('<div>').css('float','left');
                    s_region.append('<div class="trct"><span class="checkboxct" style="visibility:hidden;"><input type="checkbox" name="city[]" value="'+val.pk_i_id+'" ></span><a class="delete" class="close" onclick="return delete_dialog(\'' + val.pk_i_id + '\', \'delete_city\');"  href="' + base_url + 'index.php?page=settings&action=locations&type=delete_city&id=' + val.pk_i_id + '"><img src="' + base_url + 'images/close.png" alt="' + s_close + '" title="' + s_close + '" /></a><a href="javascript:void(0);" class="edit" onclick="edit_city($(this), ' + val.pk_i_id + ');" style="padding-right: 15px;" slug="'+val.s_slug+'">' + val.s_name + '</a></div>');
                    container.append(s_region);
                    div_regions.append(container);
                    div_regions.append(clear);
                });
                $(".trct").off("mouseenter");
                $(".trct").off("mouseleave");

                $(".trct").on("mouseenter", function() {
                    $(this).find(".checkboxct").css({ 'visibility': ''});
                });

                $(".trct").on("mouseleave", function() {
                    if (!$(this).find(".checkboxct input").is(':checked')) {
                        $(this).find(".checkboxct").css({ 'visibility': 'hidden'});
                    };
                    if($(".checkboxct input:checked").size()>0) {
                        $("#b_remove_city").show();
                    } else {
                        $("#b_remove_city").hide();
                    };
                });
                resetLayout();
            }
        });
        $('#b_new_city').css('display','block');
        $('input[name=region_parent]').val(id_region);
        return false;
    }

    $(document).ready(function(){
        $("#c_country").focus(function(){
            $('#c_code_error').css('display','none');
           $(this).css('border','');
        });

        $("#country").focus(function(){
           $(this).css('border','');
        });

        $("#country").keyup(function(){
            if($('#country').val().length == 0) {
               $('input[name=c_manual]').val('1');
            }
        });

        $("#b_new_country").click(function(){
            renderNewCountry();
        });
        $("#b_import").click(function(){
            renderImport();
        });
        $("#b_new_region").click(function(){
            renderAddRegion();
        });
        $("#b_new_city").click(function(){
            renderAddCity();
        });
    });
    function renderNewCountry(){
        $( "#d_add_country" ).dialog({
            width: 250,
            modal: true,
            title: addNewCountryText,
        });
    }

    function renderImport(){
        $( "#dialog-location-import" ).dialog({
            width: 400,
            modal: true,
            title: importLocationText,
        });
    }

    function renderEditCountry(){
        var buttonsActions = {};
        buttonsActions[editText] = function() {
            $("#d_edit_country_form").submit();
        }
        buttonsActions[cancelText] = function() {
            $(this).dialog("close");
        }
        $( "#d_edit_country" ).dialog({
            width: 250,
            modal: true,
            title: editNewCountryText
        });
    }
    function renderAddRegion(){
        $( "#d_add_region" ).dialog({
            width: 400,
            modal: true,
            title: addNewRegionText
        });
    }
    function renderEditRegion(){
        $( "#d_edit_region" ).dialog({
            width: 400,
            modal: true,
            title: editNewRegionText
        });
    }
    function renderAddCity(){
        $( "#d_add_city" ).dialog({
            width: 400,
            modal: true,
            title: addNewCityText
        });
    }
    function renderEditCity(){
        $( "#d_edit_city" ).dialog({
            width: 400,
            modal: true,
            title: editNewCityText
        });
    }
