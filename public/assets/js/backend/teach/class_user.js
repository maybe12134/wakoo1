define(['jquery', 'bootstrap', 'backend', 'table','form'], function ($, undefined, Backend, Table, Form) {
    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'teach/class_user/index' + location.search,
                    add_url: 'teach/class_user/add',
                    edit_url: 'teach/class_user/edit',
                    del_url: 'teach/class_user/del',
                    buy_url: 'teach/class_user/buy',
                    multi_url: 'teach/class_user/multi',
                    import_url: 'teach/class_user/import',
                    table: 'class_user',
                }
            });
            var table = $("#table");
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                search:false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'iphone', title: __('Iphone'),operate: 'LIKE'},
                        {field: 'password', title: __('Password'),operate:false},
                        {field: 'image', title: __('Image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'classname', title: __('Classname'), operate: 'LIKE'},
                        {field: 'sex', title: __('Sex')},
                        {field: 'age', title: __('Age')},
                        {field: 'store_name', title: __('Store_name'),cellStyle: {css: {"max-width": "300px","white-space":"nowrap","overflow":"auto"}},operate: 'LIKE'},
                        {field: 'create_time', title: __('Create_time'), operate:false, addclass:'datetimerange', autocomplete:false},
                        {field: 'operate', title: __('Operate'), table: table, buttons: [
                                {
                                    name: 'click',
                                    title: __('购买'),
                                    classname: 'btn btn-xs btn-info btn-dialog',
                                    icon: 'fa fa-leaf',
                                    url: 'teach/class_user/buy',
                                },
                                {
                                    name: 'click',
                                    title: __('详细信息'),
                                    classname: 'btn btn-xs btn-warning btn-info btn-dialog',
                                    icon: 'fa fa-list',
                                    url: 'teach/class_user/info',
                                }
                            ],events: Table.api.events.operate, formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });
            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        recyclebin: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    'dragsort_url': ''
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: 'teach/class_user/recyclebin' + location.search,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'iphone', title: __('Iphone')},
                        {field: 'image', title: __('Image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'classname', title: __('Classname'), operate: 'LIKE'},
                        {
                            field: 'delete_time',
                            title: __('Deletetime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'operate',
                            width: '130px',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'Restore',
                                    text: __('Restore'),
                                    classname: 'btn btn-xs btn-info btn-ajax btn-restoreit',
                                    icon: 'fa fa-rotate-left',
                                    url: 'teach/class_user/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'teach/class_user/destroy',
                                    refresh: true
                                }
                            ],
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);


            $('#destroyall').click(function(){
                Layer.confirm(__('Are you sure you want to truncate?'), function () {
                    var ur = 'teach/class_user/destroy';
                    var ids= $("#table").bootstrapTable('getData');// //获取所有条目ID集合
                    var arr = [];
                    if(ids.length != 0){
                        for (let prop in ids){ //prop指对象的属性名
                            arr.push(ids[prop].ids);
                        }
                    }
                    Fast.api.ajax({
                        url:ur,
                        data:{ids:arr},
                    },function () {
                        Layer.closeAll();
                        table.trigger("uncheckbox");
                        table.bootstrapTable('refresh');
                    }, function () {
                        Layer.closeAll();
                    });
                });
            });


        },
        add: function () {
            $(document).on('change','#c-iphone',function(){
               $.ajax({
                   url: 'teach/class_user/iphone',
                   type: 'POST',
                   data: {iphone:$("#c-iphone").val()},
                   dataType: 'json',
                   success: Callback
               });
               function Callback(jsonData){
                   if(jsonData.data!=""){
                       if(jsonData.data.store_name.length==0){
                           $('#c-classname').attr('readonly','readonly');
                           $('#b-store').attr('disabled','disabled');
                           $("#c-sex").attr("disabled","disabled");
                           $('#c-age').attr('readonly','readonly');
                           $('.selectpicker').selectpicker('refresh');//加载select框选择器
                       }else{
                           $('#c-classname').val(jsonData.data.classname);
                           $('#c-classname').attr('readonly','readonly');
                           $("#c-sex").val(jsonData.data.sex);
                           $('.selectpicker').selectpicker('refresh');
                           $("#c-sex").attr("disabled","disabled");
                           $('#c-age').val(jsonData.data.age);
                           $('#c-age').attr('readonly','readonly');
                           var html="";
                           var list=Object.values(jsonData.data.store_name);
                           list.forEach(function(value,index){
                               html += "<option  value='" +value.id+ "' selected='selected' data-name='"+value.store_name+"'>" + value.store_name+ "</option>";
                           });
                           $("#b-store").html(html);
                           $('.selectpicker').selectpicker('refresh');//加载select框选择器
                       }
                   }else{
                       $("#c-classname").attr("readonly",false);
                       $("#c-age").attr("readonly",false);
                       $("#c-sex").attr("disabled",false);
                       $("#c-classname").val('');
                       $("#c-sex").val('');
                       $('.selectpicker').selectpicker('refresh');
                       $("#c-age").val('');
                   }

               }

            });
            var log=new Array();
            $('#b-store option').each(function(){
                log.push($(this).text());
            });
            if(log.length==1){
                $(".sele").css("display","none");
            }
            $(document).on("click","#but",function(){
                $("#c-sex").removeAttr("disabled");
            });
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        buy: function () {
            $(document).on("change", "#b-store", function(){
                //变更后的回调事件
                let val=$("#b-store").val();
                if(val==0){
                    $('#b-type').selectPageClear();
                    $("#b-type").selectPageDisabled(true);
                }else{
                    $.ajax({
                        url: 'teach/class_user/ftype',
                        type: 'POST',
                        data: {campus_id:$('#b-store').val()},
                        dataType: 'json',
                    });
                    $('#b-type').selectPageClear();
                    $("#b-type").selectPageDisabled(false);
                }
            });
            $(document).on("change", "#b-type", function(){
                let count=$('#b-type').val();
                count=count.split(',');
                if(count.length===2){
                    $("#b-type_text").data("selectPageObject").option.data = "teach/class_user/ftype";
                }else{
                    if(count!= ''){
                        $.ajax({
                            url: 'teach/class_user/campus',
                            type: 'POST',
                            data: {ftype:count[0],iphone:$('#c-iphone').val(),campus_id:$('#b-store').val()},
                            dataType: 'json',
                        });
                        $("#b-type_text").data("selectPageObject").option.data = "teach/class_user/campus";
                    }else{
                        //变更后的回调事件
                        $("#b-type_text").data("selectPageObject").option.data = "teach/class_user/ftype";
                    }
                }
                if(count.length!=2){
                    $("#ok_id").attr('disabled','disabled');
                }else{
                    $("#ok_id").removeAttr('disabled');
                }
            });
            Controller.api.bindevent();
        },
        info: function () {
            $(document).on("change", "#i-store", function(){
                let val=$("#i-store").val();
                if(val==0){
                    $('#c-name').selectPageClear();
                    $("#c-name").selectPageDisabled(true);
                }else{
                    $('#c-name').selectPageClear();
                    $("#c-name").selectPageDisabled(false);
                    $.ajax({
                        url: 'teach/class_user/store',
                        type: 'POST',
                        data: {iphone:$('#c-iphone').val(),store:$(this).val()},
                        dataType: 'json',
                    });
                }
            });
            $(document).on('change', '#c-name', function () {
                let type = $(this).val();
                if (type == '') {
                    $('#i-div').css('display', 'none');
                }else{
                    $('#i-div').css('display', 'block');
                    $.ajax({
                        url: 'teach/class_user/state',
                        type: 'POST',
                        data: {iphone: $('#c-iphone').val(), type: type},
                        dataType: 'json',
                        success: function(jsonData) {
                            $("#i-state").val(jsonData);
                        }
                    });
                    
                }
            });
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
