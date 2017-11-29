let default_selected = [];
document.addEventListener("DOMContentLoaded", function(event) {
    let codes_select = document.getElementById("building_codes_select");
    if (codes_select == null) {
        return;
    }
    
    for (let code_i in building_codes.sort()) {
        let option = document.createElement("option");
        option.selected = default_selected.includes(building_codes[code_i]);
        option.text = building_codes[code_i];
        option.value = building_codes[code_i];
        codes_select.add(option);
    }
});