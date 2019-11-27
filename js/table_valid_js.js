function Sum(d) {

    var active_cell_id = d.id.split('_');
    var count = active_cell_id.length;

    var id_1_1_1, id_1_1_2, id_1_1_3, id_1_1_4, id_1_1_5, id_1_1_6, id_1_1_7, id_1_1_8, id_1_1_9;
    var id_1_1_10, id_1_1_11, id_1_1_12, id_1_1_13, id_1_1_14, id_1_1_15, id_1_1_16, id_1_1_17;

    id_1_1_1 = document.getElementById("1_1_1").value;
    id_1_1_2 = document.getElementById("1_1_2").value;
    id_1_1_3 = document.getElementById("1_1_3").value;

    id_1_1_4 = ((id_1_1_1 + id_1_1_2 + id_1_1_3) + 1) / 3;
    document.getElementById('1_1_4').value = parseFloat(id_1_1_4);


    id_1_1_5 = document.getElementById("1_1_5").value;
    id_1_1_6 = document.getElementById("1_1_6").value;
    id_1_1_7 = document.getElementById("1_1_7").value;

    id_1_1_8 = ((id_1_1_5 + id_1_1_6 + id_1_1_7) + 1) / 3;
    document.getElementById('1_1_8').value = parseFloat(id_1_1_8);


    id_1_1_9 = document.getElementById("1_1_9").value;
    id_1_1_10 = document.getElementById("1_1_10").value;
    id_1_1_11 = document.getElementById("1_1_11").value;

    id_1_1_12 = ((id_1_1_9 + id_1_1_10 + id_1_1_11) + 1) / 3;
    document.getElementById('1_1_12').value = parseFloat(id_1_1_12);

    id_1_1_13 = document.getElementById("1_1_13").value;
    id_1_1_14 = document.getElementById("1_1_14").value;
    id_1_1_15 = document.getElementById("1_1_15").value;

    id_1_1_16 = ((id_1_1_13 + id_1_1_14 + id_1_1_15) + 1) / 3;
    document.getElementById('1_1_16').value = parseFloat(id_1_1_16);

    id_1_1_17 = ((id_1_1_4 + id_1_1_8 + id_1_1_12 + id_1_1_16)+1)/4
    document.getElementById('1_1_17').value = parseFloat(id_1_1_17);
}