class CookieService {
    static get(key) {
        var pair = document.cookie.split(';').find(x => x.startsWith(key + '='));

        if (!pair) {
            return;
        }

        return pair.split('=')[1];
    }
}

export { CookieService as default}
