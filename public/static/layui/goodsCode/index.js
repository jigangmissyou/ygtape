    layui.use(['form', 'table', 'laydate', 'element'], function(){
    var table = layui.table;
    var form = layui.form;
    var laydate = layui.laydate;
    // var element = layui.element;
    const addGoodsCodeUrl = "/general/guojigang/public/index/index/addGoodsCode";
    const delGoodsCodeUrl = "/general/guojigang/public/index/index/delGoodsCode";
    const editGoodsCodeUrl = "/general/guojigang/public/index/index/editGoodsCode";
    const addHongYanUrl = "/general/guojigang/public/index/index/addHongYan";
    const editHongYanUrl = "/general/guojigang/public/index/index/editHongYan";
    const ajaxGoodsCodeList = "/general/guojigang/public/index/index/ajaxGoodsCodeList";
    const ajaxBomListUrl = "/general/guojigang/public/index/index/ajaxBomList";
    const ajaxHongYanListUrl = "/general/guojigang/public/index/index/ajaxHongYanList";
    const exportExcelUrl = "/general/guojigang/public/index/index/exportData";
    const logUrl = "/general/guojigang/public/index/index/logList";
    const ajaxFindInventoryUrl = "/general/guojigang/public/index/index/ajaxFindInventory";
    //用来存放 每一页 的 所有数据行 ID 的map集合
    var pageDataIdMap;
    //用来存放 我们勾选的数据行 ID 的map集合
    var idMap = new Map();
    //渲染存货编码
    table.render({
        elem: '#cunhuobm_list'
        ,url: ajaxGoodsCodeList
        ,limits: [10,20,50,100,200,500,1000,99999]
        ,limit: 20 //每页默认显示的数量
        ,response: {
            statusName: 'status'
            ,statusCode: 200
            ,msgName: 'msg'
            ,countName: 'count'
            ,dataName: 'data'
        }
        ,cols: [[
            {checkbox: true, fixed: true}
            ,{field:'id', title: 'ID'}
            ,{field:'code', title: '存货编码'}
            ,{field:'ctime', title: '添加日期'}
            ,{field:'', title: '操作', toolbar:'#colCunHuoBar'}
        ]]
        ,id: 'cunhuobm_list'
        ,page: true
        ,toolbar:"#headCunHuoBar"
        ,text: { none: '暂无相关数据' }
        ,totalRow:true
        ,done: function (res, curr, count){
            var len = res.data.length;
            //每一页的数据行时,将这一页的所有数据行 ID 保存到 pageDataIdMap 中
            pageDataIdMap = new Map();
              for(var i = 0;i < len;i++){   //填充当前页的数据
                  pageDataIdMap[res.data[i].code] = res.data[i].code;
              }
            var chooseNum = 0;   //记录当前页选中的数据行数
            for(var i = 0;i < len;i++){   //勾选行回显
                for(var key in idMap){
                    if(res.data[i].code == key){
                        res.data[i]["LAY_CHECKED"]='true';
                        //找到对应数据改变勾选样式，呈现出选中效果
                        var index= res.data[i]['LAY_TABLE_INDEX'];
                        $('tr[data-index=' + index + '] input[type="checkbox"]').prop('checked', true);
                        $('tr[data-index=' + index + '] input[type="checkbox"]').next().addClass('layui-form-checked');
                        chooseNum++;
                    }
                }
            }
            if(len != 0 && chooseNum == len){   //表示该页全选  --  全选按钮样式回显
                $('input[lay-filter="layTableAllChoose"]').prop('checked',true);
                $('input[lay-filter="layTableAllChoose"]').next().addClass('layui-form-checked');
            }
        }
    });
    table.on('checkbox(user)', function(obj){
        if(obj.type == 'one'){    //单选操作
            if(obj.checked){     //选中
                idMap[obj.data.code] = obj.data.code;
            }else{      //取消选中
                for(var key in idMap){
                    if(key == obj.data.code){   //移除取消选中的id
                        delete idMap[obj.data.code];
                    }
                }
            }
        }else{      //全选操作
            if(obj.checked){    //选中
                for(var pageKey in pageDataIdMap){
                    idMap[pageKey] = pageKey;
                }
            }else{     //取消选中
                for(var pageKey in pageDataIdMap){
                    for(var key in idMap){
                        if(key == pageKey){
                            delete idMap[pageKey];
                        }
                    }
                }
            }
        }
        // var active = {
        //     getCheckData: function(){
        //         batchSubmitWt();
        //     }
        // };

        // $('.demoTable .layui-btn').on('click', function(){
        //     var type = $(this).data('type');
        //     active[type] ? active[type].call(this) : '';
        //  });
    });
    table.render({
        elem: '#hongyan_list'
        ,url: ajaxHongYanListUrl
        ,response: {
            statusName: 'status'
            ,statusCode: 200
            ,msgName: 'msg'
            ,countName: 'count'
            ,dataName: 'data'
        }
        ,cols: [[
            ,{field:'id', title: 'ID'}
            ,{field:'code', title: '编码'}
            ,{field:'item_name', title: '名称'}
            ,{field:'model_no', title: '规格型号'}
            ,{field:'unit', title: '单位'}
            ,{field:'price_with_tax', title: '含税价格'}
            ,{field:'currency_type', title: '币种'}
            ,{field:'local_currency', title: '本币价格'}
            ,{field:'price_without_tax', title: '不含税价格'}
            ,{field:'ctime', title: '添加日期'}
            ,{field:'', title: '操作', toolbar:'#colHongYanBar'}
        ]]
        ,id: 'hongyan_list'
        ,page: true
        ,toolbar:"#hongYanBar"
        ,text: { none: '暂无相关数据' }
        ,totalRow:true
    });

    table.on('tool(user)', function (obj) {
        switch(obj.event){
            case 'edit':
                selectRole1(editGoodsCodeUrl+"?id="+obj.data.id, '编辑存货编码', '30%', '30%');
                break;
            case 'del':
                layer.confirm('确定删除?', {icon: 3, title: '提示'}, function (index) {
                    $.post(delGoodsCodeUrl, {id:obj.data.id}, function(data){
                        layer.msg(data.msg);
                        if(data.status==200) obj.del();
                        layer.close(index);
                    })
                    
                });
                break;
            case 'hy_edit':
                selectRole1(editHongYanUrl+"?id="+obj.data.id, '编辑洪研价格', '50%', '80%');
                break;
            case 'cubm_log':
                showDropDownPanel(logUrl + '?ticket_no=' + obj.data.id + '&action_type=1');
                break;
            case 'hy_log':
                showDropDownPanel(logUrl + '?ticket_no=' + obj.data.id + '&action_type=2');
                break;
        }
    })

    table.on('toolbar(user)', function (obj) {
        switch(obj.event){
            case 'add':
                selectRole(1, '50%', '30%');
                break;
            case 'hy_add':
                selectRole(2, '50%', '70%');
                break;
            case 'search':
                // let data = table.checkStatus('cunhuobm_list').data; //idTest 即为基础参数 id 对应的值
                getSearchData();
                break;
            case 'export_excel':
                exportExcelData();
                break;
        }

    })

    function selectRole(type=1, area1='50%', area2='50%'){
        let title, url;
        if (type == 1){
            title = "新增存货编码";
            url = addGoodsCodeUrl;
        } else if(type == 2){
            title = "新增洪研价格";
            url = addHongYanUrl;
        }
        layer.open({
            //layer提供了5种层类型。可传入的值有：0（信息框，默认）1（页面层）2（iframe层）3（加载层）4（tips层）
            type:2,
            title:title,
            area: [area1,area2],
            content: url,
            success:function(){
                form.render();
            }
        });
    }

    function getSearchData(){
        let codes = "";
        for(var key in idMap){
            codes += key + ",";
        }
        if(codes == ''){
            layer.open({title:'提示',content:'请勾选要查询的库存编码'});
            return false;
        }
        codes = codes.slice(0,-1);
        // let para = new Array();
        // for(let i=0; i<data.length; i++){
        //     para.push(data[i].code);
        // }
        // para = para.join(',');
        table.render({
            elem: '#bom_list'
            ,url: ajaxBomListUrl+'?code='+codes
            ,limits: [10,20,50,100,200,500,1000,99999]
            ,limit: 20 //每页默认显示的数量
            ,response: {
                statusName: 'status'
                ,statusCode: 200
                ,msgName: 'msg'
                ,countName: 'count'
                ,dataName: 'data'
            }
            ,cols: [[
                ,{field:'mjdl', title: '母件大类'}
                ,{field:'mjbm', title: '母件编码'}
                ,{field:'mjmc', title: '母件名称'}
                ,{field:'mjchdm', title: '母件存货代码'}
                ,{field:'mjggxh', title: '母件规格型号'}
                ,{field:'mjjdpf', title: '母件卷到平方'}
                ,{field:'mjjldw', title: '母件计量单位'}
                ,{field:'zjxh', title: '子件序号'}
                ,{field:'zjdl', title: '子件大类'}
                ,{field:'zjbm', title: '子件编码'}
                ,{field:'zjmc', title: '子件名称'}
                ,{field:'zjdm', title: '子件代码'}
                ,{field:'zjgg', title: '子件规格'}
                ,{field:'zjjdpf', title: '子件卷到平方'}
                ,{field:'jldw', title: '计量单位'}
                ,{field:'gylx', title: '供应类型'}
                ,{field:'jbyl', title: '基本用量'}
                ,{field:'jcyl', title: '基础用量'}
                ,{field:'bbwsdj', title: '本币无税单价', edit: 'text'}
                ,{field:'zdjg', title: '取价MAX(不含税)'}
                ,{field:'hyjg', title: '洪研价格', edit: 'text'}
                ,{field:'zf', title: '涨幅'}
                ,{field:'dzdsl', title: '打折的数量（膜和胶95%）'}
                ,{field:'bompfkz', title: 'BOM平方克重'}
                ,{field:'dh', title: '单耗'}
                ,{field:'cldj', title: '材料单价'}
                ,{field:'dwcbzc', title: '单位成本组成'}
                ,{field:'dwcbhj', title: '单位成本合计'}
                ,{field:'mjfzdw', title: '母件辅助单位'}
                ,{field:'bomfzdwcb', title: 'BOM辅助单位成本(纯专用材料)'}
            ]]
            ,id: 'bom_list'
            ,page: true
            ,toolbar:"#bomHeadBar"
            ,text: { none: '暂无相关数据' }
        });
    }

    function exportExcelData(){
        let codes = "";
        for(var key in idMap){
            codes += key + ",";
        }
        if(codes == ''){
            layer.open({title:'提示',content:'请先勾选需要导出的库存编码'});
            return false;
        }
        codes = codes.slice(0,-1);
        let url = exportExcelUrl + '?code=' + codes;
        window.open(url);
    }

    function selectRole1(path, title, area1 = '50%', area2 = '50%'){
        layer.open({
            //layer提供了5种层类型。可传入的值有：0（信息框，默认）1（页面层）2（iframe层）3（加载层）4（tips层）
            type:2,
            title:title,
            area: [area1,area2],
            content:path,
            success: function(obj, index){
                form.render();
            }
        });
    }

    function showDropDownPanel( path, area1 = '50%', area2 = '50%'){
        layer.open({
            //layer提供了5种层类型。可传入的值有：0（信息框，默认）1（页面层）2（iframe层）3（加载层）4（tips层）
            type:2,
            title:'变更日志',
            area: [area1,area2],
            content:path,
            success: function(obj, index){
                form.render();
            }
        });
    }

    var $ = layui.$, active = {
        reload: function(){
          var demoReload = $('#demoReload');
          var ddate = $('#ddate');
          
          //执行重载
          table.reload('cunhuobm_list', {
            page: {
              curr: 1 //重新从第 1 页开始
            }
            ,where: {
              key: {
                code: demoReload.val(),
                date: ddate.val()
              }
            }
          });
        }
    };

    var $ = layui.$, active = {
        reload: function(){
          var hyCode = $('#hyCode');
          var hyItemName = $('#itemName');
          var beginDate = $('#ddate1');
          var endDate = $('#ddate2');

          
          //执行重载
          table.reload('hongyan_list', {
            page: {
              curr: 1 //重新从第 1 页开始
            }
            ,where: {
              key: {
                code: hyCode.val(),
                item_name: hyItemName.val(),
                begin_date: beginDate.val(),
                end_date: endDate.val(),
              }
            }
          });
        }
    };

    $('.layui-fluid .layui-btn').on('click', function(){
    var type = $(this).data('type');
    active[type] ? active[type].call(this) : '';
    });

    $('.apply_table .layui-btn').on('click', function(){
        var type = $(this).data('type');
        active[type] ? active[type].call(this) : '';
    });

    //添加子件编码
    form.on('submit(add-goods-code)', function(data){
        submitData(addGoodsCodeUrl, data);
        return false;
    });

    //编辑子件编码
    form.on('submit(edit-goods-code)', function(data){
        submitData(editGoodsCodeUrl, data);
        return false;
    });

    //添加HongYan价格
    form.on('submit(add-hong-yan)', function(data){
        submitData(addHongYanUrl, data);
        return false;
    });

    //保存HongYan价格
    form.on('submit(edit-hong-yan)', function(data){
        submitData(editHongYanUrl, data);
        return false;
    });

    function submitData(url, data){
        $.ajax({
            type: "POST",
            url: url,
            data: data.field,
            traditional: true, //是否使用传统的方式浅层序列化,若有数组参数或对象参数需要设置true!!!!!!
            dataType:"json",
            success: function(returnData){
                if(returnData.status == 200){
                    layer.open({
                        type: 1
                        ,offset: 'auto'
                        ,id: 'layerDemo'
                        ,content: '<div style="padding: 20px 100px;">'+ returnData.msg +'</div>'
                        ,btn: '确定'
                        ,btnAlign: 'c'
                        ,shade: 0
                        ,yes: function(){
                            // layer.closeAll();
                            window.parent.location.reload();
                        },
                        success: function(){
                            // window.parent.location.reload();
                            table.reload();
                            //3.2  获得frame索引
                            // var index = parent.layer.getFrameIndex(window.name);
                            // //3.3   关闭当前frame
                            // parent.layer.close(index);
                            // //3.4   刷新页面
                            // window.parent.location.reload();
                        }
                    });
                }else{
                    layer.open({
                        type: 1
                        ,offset: 'auto'
                        ,id: 'layerDemo'
                        ,content: '<div style="padding: 20px 100px;">'+ returnData.msg +'</div>'
                        ,btn: '确定'
                        ,btnAlign: 'c'
                        ,shade: 0
                        ,yes: function(){
                            window.parent.location.reload();
                        },
                        success: function(){
                            table.reload();
                        }
                    });
                }
            }
        })
    }

    //单元格编辑hongyan价格
    table.on('edit(user)', function(obj){
        let zjbm = obj.data.zjbm;
        let hyjg = obj.data.hyjg;
        let bbwsdj = obj.data.bbwsdj;
        //先保存数据
        $.post(addHongYanUrl, {code:zjbm,price_without_tax:hyjg,local_currency:bbwsdj}, function(){
            // let data2 = table.checkStatus('cunhuobm_list').data
            getSearchData();
        })
    })

    //常规用法
    laydate.render({
        elem: '#ddate'
    });
    //常规用法
    laydate.render({
        elem: '#ddate1'
    });
    //常规用法
    laydate.render({
        elem: '#ddate2'
    });
    $("#zjbm").blur(function(){
        let zjbm = $("#zjbm").val();
        let item_name = $("#item_name").val();
        let model_no = $("#model_no").val();
        let unit = $("#unit").val();
        $.ajax({
            type: "GET",
            url: ajaxFindInventoryUrl + '?code=' + zjbm,
            success: function(returnData){
                $("#item_name").val(returnData.data.cInvName);
                $("#model_no").val(returnData.data.cInvStd);
            }
        })
    })
});


