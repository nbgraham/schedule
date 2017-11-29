function goToHeatmapWithSlots(s) {
    let route = "/app_dev.php/t/" + encodeURI(JSON.stringify(s));
    console.debug(route);
    window.location.href = route;
    return route;
}

function filter(buildings, building_codes, max_seat, min_seat) {
    let result = {}
    if (building_codes.length == 0) {
        result = buildings;
    } else {
        for (let building_code_i in building_codes) {
            let building_code = building_codes[building_code_i];
            result[building_code] = buildings[building_code]
        }
    }
    
    for (let building_code in result) {
        building = result[building_code];
        
        building.filtered_rooms = {}
        for (let room_number in building.rooms) {
            let room = building.rooms[room_number];
            if (room.seat_count >= min_seat && room.seat_count <= max_seat) {
                building.filtered_rooms[room_number] = room;
            }
        }
    }
    
    return result;
}

function getOccInDataFormat(buildings, building_codes, max_seat, min_seat, seat, selected_intervals) {
    let filtered_buildings = filter(buildings, building_codes, max_seat, min_seat);
    
    let data = [];
    let intervals = [];
    let rooms = [];
    
    let start = new moment().day(1).hour(8).minute(0);
    while (start.day() < 6) {
        if (start.hour() < 16 || (start.hour() == 16 && start.minute() == 0)) {
            intervals.push(new moment(start));    
        }
        start.add(30, "minutes");        
    }
    
    if (selected_intervals === undefined) {
        selected_intervals = intervals;
    }
    
    let room_i = 0;
    for (let building_code in filtered_buildings) {
        let building = filtered_buildings[building_code];
        
        for (let room_number in building.filtered_rooms) {
            let room = building.filtered_rooms[room_number];
            
            room_i++;
            rooms.push(building.name + " " + room.number);
            
            let i_overall_interval = 0;
            let x_count = 0;
            let matrix = seat ? room.seat_occupancy_matrix : room.occupancy_matrix;
            for (let i_day in matrix) {
                let day_matrix = matrix[i_day];
                for (let i_interval in day_matrix) {
                    let interval_name = toShortFormat(intervals[i_overall_interval]);
                    
                    if (selected_intervals.includes(interval_name)) {
                        let sections = room.interval_names_to_sections_dict[interval_name];
                        x_count++;
                        data.push({
                            y: room_i,
                            x: x_count,
                            val: day_matrix[i_interval],
                            sections,
                            room_cap: room.seat_count
                        });
                    } else {
                        console.debug("Interval not found: " + intervals[i_overall_interval]);
                    }
                    i_overall_interval++;
                }
            }
            
        }
    }
    
    console.debug(data);
    return {
        data,
        ylabels: rooms,
        xlabels: selected_intervals
    };
}