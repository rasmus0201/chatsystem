class TimeService {
    static now() {
        var now = new Date();

        return now.toISOString();

        // var time = '';
        // var intervals = [now.getHours(), now.getMinutes(), now.getSeconds()];
        //
        // for (const interval of intervals) {
        //     if (interval < 10) {
        //         time += '0';
        //     }
        //
        //     time += interval + ':';
        // }
        //
        // return time.substring(0, time.length - 1);
    }

    static dateDiffInDays(a, b) {
        const MS_PER_Day = 1000 * 60 * 60 * 24;

        // Discard the time and time-zone information.
        const utc1 = Date.UTC(a.getFullYear(), a.getMonth(), a.getDate(), a.getHours(), a.getMinutes(), a.getSeconds());
        const utc2 = Date.UTC(b.getFullYear(), b.getMonth(), b.getDate(), b.getHours(), b.getMinutes(), b.getSeconds());

        return ((utc2 - utc1) / MS_PER_Day).toFixed(2);
    }

    static format(d) {
        const date = new Date(d);
        const diff = TimeService.dateDiffInDays(date, new Date());

        // If more than a day show the whole date except for the year.
        if (diff >= 1) {
            return date.getDate() + '/' + date.getMonth() + ' ' + date.getHours() + ':' + date.getMinutes() + ':' + date.getSeconds();
        }

        // Get the time part
        return d.split(' ')[1];
    }
}

export { TimeService as default}
