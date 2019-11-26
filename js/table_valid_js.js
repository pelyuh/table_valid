function Sum(d) {

    let active_cell_id = d.id.split('_');

    console.log(active_cell_id);



    let id1;
    let id2;
    let id3;
    let id4;

    id1 = document.getElementById("1_1_1").value;
    id2 = document.getElementById("1_1_2").value;
    id3 = document.getElementById("1_1_3").value;


    id4 = ((id1+id2+id3)+1)/3;
    console.log(id4);
    document.getElementById('1_1_4').value = parseFloat(id4);


}
