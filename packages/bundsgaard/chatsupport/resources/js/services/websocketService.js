class WebSocketService {
    static new(protocol = 'ws') {
        var host = protocol + '://' + window.location.hostname;

        var url = host + '/websocket';
        if (host.match(/\.(test|localhost|dev)$/)) {
            var url = host + ':9000';
        }

        return new WebSocket(url);
    }
}

export { WebSocketService as default}
