define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'teach/class_type/index' + location.search,
                    add_url: 'teach/class_type/add',
                    edit_url: 'teach/class_type/edit',
                    del_url: 'teach/class_type/del',
                    multi_url: 'teach/class_type/multi',
                    import_url: 'teach/class_type/import',
                    table: 'class_type',
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
                        {field: 'type_name', title: __('Ftype'), operate: 'LIKE'},
                        {field: 'type', title: __('Type'), operate: 'LIKE'},
                        {field: 'status', title: __('Status'), operate: 'LIKE'},
                        {field: 'type_age', title: __('Type_age')},
                        {field: 'inage_image', title: __('Inage_image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'age_image', title: __('Age_image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'material', title: __('Material'), operate: 'LIKE'},
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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
                url: 'teach/class_type/recyclebin' + location.search,
                pk: 'id',
                sortName: 'id',
                search:false,
                columns: [
                    [
                        {field: 'type_name', title: __('Ftype'), operate: 'LIKE'},
                        {field: 'type', title: __('Type'), operate: 'LIKE'},
                        {field: 'type_age', title: __('Type_age')},
                        {field: 'inage_image', title: __('Inage_image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'age_image', title: __('Age_image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
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
                                    url: 'teach/class_type/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'teach/class_type/destroy',
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
                    var ur = 'teach/class_type/destroy';
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
            $.validator.config({
                rules: {
                    agebb:function (element) {
                        return $.ajax({
                            url: 'teach/class_type/age',
                            type: 'POST',
                            data: {type:$("#c-type").val(),one:$("#c-type-age").val(),two:$("#c-type-age2").val()},
                            dataType: 'json'
                        });
                    },
                    ageImage:function(){
                        return $.ajax({
                            url: 'teach/class_type/image',
                            type: 'POST',
                            data: {one:$("#c-type-age").val(),two:$("#c-type-age2").val(),age_image:$("#c-age_image").val()},
                            dataType: 'json'
                        });
                    },
                    inageImage:function(){
                        return $.ajax({
                            url: 'teach/class_type/image',
                            type: 'POST',
                            data: {one:$("#c-type-age").val(),two:$("#c-type-age2").val(),age_image:$("#c-inage_image").val()},
                            dataType: 'json'
                        });
                    },
                    status:function(){
                        return $.ajax({
                            url: 'teach/class_type/status',
                            type: 'POST',
                            data: {status:$("#c-status").val()},
                            dataType: 'json'
                        });
                    }
                }
            });
            $(document).on('change','#c-type',function(){
                $.ajax({
                    url: 'teach/class_type/typeImage',
                    type: 'POST',
                    data: {type: $(this).val()},
                    dataType: 'json',
                    success: Callback
                });
                function Callback(jsonData){
                    if(jsonData.data!=''){
                        $('#c-type_image').val(jsonData.data);
                        $("#c-type_image").attr("readonly","readonly");
                        $("#faupload-type_image").attr("disabled",true);
                    }
                }
            });
            var ctype;
            var typeage;
            var typeage2;
            $(document).on('change','#c-type,#c-type-age,#c-type-age2',function(){
                ctype=$('#c-type').val();
                typeage=$('#c-type-age').val();
                typeage2=$('#c-type-age2').val();
                if(ctype!=''&&typeage!=''&&typeage2!=''){
                    $("#faupload-inage_image").attr("disabled",false);
                    $("#faupload-age_image").attr("disabled",false);
                    $("#faupload-ftype_image").attr("disabled",false);
                }
            });
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
        }
    };
    return Controller;
});
