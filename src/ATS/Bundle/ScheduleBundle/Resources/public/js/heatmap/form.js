function submitForm(form) {
    let building_codes = Array.prototype.map.call(form.building_codes.selectedOptions, function(x){ return x.value });
    let max_room = form.max_room_size.value;
    let min_room = form.min_room_size.value;
    let seat_utilization = form.seat_utilization.checked;
    
    showFilteredHeatMap(buildings, building_codes, max_room, min_room, seat_utilization)
}
