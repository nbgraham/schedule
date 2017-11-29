function goToHeatmapWithSlots(s) {
    let route = "/app_dev.php/t/" + encodeURI(JSON.stringify(s));
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

function buildDefaultIntervals() {
    let intervals = [];
    let start = new moment().day(0);
    while (start.day() < 6) {
        let day = [];
        start.hour(8).minute(0).add(1,'day');
        while (start.hour() < 16 || (start.hour() == 16 && start.minute() == 0)) {
            day.push(new moment(start));  
            start.add(30, "minutes");                    
        }
        intervals.push(day);
    }
    return intervals;
}

function getSelectedIntervals(selected_intervals, intervals) {
    if (selected_intervals === undefined) {
        selected_intervals = [];

        for (let i_day in intervals) {
            let dayArrayShortFormat = intervals[i_day].map(mmt => toShortFormat(mmt));
            selected_intervals = selected_intervals.concat(dayArrayShortFormat);
        }
    }
    return selected_intervals;
}

function getOccInDataFormat(buildings, building_codes, max_seat, min_seat, seat, chosen_intervals) {
    let filtered_buildings = filter(buildings, building_codes, max_seat, min_seat);
    let intervals = buildDefaultIntervals();  
    let selected_intervals = getSelectedIntervals(chosen_intervals, intervals);

    let data = [];
    let selected_intervals_names = [];
    let rooms = [];
    
    let room_i = 0;
    for (let building_code in filtered_buildings) {
        let building = filtered_buildings[building_code];
        
        for (let room_number in building.filtered_rooms) {
            let room = building.filtered_rooms[room_number];
            
            room_i++;
            rooms.push(building.name + " " + room.number);
            
            let x_count = 0;
            let matrix = seat ? room.seat_occupancy_matrix : room.occupancy_matrix;
            for (let i_day in matrix) {
                let day_array = matrix[i_day];
                for (let i_interval in day_array) {
                    let interval_name = toShortFormat(intervals[i_day][i_interval]);
                    
                    if (selected_intervals.includes(interval_name)) {
                        let display_name = intervals[i_day][i_interval].format("dd h:mm");
                        if (! selected_intervals_names.includes(display_name)) {
                            selected_intervals_names.push(display_name); 
                        }
                        let sections = room.interval_names_to_sections_dict[interval_name];
                        x_count++;
                        data.push({
                            y: room_i,
                            x: x_count,
                            val: day_array[i_interval],
                            sections,
                            room_cap: room.seat_count
                        });
                    }
                }
            }
            
        }
    }
    
    return {
        data,
        ylabels: rooms,
        xlabels: selected_intervals_names
    };
}