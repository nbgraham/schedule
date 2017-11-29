DAYS = ["M","T","W","R","F"];

function calculate(ev) {
    var timeStops = [];
    var startTime = roundToNearestHalfHour(ev.start, true);

    while(startTime <= ev.end){
        timeStops.push(new moment(startTime));
        startTime.add(30, 'minutes');
    }

    return timeStops;
}

function toShortFormat(m) { return DAYS[m.day() - 1] + " " + m.format("Hmm")}

function roundToNearestHalfHour(mm, up) {
    remainder = mm.minutes() % 30;
    remainder = up ? 30 - remainder: -1 * remainder;
    return moment(mm).add(remainder, "minutes").seconds(0);
}

function addHalfHourSections(ev, list) {
    var mmHalfHours = calculate(ev);

    for (i in mmHalfHours) {
        var i_name = toShortFormat(mmHalfHours[i]);
        if (list.indexOf(i_name) < 0) {
            list.push(i_name);
        }
    }
}