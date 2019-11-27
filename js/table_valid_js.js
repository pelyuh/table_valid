function Sum(d) {

    var active_cell_id = d.id.split('_');
    var count = active_cell_id.length;
    var id1;
    var id2;
    var id3;
    var id4;


    id = active_cell_id[0] + '_' + active_cell_id[1] + '_' + active_cell_id[2];
    for (var t = 1; t <= 2; t++) {
        if (active_cell_id[0] == t) {
            for (var r = 1; r <= 2; r++) {
                if (active_cell_id[1] == r) {
                    if (active_cell_id[2] == 1) {
                        id1 = active_cell_id[0] + '_' + active_cell_id[1] + '_' + active_cell_id[2];
                    }
                }
            }
        }
    }



        console.log(id1);
        console.log(id2);

    var cell1 = document.getElementById(id1).value;
    var cell2 = document.getElementById(id2).value;

    id3 = document.getElementById("1_1_3").value;


    id4 = ((cell1 + cell2 + id3) + 1) / 3;
    document.getElementById('1_1_4').value = parseFloat(id4);
}