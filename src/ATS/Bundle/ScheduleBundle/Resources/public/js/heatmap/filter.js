function goToHeatmapWithSlots(s) {
    var a = "/app_dev.php/t/" + encodeURI(JSON.stringify(s));
    console.log(a);
    window.location.href = a;
    return a;
}

function filter(buildings, building_codes, max_seat, min_seat) {
    var result = {}
    if (building_codes.length == 0) {
        result = buildings;
    } else {
        for (building_code_i in building_codes) {
            var building_code = building_codes[building_code_i];
            result[building_code] = buildings[building_code]
        }
    }

    for (building_code in result) {
        building = result[building_code];

        building.filtered_rooms = {}
        for (room_number in building.rooms) {
            room = building.rooms[room_number];
            if (room.seat_count >= min_seat && room.seat_count <= max_seat) {
                building.filtered_rooms[room_number] = room;
            }
        }
    }

    return result;
}

function getOccInDataFormat(buildings, building_codes, max_seat, min_seat, seat, selected_intervals) {
    var filtered_buildings = filter(buildings, building_codes, max_seat, min_seat);

    data = [];
    intervals = [];
    rooms = [];

    days = ["M","T","W","R","F"];
    hours = [8,9,10,11,12,1,2,3,4];
    hours24 = [8,9,10,11,12,13,14,15,16];

    for (i_day in days) {
        day = days[i_day];
        for (i_hour in hours) {
            hour = hours[i_hour];
            intervals.push(day + " " + hour + ":00")
            if (hour !== 4) {
                intervals.push(day + " " + hour + ":30")
            }
        }
    }

    if (selected_intervals === undefined) {
        selected_intervals = intervals;
    }

    room_i = -1;
    for (building_code in filtered_buildings) {
        building = filtered_buildings[building_code];

        for (room_number in building.filtered_rooms) {
            room = building.filtered_rooms[room_number];

            room_i++;
            rooms.push(building.name + " " + room.number);

            i_overall_interval = 0;
            let x_count = 0;
            var matrix = seat ? room.seat_occupancy_matrix : room.occupancy_matrix;
            for (i_day in matrix) {
                day_matrix = matrix[i_day];
                for (i_interval in day_matrix) {
                    var hour = hours24[Math.trunc(i_interval/2)];
                    var rem = i_interval/2.0 - Math.trunc(i_interval/2);
                    var minutes = rem > 0 ? "30" : "00";
                    var interval_name = days[i_day] + " " + hour + ":" + minutes;

                    if (selected_intervals.indexOf(interval_name) >= 0) {
                        interval_name = interval_name.replace(":","");
                        var sections = room.interval_names_to_sections_dict[interval_name];

                        x_count++;
                        data.push({
                            y: room_i + 1,
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