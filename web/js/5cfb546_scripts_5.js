 $(document).ready(function () {			
    $("#jqGrid").jqGrid({
        url: 'http://localhost:8000/ajax/criteria/',
        mtype: "GET",
        styleUI : 'Bootstrap',
        datatype: "jsonp",
        colModel: [
            { label: 'Id', name: 'id', key: true, width: 75 },
            { label: 'Topic', name: 'topic', width: 150 },
            { label: 'Tag', name: 'tag', width: 150 },
        ],
        viewrecords: true,
        height: 250,
        rowNum: 20,
        pager: "#jqGridPager"
    });
});


