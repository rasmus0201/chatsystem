class TimeService {
    static now() {
        var now = new Date();

        var time = '';
        var intervals = [now.getHours(), now.getMinutes(), now.getSeconds()];

        for (const interval of intervals) {
            if (interval < 10) {
                time += '0';
            }

            time += interval + ':';
        }

        return time.substring(0, time.length - 1);
    }
}

export { TimeService as default}
