define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'sundry/class_advert/index' + location.search,
                    add_url: 'sundry/class_advert/add',
                    edit_url: 'sundry/class_advert/edit',
                    del_url: 'sundry/class_advert/del',
                    multi_url: 'sundry/class_advert/multi',
                    import_url: 'sundry/class_advert/import',
                    table: 'class_advert',
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
                        {field: 'avideo_name', title: __('Avideo_name'), operate: 'LIKE'},
                        {field: 'avideo_inter', title: __('Avideo_inter'), operate: 'LIKE'},
                        {field: 'video', title: __('Video'), operate: 'LIKE'},
                        {field: 'avideo_isp', title: __('Avideo_isp'), operate: 'LIKE'},
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
                        {field: 'update_time', title: __('Update_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
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
